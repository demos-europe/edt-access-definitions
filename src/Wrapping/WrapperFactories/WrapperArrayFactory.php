<?php

declare(strict_types=1);

namespace EDT\Wrapping\WrapperFactories;

use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\Contracts\PaginationException;
use EDT\Querying\Contracts\SortException;
use EDT\Querying\Contracts\SortMethodInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\Types\AliasableTypeInterface;
use EDT\Wrapping\Contracts\Types\ExposableRelationshipTypeInterface;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Utilities\PropertyReader;
use InvalidArgumentException;

/**
 * @template-implements WrapperFactoryInterface<FunctionInterface<bool>, SortMethodInterface>
 */
class WrapperArrayFactory implements WrapperFactoryInterface
{
    private PropertyAccessorInterface $propertyAccessor;

    /**
     * @var int<0, max>
     */
    private int $depth;

    private PropertyReader $propertyReader;

    /**
     * @param int<0, max> $depth
     *
     * @throws InvalidArgumentException Thrown if the given depth is negative.
     */
    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        PropertyReader $propertyReader,
        int $depth
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->depth = $depth;
        $this->propertyReader = $propertyReader;
    }

    /**
     * Converts the given object into an array with the object's property names as array keys and the
     * property values as array values. Only properties that are defined as readable by
     * {@link TransferableTypeInterface::getReadableProperties()} are included. Relationships to
     * other types will be copied recursively in the same manner, but only if they're
     * allowed to be accessed. If they are allowed to be accessed depends on their
     * {@link ExposableRelationshipTypeInterface::isExposedAsRelationship()} and
     * {@link TypeInterface::getAccessCondition()} methods, both must return `true` for the property to be included.
     *
     * The recursion stops when the specified depth in {@link WrapperArrayFactory::$depth} is reached.
     *
     * If for example the specified depth is 0 and the given type is a Book with a
     * `title` string property and an author relationship to another type then
     * (assuming all properties are accessible as defined above) an array with the keys `title` and `author`
     * will be returned with `title` being a string and `author` being `null`.
     *
     * Assuming the `title` property was not readable then it would not be present in the
     * returned array at all.
     *
     * If depth is set to `1` then the value for `author` would be an array with all
     * accessible properties of the `author` type as keys. However, the recursion
     * would stop at the author and the values to relationships from the `author` property
     * to other types would be set to `null`.
     *
     * @param TransferableTypeInterface<FunctionInterface<bool>, SortMethodInterface, object> $type
     *
     * @return array<non-empty-string, mixed> an array containing the readable properties of the given type
     *
     * @throws AccessException Thrown if $type is not available.
     */
    public function createWrapper(object $entity, TransferableTypeInterface $type): array
    {
        // we only include properties in the result array that are actually accessible
        $readableProperties = $type->getReadableProperties();

        $aliases = $type instanceof AliasableTypeInterface ? $type->getAliases() : [];

        // Set the actual value for each remaining property
        array_walk($readableProperties, [$this, 'setValue'], [$entity, $this->depth, $aliases]);

        return $readableProperties;
    }

    /**
     * Each null $value corresponding to the property given with the $propertyName will be replaced
     * with the value read using the property path corresponding to the $propertyName.
     *
     * For each relationship the same will be done, but additionally it will be recursively
     * wrapped using this factory until $depth is reached. If access is not granted due to the
     * settings in the corresponding {@link TypeInterface::getAccessCondition()} it will be
     * replaced by `null`.
     *
     * If a to-many relationship is referenced each value will be checked using {@link TypeInterface::getAccessCondition()}
     * if it should be included, if so it is wrapped using this factory and included in the result.
     *
     * @param TransferableTypeInterface<FunctionInterface<bool>, SortMethodInterface, object>|null   $value
     * @param array{0: object, 1: int, 2: array<non-empty-string, non-empty-list<non-empty-string>>} $context
     *
     * @throws PathException
     * @throws PaginationException
     * @throws SortException
     */
    private function setValue(?TransferableTypeInterface &$value, string $propertyName, array $context): void
    {
        [$target, $depth, $aliases] = $context;
        $propertyPath = $aliases[$propertyName] ?? [$propertyName];
        $propertyValue = $this->propertyAccessor->getValueByPropertyPath($target, ...$propertyPath);

        $newDepth = $depth - 1;
        $wrapperFactory = 0 > $newDepth
            ? new ArrayEndWrapperFactory()
            : new self($this->propertyAccessor, $this->propertyReader, $newDepth);

        if (null === $value) {
            // if non-relationship, simply use the value read from the target
            $value = $propertyValue;
        } elseif (is_iterable($propertyValue)) {
            $entities = $this->propertyReader->determineToManyRelationshipValue($value, $propertyValue);

            // wrap the entities
            $value = array_map(
                static fn (object $objectToWrap) => $wrapperFactory->createWrapper($objectToWrap, $value),
                $entities
            );
        } else {
            $entity = $this->propertyReader->determineToOneRelationshipValue($value, $propertyValue);
            $value = null === $entity ? null : $wrapperFactory->createWrapper($entity, $value);
        }
    }
}
