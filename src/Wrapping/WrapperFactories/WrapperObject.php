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
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Contracts\Types\UpdatableTypeInterface;
use EDT\Wrapping\Utilities\PropertyReader;
use EDT\Wrapping\Utilities\TypeAccessor;
use InvalidArgumentException;
use function array_key_exists;
use function count;
use function is_array;
use function Safe\preg_match;

/**
 * Wraps a given object, corresponding to a {@link TypeInterface}.
 *
 * Instances will provide read and write access to specific properties of the given object.
 *
 * Read access will only be granted if the given {@link TypeInterface} implements {@link ReadableTypeInterface}.
 * The properties allowed to be read depend on the return of {@link ReadableTypeInterface::getReadableProperties()}.
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
     * @var TypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity>
     */
    private TypeInterface $type;

    /**
     * @var TypeAccessor<FunctionInterface<bool>, SortMethodInterface>
     */
    private TypeAccessor $typeAccessor;

    private PropertyAccessorInterface $propertyAccessor;

    private PropertyReader $propertyReader;

    private ConditionEvaluator $conditionEvaluator;

    private WrapperObjectFactory $wrapperFactory;

    /**
     * @param TEntity                                                              $object
     * @param TypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity> $type
     * @param TypeAccessor<FunctionInterface<bool>, SortMethodInterface>     $typeAccessor
     */
    public function __construct(
        object                    $object,
        PropertyReader            $propertyReader,
        TypeInterface             $type,
        TypeAccessor              $typeAccessor,
        PropertyAccessorInterface $propertyAccessor,
        ConditionEvaluator        $conditionEvaluator,
        WrapperObjectFactory      $wrapperFactory
    ) {
        $this->object = $object;
        $this->type = $type;
        $this->typeAccessor = $typeAccessor;
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
        if (!$this->type instanceof ReadableTypeInterface) {
            throw AccessException::typeNotReadable($this->type);
        }

        // we allow reading of properties that are actually accessible
        $readableProperties = $this->typeAccessor->getAccessibleReadableProperties($this->type);
        if (!array_key_exists($propertyName, $readableProperties)) {
            throw PropertyAccessException::propertyNotAvailableInReadableType($propertyName, $this->type, ...array_keys($readableProperties));
        }

        // Get the potentially wrapped value for the requested property
        $relationship = $readableProperties[$propertyName];
        $propertyPath = $this->mapProperty($propertyName);
        // TODO: this should probably be `1 === count($propertyPath)`
        $propertyValue = [] === $propertyPath
            ? $this->object
            : $this->propertyAccessor->getValueByPropertyPath($this->object, ...$propertyPath);

        if (null === $relationship) {
            // if non-relationship, simply use the value read from the target
            return $propertyValue;
        }

        $entityOrEntities = $this->propertyReader->determineRelationshipValue($relationship, $propertyValue);
        if (null === $entityOrEntities) {
            return null;
        }

        if (is_array($entityOrEntities)) {
            // wrap the entities
            return array_map(
                fn (object $objectToWrap) => $this->wrapperFactory->createWrapper($objectToWrap, $relationship),
                $entityOrEntities
            );
        }

        return $this->wrapperFactory->createWrapper($entityOrEntities, $relationship);
    }

    /**
     * @param non-empty-string $propertyName
     * @param mixed $value The value to set. Will only be allowed if the property name matches with an allowed property
     *                     (must be {@link UpdatableTypeInterface::getUpdatableProperties() updatable} and
     *                     (if it is a relationship) the target type of the relationship returns `true` in
     *                     {@link ExposableRelationshipTypeInterface::isExposedAsRelationship()}.
     *
     * @throws AccessException
     */
    public function __set(string $propertyName, $value): void
    {
        if (!$this->type instanceof UpdatableTypeInterface) {
            throw AccessException::typeNotUpdatable($this->type);
        }

        // we allow writing of properties that are actually accessible
        $updatableProperties = $this->typeAccessor->getAccessibleUpdatableProperties($this->type, $this->object);
        if (!array_key_exists($propertyName, $updatableProperties)) {
            throw PropertyAccessException::propertyNotAvailableInUpdatableType($propertyName, $this->type, ...array_keys($updatableProperties));
        }

        $relationship = $updatableProperties[$propertyName];
        $propertyPath = $this->mapProperty($propertyName);
        $deAliasedPropertyName = array_pop($propertyPath);
        $target = [] === $propertyPath
            ? $this->object
            : $this->propertyAccessor->getValueByPropertyPath($this->object, ...$propertyPath);

        // at this point we ensured the relationship type is available and referencable but still
        // need to check the access conditions
        $this->throwIfNotSetable($relationship, $propertyName, $deAliasedPropertyName, $value);
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
     * information like {@link ReadableTypeInterface::getReadableProperties() readable} or
     * {@link UpdatableTypeInterface::getUpdatableProperties() updatable} properties.
     *
     * @param TypeInterface<FunctionInterface<bool>, SortMethodInterface, object>|null $relationship
     * @param non-empty-string                                    $propertyName
     * @param non-empty-string                                    $deAliasedPropertyName
     * @param mixed                                               $propertyValue         A single value of some type or an iterable.
     *
     * @throws AccessException
     */
    protected function throwIfNotSetable(?TypeInterface $relationship, string $propertyName, string $deAliasedPropertyName, $propertyValue): void
    {
        if (null === $relationship) {
            // if non-relationship we do not restrict
            return;
        }

        $condition = $relationship->getAccessCondition();
        if (is_iterable($propertyValue)) {
            // if to-many relationship prevent setting restricted items
            foreach (Iterables::asArray($propertyValue) as $key => $value) {
                if (!$this->conditionEvaluator->evaluateCondition($value, $condition)) {
                    throw RelationshipAccessException::toManyWithRestrictedItemNotSetable($this->type, $propertyName, $deAliasedPropertyName, $relationship, $key);
                }
            }
        } elseif (!$this->conditionEvaluator->evaluateCondition($propertyValue, $condition)) {
            // if restricted to-one relationship
            throw RelationshipAccessException::toOneWithRestrictedItemNotSetable($this->type, $propertyName, $deAliasedPropertyName, $relationship);
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
