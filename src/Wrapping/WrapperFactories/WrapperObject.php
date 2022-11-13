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
use EDT\Wrapping\Properties\UpdatableRelationship;
use EDT\Wrapping\Utilities\PropertyReader;
use InvalidArgumentException;
use function array_key_exists;
use function count;
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
     * @var TEntity
     */
    private object $object;

    /**
     * @var TransferableTypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity>
     */
    private TransferableTypeInterface $type;

    private PropertyAccessorInterface $propertyAccessor;

    private PropertyReader $propertyReader;

    private ConditionEvaluator $conditionEvaluator;

    private WrapperObjectFactory $wrapperFactory;

    /**
     * @param TEntity                                                                          $object
     * @param TransferableTypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity> $type
     */
    public function __construct(
        object                    $object,
        PropertyReader            $propertyReader,
        TransferableTypeInterface $type,
        PropertyAccessorInterface $propertyAccessor,
        ConditionEvaluator        $conditionEvaluator,
        WrapperObjectFactory      $wrapperFactory
    ) {
        $this->object = $object;
        $this->type = $type;
        $this->propertyAccessor = $propertyAccessor;
        $this->propertyReader = $propertyReader;
        $this->conditionEvaluator = $conditionEvaluator;
        $this->wrapperFactory = $wrapperFactory;
    }

    /**
     * @return TypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity>
     */
    public function getResourceType(): TypeInterface
    {
        return $this->type;
    }

    /**
     * @param non-empty-string         $methodName
     * @param array<int|string, mixed> $arguments
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
        if (!array_key_exists($propertyName, $readableProperties)) {
            throw PropertyAccessException::propertyNotAvailableInReadableType($propertyName, $this->type, ...array_keys($readableProperties));
        }

        // Get the potentially wrapped value for the requested property
        $relationship = $readableProperties[$propertyName];
        $propertyPath = $this->mapProperty($propertyName);
        $propertyValue = $this->propertyAccessor->getValueByPropertyPath($this->object, ...$propertyPath);

        if (null === $relationship) {
            // if non-relationship, simply use the value read from the target
            return $propertyValue;
        }

        if (is_iterable($propertyValue)) {
            $entities = $this->propertyReader->determineToManyRelationshipValue($relationship, $propertyValue);

            // wrap the entities
            return array_map(
                fn (object $objectToWrap) => $this->wrapperFactory->createWrapper($objectToWrap, $relationship),
                $entities
            );
        }

        $entity = $this->propertyReader->determineToOneRelationshipValue($relationship, $propertyValue);
        if (null === $entity) {
            return null;
        }

        return $this->wrapperFactory->createWrapper($entity, $relationship);
    }

    /**
     * @param non-empty-string $propertyName
     * @param mixed $value The value to set. Will only be allowed if the property name matches with an allowed property
     *                     (must be {@link TransferableTypeInterface::getUpdatableProperties() updatable} and
     *                     (if it is a relationship) the target type of the relationship returns `true` in
     *                     {@link ExposableRelationshipTypeInterface::isExposedAsRelationship()}.
     *
     * @throws AccessException
     */
    public function __set(string $propertyName, $value): void
    {
        // we allow writing of properties that are actually accessible
        $updatableProperties = $this->type->getUpdatableProperties($this->object);
        if (!array_key_exists($propertyName, $updatableProperties)) {
            throw PropertyAccessException::propertyNotAvailableInUpdatableType($propertyName, $this->type, ...array_keys($updatableProperties));
        }

        $updatableRelationship = $updatableProperties[$propertyName];
        $propertyPath = $this->mapProperty($propertyName);

        // follow the path to get the actual target in which a property is to be set
        $deAliasedPropertyName = array_pop($propertyPath);
        $target = [] === $propertyPath
            ? $this->object
            : $this->propertyAccessor->getValueByPropertyPath($this->object, ...$propertyPath);

        // at this point we ensured the relationship type is available and referencable but still
        // need to check the access conditions
        $this->throwIfNotSetable($updatableRelationship, $propertyName, $deAliasedPropertyName, $value);
        $this->setUnrestricted($deAliasedPropertyName, $target, $value);
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return mixed|null
     *
     * @throws AccessException
     */
    public function getPropertyValue(string $propertyName)
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
     * Set the value into the given object
     *
     * @param non-empty-string $propertyName
     * @param mixed|null       $value
     */
    protected function setUnrestricted(string $propertyName, object $target, $value): void
    {
        $this->propertyAccessor->setValue($target, $value, $propertyName);
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
     * @param UpdatableRelationship<FunctionInterface<bool>>|null $updatableRelationship
     * @param non-empty-string              $propertyName
     * @param non-empty-string              $deAliasedPropertyName
     * @param mixed                         $propertyValue         A single value of some type or an iterable.
     *
     * @throws AccessException
     */
    protected function throwIfNotSetable(
        ?UpdatableRelationship $updatableRelationship,
        string $propertyName,
        string $deAliasedPropertyName,
        $propertyValue
    ): void {
        if (null === $updatableRelationship) {
            return;
        }

        $relationshipConditions = $updatableRelationship->getRelationshipConditions();

        if (is_iterable($propertyValue)) {
            // if to-many relationship prevent setting restricted items
            foreach (Iterables::asArray($propertyValue) as $key => $value) {
                if (!$this->conditionEvaluator->evaluateConditions($value, $relationshipConditions)) {
                    throw RelationshipAccessException::toManyWithRestrictedItemNotSetable($this->type, $propertyName, $deAliasedPropertyName, $key);
                }
            }
        } elseif (!$this->conditionEvaluator->evaluateConditions($propertyValue, $relationshipConditions)) {
            // if restricted to-one relationship
            throw RelationshipAccessException::toOneWithRestrictedItemNotSetable($this->type, $propertyName, $deAliasedPropertyName);
        }
    }

    /**
     * @return TEntity
     *
     * @internal Warning: exposing the backing object is dangerous, as it allows to read values
     * unrestricted not only from the returned object but all its relationships.
     */
    public function getObject(): object
    {
        return $this->object;
    }
}
