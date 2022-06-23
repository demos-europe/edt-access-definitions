<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\Contracts\SliceException;
use EDT\Querying\Contracts\SortException;
use EDT\Querying\ObjectProviders\PrefilledObjectProvider;
use EDT\Querying\ObjectProviders\TypeRestrictedEntityProvider;
use EDT\Querying\Utilities\Iterables;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;
use Exception;
use function count;

/**
 * Provides helper methods to determine the access rights to properties of an entity based on the implementation of a given {@link TypeInterface}.
 */
class PropertyReader
{
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;
    /**
     * @var SchemaPathProcessor
     */
    private $schemaPathProcessor;

    /**
     * @param PropertyAccessorInterface<object> $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor, SchemaPathProcessor $schemaPathProcessor)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->schemaPathProcessor = $schemaPathProcessor;
    }

    /**
     * You can pass any value read from an entity into this method. If the property the
     * value was read from is expected to be a relationship you need to pass
     * a {@link ReadableTypeInterface}, otherwise you can set `$relationship` to `null`.
     *
     * If `$relationship` is `null` then the given `$propertyValue` will be returned.
     *
     * If `$relationship` is not `null` and the given `$propertyValue` is iterable then we
     * assume the relationship is a to-many. In this case every item in the iterable `$propertyValue`
     * will be wrapped using the given `$createWrapper` callback and the resulting array returned.
     *
     * If `$relationship` is not `null` and the given `$propertyValue` is not iterable then
     * we assume the relationship is a to-one. In this case the given `$createWrapper` callback
     * will be used on the `$propertyValue` and the result returned.
     *
     * In case of a relationship the `$createWrapper` callback will only be used on a value
     * if the {@link TypeInterface::getAccessCondition() access condition} of the given
     * `$relationship` allows the access to the value. Otherwise, for a to-many relationship
     * the value will be skipped (not wrapped or returned) and for a to-one relationship
     * instead of the wrapped value `null` will be returned.
     *
     * In case of a to-many relationship the entities will be sorted according to the definition
     * of {@link TypeInterface::getDefaultSortMethods()} of the relationship.
     *
     * @param callable $createWrapper callback corresponding to the {@link WrapperFactoryInterface::createWrapper()} method definition
     * @param ReadableTypeInterface|null $relationship
     * @param mixed $propertyValue
     * @return mixed
     *
     * @throws PathException
     * @throws SliceException
     * @throws SortException
     */
    public function determineValue(callable $createWrapper, ?ReadableTypeInterface $relationship, $propertyValue)
    {
        if (null === $relationship) {
            // if non-relationship simply use the value read from the target
            return $propertyValue;
        }

        if (is_iterable($propertyValue)) {
            return $this->getEntities($createWrapper, $relationship, Iterables::asArray($propertyValue));
        }

        if (null === $propertyValue) {
            return null;
        }

        $objects = $this->getEntities($createWrapper, $relationship, [$propertyValue]);

        switch (count($objects)) {
            case 1:
                // if available to-one relationship return the wrapped object
                return array_pop($objects);
            case 0:
                // if restricted to-one relationship use null instead
                return null;
            default:
                throw new Exception('Unexpected count: '.count($objects));
        }
    }

    /**
     * @template K of int|string
     *
     * @param array<K, object> $items must not contain `null` values
     *
     * @return array<K, mixed> the type of the values depends on the return of the given callable
     *
     * @throws SliceException
     * @throws PathException
     * @throws SortException
     */
    private function getEntities(callable $createWrapper, ReadableTypeInterface $relationship, array $items): array
    {
        // filter out restricted items
        $objectProvider = new PrefilledObjectProvider($this->propertyAccessor, $items);
        $objectProvider = new TypeRestrictedEntityProvider($objectProvider, $relationship, $this->schemaPathProcessor);
        $objects = Iterables::asArray($objectProvider->getObjects([]));

        // wrap the remaining objects
        return array_map(static function ($nestedValue) use ($createWrapper, $relationship) {
            return $createWrapper($nestedValue, $relationship);
        }, $objects);
    }
}
