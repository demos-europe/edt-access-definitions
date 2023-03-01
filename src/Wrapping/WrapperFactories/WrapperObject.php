<?php

declare(strict_types=1);

namespace EDT\Wrapping\WrapperFactories;

use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\Contracts\PaginationException;
use EDT\Querying\Contracts\SortException;
use EDT\Querying\Contracts\SortMethodInterface;
use EDT\Querying\Utilities\ConditionEvaluator;
use EDT\Querying\Utilities\Iterables;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\RelationshipAccessException;
use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\AliasableTypeInterface;
use EDT\Wrapping\Contracts\Types\ExposableRelationshipTypeInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Properties\AttributeUpdatability;
use EDT\Wrapping\Properties\ToManyRelationshipUpdatability;
use EDT\Wrapping\Properties\ToOneRelationshipUpdatability;
use EDT\Wrapping\Properties\AbstractUpdatability;
use EDT\Wrapping\Utilities\PropertyReader;
use InvalidArgumentException;
use function array_key_exists;
use function count;
use function is_object;
use function Safe\preg_match;

/**
 * Wraps a given object, corresponding to a {@link TypeInterface}.
 *
 * Instances will provide read and write access to specific properties of the given object.
 *
 * Read access will only be granted if the given {@link TypeInterface} implements {@link TransferableTypeInterface}.
 * The properties allowed to be read depend on the return of {@link TransferableTypeInterface::getReadableProperties()}.
 * Only those relationships will be readable whose target type return `true` in
 * {@link ExposableRelationshipTypeInterface::isExposedAsRelationship()}
 *
 * Returned relationships will be wrapped themselves inside {@link WrapperObject} instances.
 *
 * @template TEntity of object
 */
class WrapperObject
{
    /**
     * @var non-empty-string
     */
    private const METHOD_PATTERN = '/(get|set)([A-Z_]\w*)/';

    /**
     * @param TEntity $entity
     * @param TransferableTypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity> $type
     */
    public function __construct(
        private readonly object $entity,
        private readonly PropertyReader $propertyReader,
        private readonly TransferableTypeInterface $type,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly ConditionEvaluator $conditionEvaluator,
        private readonly WrapperObjectFactory $wrapperFactory
    ) {}

    /**
     * @return TypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity>
     */
    public function getResourceType(): TypeInterface
    {
        return $this->type;
    }

    /**
     * @param non-empty-string $methodName
     * @param array<int|string, array<int|string, mixed>|simple_primitive|object|null> $arguments
     *
     * @return mixed|null|void If no parameters given:<ul>
     *   <li>In case of a relationship: an array, {@link WrapperObject} or <code>null</code>.
     *   <li>Otherwise a primitive type.</ul> If parameters given: `void`.
     * @throws AccessException
     */
    public function __call(string $methodName, array $arguments = [])
    {
        $match = $this->parseMethodAccess($methodName);
        $propertyName = lcfirst($match[2]);
        if ('' === $propertyName) {
            throw new InvalidArgumentException('Property name must not be an empty string.');
        }
        $argumentsCount = count($arguments);

        if ('get' === $match[1] && 0 === $argumentsCount) {
            return $this->__get($propertyName);
        }
        if ('set' === $match[1] && 1 === $argumentsCount) {
            $this->__set($propertyName, array_pop($arguments));
            return;
        }

        throw AccessException::unexpectedArguments($this->type, 0, $argumentsCount);
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return mixed|null The value of the accessed property, wrapped into another wrapper instance.
     *
     * @throws AccessException
     * @throws TypeRetrievalAccessException
     * @throws AccessException
     * @throws PropertyAccessException
     * @throws PathException
     * @throws SortException
     * @throws PaginationException
     */
    public function __get(string $propertyName)
    {
        // we allow reading of properties that are actually accessible
        $readableProperties = $this->type->getReadableProperties();
        $propertyPath = $this->mapProperty($propertyName);

        // TODO: respect readability settings at least partially

        if (array_key_exists($propertyName, $readableProperties[0])) {
            $readability = $readableProperties[0][$propertyName];
            $propertyValue = $this->propertyAccessor->getValueByPropertyPath($this->entity, ...$propertyPath);
            if (!$readability->isValidValue($propertyValue)) {
                $stringPath = implode('.', $propertyPath);
                throw new InvalidArgumentException("Value retrieved via property path '$stringPath' is not allowed by readability settings.");
            }

            // if non-relationship, simply use the value read from the target
            return $propertyValue;
        }

        if (array_key_exists($propertyName, $readableProperties[1])) {
            $readability = $readableProperties[2][$propertyName];
            $propertyValue = $this->propertyAccessor->getValueByPropertyPath($this->entity, ...$propertyPath);
            $relationshipType = $readability->getRelationshipType();
            $relationshipEntityClass = $relationshipType->getEntityClass();

            if (null === $propertyValue) {
                return null;
            }

            if (!$propertyValue instanceof $relationshipEntityClass) {
                throw RelationshipAccessException::toOneNeitherObjectNorNull($propertyName);
            }

            $relationshipEntity = $this->propertyReader->determineToOneRelationshipValue($relationshipType, $propertyValue);
            if (null === $relationshipEntity) {
                return null;
            }

            return $this->wrapperFactory->createWrapper($relationshipEntity, $relationshipType);
        }

        if (array_key_exists($propertyName, $readableProperties[2])) {
            $readability = $readableProperties[2][$propertyName];
            $propertyValue = $this->propertyAccessor->getValueByPropertyPath($this->entity, ...$propertyPath);
            $relationshipType = $readability->getRelationshipType();

            $relationshipValues = $this->propertyReader->verifyToManyIterable($propertyValue, $propertyName, $relationshipType->getEntityClass());
            $entities = $this->propertyReader->determineToManyRelationshipValue($relationshipType, $relationshipValues);

            // wrap the entities
            return array_map(
                fn (object $objectToWrap) => $this->wrapperFactory->createWrapper($objectToWrap, $relationshipType),
                $entities
            );
        }

        throw PropertyAccessException::propertyNotAvailableInReadableType(
            $propertyName,
            $this->type,
            ...array_keys(array_merge(...$readableProperties))
        );
    }

    /**
     * @param AbstractUpdatability<FunctionInterface<bool>> $updatability
     */
    protected function isAllowedByEntityConditions(AbstractUpdatability $updatability): bool
    {
        return $this->conditionEvaluator->evaluateConditions(
            $this->entity,
            $updatability->getEntityConditions()
        );
    }
    /**
     * @param non-empty-string $propertyName
     * @param array<int|string, mixed>|simple_primitive|object|null $value The value to set. Will only be allowed if the property name matches with an allowed property
     *                     (must be {@link TransferableTypeInterface::getUpdatableProperties() updatable} and
     *                     (if it is a relationship) the target type of the relationship returns `true` in
     *                     {@link ExposableRelationshipTypeInterface::isExposedAsRelationship()}.
     *
     * @throws AccessException
     */
    public function __set(string $propertyName, array|string|int|float|bool|object|null $value): void
    {
        // we allow writing of properties that are actually accessible
        $nestedUpdatabilities = $this->type->getUpdatableProperties();
        $nestedUpdatabilities = [
            array_filter($nestedUpdatabilities[0], [$this, 'isAllowedByEntityConditions']),
            array_filter($nestedUpdatabilities[1], [$this, 'isAllowedByEntityConditions']),
            array_filter($nestedUpdatabilities[2], [$this, 'isAllowedByEntityConditions']),
        ];
        $updatabilities = array_merge(...$nestedUpdatabilities);
        if (!array_key_exists($propertyName, $updatabilities)) {
            throw PropertyAccessException::propertyNotAvailableInUpdatableType($propertyName, $this->type, ...array_keys($updatabilities));
        }

        // at this point we ensured the relationship type is available and referencable but still
        // need to check the access conditions
        $this->setOrThrowIfNotSetable($nestedUpdatabilities, $propertyName, $value);
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @throws AccessException
     */
    public function getPropertyValue(string $propertyName): mixed
    {
        return $this->__get($propertyName);
    }

    /**
     * @param non-empty-string $methodName
     *
     * @return list<string>
     *
     * @throws AccessException
     */
    protected function parseMethodAccess(string $methodName): array
    {
        preg_match(self::METHOD_PATTERN, $methodName, $match);

        // First item is complete $methodName, second set|get, third the property name
        if (3 !== count($match)) {
            throw AccessException::failedToParseAccessor($this->type, $methodName);
        }

        return $match;
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return non-empty-list<non-empty-string>
     */
    private function mapProperty(string $propertyName): array
    {
        return $this->type instanceof AliasableTypeInterface
            ? $this->type->getAliases()[$propertyName] ?? [$propertyName]
            : [$propertyName];
    }

    /**
     * This method will prevent access to relationship values that should not be accessible.
     *
     * The type of the relationship is provided via `$relationship`. The value will be deemed setable if one of the following is true:
     *
     * * `$relationship` is `null`, indicating it is a non-relationship, which is not the scope of this method
     * * the {@link TypeInterface::getAccessCondition() access condition} of the given `$relationship` match
     * the given `$propertyValue`
     *
     * If the given property value is iterable, indicating a to-many relationship, then each
     * value within this iterable will be checked against the access condition. All values must
     * match, otherwise an {@link AccessException} is thrown.
     *
     * This method will **not** check if the given `$relationship`
     * {@link ExposableRelationshipTypeInterface::isExposedAsRelationship() exposable as relationship}.
     *
     * It will also **not** consider
     * information like {@link TransferableTypeInterface::getReadableProperties() readable} or
     * {@link TransferableTypeInterface::getUpdatableProperties() updatable} properties.
     *
     * @param array{0: array<non-empty-string, AttributeUpdatability<FunctionInterface<bool>, TEntity>>, 1: array<non-empty-string, ToOneRelationshipUpdatability<FunctionInterface<bool>, SortMethodInterface, TEntity, object>>, 2: array<non-empty-string, ToManyRelationshipUpdatability<FunctionInterface<bool>, SortMethodInterface, TEntity, object>>} $updatabilities
     * @param non-empty-string $propertyName
     * @param array<int|string, mixed>|simple_primitive|object|null $propertyValue A single value of some type or an iterable.
     *
     * @throws AccessException
     */
    protected function setOrThrowIfNotSetable(
        array $updatabilities,
        string $propertyName,
        array|string|int|float|bool|object|null $propertyValue
    ): void {
        $propertyPath = $this->mapProperty($propertyName);

        // follow the path to get the actual target in which a property is to be set
        $deAliasedPropertyName = array_pop($propertyPath);

        if (array_key_exists($propertyName, $updatabilities[2])) {
            $updatability = $updatabilities[2][$propertyName];
            if (!is_iterable($propertyValue)) {
                throw RelationshipAccessException::toManyNotIterable($propertyName);
            }
            $entityClass = $updatability->getRelationshipType()->getEntityClass();
            // if to-many relationship prevent setting restricted items
            foreach (Iterables::asArray($propertyValue) as $key => $value) {
                if (!$value instanceof $entityClass) {
                    throw new InvalidArgumentException('Tried setting a value with the wrong entity type into a to-many relationship.');
                }
                if (!$this->conditionEvaluator->evaluateConditions($value, $updatability->getValueConditions())) {
                    throw RelationshipAccessException::toManyWithRestrictedItemNotSetable($this->type, $propertyName, $deAliasedPropertyName, $key);
                }
            }

            $customWriteCallback = $updatability->getCustomWriteCallback();
            if (null !== $customWriteCallback) {
                $customWriteCallback($this->entity, $propertyValue);

                return;
            }
        }

        if (array_key_exists($propertyName, $updatabilities[1])) {
            $updatability = $updatabilities[1][$propertyName];
            $entityClass = $updatability->getRelationshipType()->getEntityClass();
            if (!$propertyValue instanceof $entityClass) {
                throw new InvalidArgumentException('Tried setting a value with the wrong entity type into a to-one relationship property.');
            }

            if (!$this->conditionEvaluator->evaluateConditions($propertyValue, $updatability->getValueConditions())) {
                // if restricted to-one relationship
                throw RelationshipAccessException::toOneWithRestrictedItemNotSetable($this->type, $propertyName, $deAliasedPropertyName);
            }

            $customWriteCallback = $updatability->getCustomWriteCallback();
            if (null !== $customWriteCallback) {
                $customWriteCallback($this->entity, $propertyValue);

                return;
            }
        }

        if (array_key_exists($propertyName, $updatabilities[0])) {
            $updatability = $updatabilities[0][$propertyName];

            // TODO: simplify by adding support for conditions evaluating non-objects
            if (!$updatability->isValidValue($propertyValue)) {
                throw new InvalidArgumentException('Value to set into attribute is not allowed by updatability settings.');
            }
            if (is_object($propertyValue) && !$this->conditionEvaluator->evaluateConditions($propertyValue, $updatability->getValueConditions())) {
                throw new InvalidArgumentException('Value to set into attribute is not allowed by value conditions.');
            }

            $customWriteCallback = $updatability->getCustomWriteCallback();
            if (null !== $customWriteCallback) {
                $customWriteCallback($this->entity, $propertyValue);

                return;
            }
        }

        // Set via propertyAccessor if there was no custom-write function.
        // We already ensured before this method was called that the property name exists in one
        // of the updatability-arrays.
        $target = [] === $propertyPath
            ? $this->entity
            : $this->propertyAccessor->getValueByPropertyPath($this->entity, ...$propertyPath);
        $this->propertyAccessor->setValue($target, $propertyValue, $deAliasedPropertyName);
    }

    /**
     * @return TEntity
     *
     * @internal Warning: exposing the backing object is dangerous, as it allows to read values
     * unrestricted not only from the returned object but all its relationships.
     *
     * @deprecated use {@link self::getEntity} instead
     */
    public function getObject(): object
    {
        return $this->entity;
    }

    /**
     * @return TEntity
     *
     * @internal Warning: exposing the backing object is dangerous, as it allows to read values
     * unrestricted not only from the returned object but all its relationships.
     */
    public function getEntity(): object
    {
        return $this->entity;
    }
}
