<?php

declare(strict_types=1);

namespace EDT\Wrapping\WrapperFactories;

use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\Contracts\SliceException;
use EDT\Querying\Contracts\SortException;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Utilities\PropertyReader;
use EDT\Wrapping\Utilities\TypeAccessor;
use InvalidArgumentException;

/**
 * @template-implements WrapperFactoryInterface<object,array<string,mixed>>
 */
class WrapperArrayFactory implements WrapperFactoryInterface
{
    /**
     * @var PropertyAccessorInterface<object>
     */
    private $propertyAccessor;
    /**
     * @var int
     */
    private $depth;
    /**
     * @var TypeAccessor
     */
    private $typeAccessor;
    /**
     * @var PropertyReader
     */
    private $propertyReader;

    /**
     * @param PropertyAccessorInterface<object> $propertyAccessor
     * @throws InvalidArgumentException Thrown if the given depth is negative.
     */
    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        PropertyReader $propertyReader,
        TypeAccessor $typeAccessor,
        int $depth
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->typeAccessor = $typeAccessor;
        if (0 > $depth) {
            throw new InvalidArgumentException("Depth must be 0 or positive, is $depth");
        }
        $this->depth = $depth;
        $this->propertyReader = $propertyReader;
    }

    public function createWrapper(object $object, ReadableTypeInterface $type): array
    {
        return $this->createWrapperArray($object, $type, $this->depth);
    }

    /**
     * Converts the given object into an array with the objects property names as array keys and the
     * property values as array values. Only properties that are defined as readable by
     * {@link ReadableTypeInterface::getReadableProperties()} are included. Relationships to
     * other types will be copied recursively in the same manner, but only if they're
     * allowed to be accessed (depends on their {@link ReadableTypeInterface::isAvailable()},
     * {@link TypeInterface::getAccessCondition()} and {@link TypeInterface::isReferencable()} methods,
     * all must return `true` for the property to be included.
     *
     * The recursion stops when the specified depth is reached.
     *
     * If for example the specified depth is 0 and the given type is a Book with a
     * `title` string property and an author relationship to another type then
     * (assuming all properties are accessible as defined above) an array with the keys `title` and `author`
     * will be returned with `title` being a string and `author` being `null`.
     *
     * Assuming the `title` property was not readable then it would not be present in the
     * returned array at all.
     *
     * If `$depth` would have been `1` then the value for `author` would be an array with all
     * accessible properties of the `author` type as keys. However the recursion
     * would stop at the author and the values to relationships from the `author` property
     * to other types would be set to `null`.
     *
     * @return array<string,mixed>|null `null` if $depth is less 0. Otherwise an array containing
     *                                  the readable properties of the given type.
     * @throws AccessException Thrown if $type is not available.
     */
    protected function createWrapperArray(object $target, ReadableTypeInterface $type, int $depth): ?array
    {
        if (0 > $depth) {
            return null;
        }

        if (!$type->isAvailable()) {
            throw AccessException::typeNotAvailable($type);
        }

        // we only include properties in the result array that are actually accessible
        $readableProperties = $this->typeAccessor->getAccessibleReadableProperties($type);

        // Set the actual value for each remaining property
        array_walk($readableProperties, [$this, 'setValue'], [$target, $depth, $type->getAliases()]);
        return $readableProperties;
    }

    /**
     * Each null $value corresponding to the property given with the $propertyName will be replaced
     * with the value read using the property path corresponding to the $propertyName.
     *
     * For each relationship the same will be done but additionally it will be recursively
     * wrapped using this factory until $depth is reached. If access is not granted due to the
     * settings in the corresponding {@link TypeInterface::getAccessCondition()} it will be
     * replaced by `null`.
     *
     * If a to-many relationship is referenced each value will be checked using {@link TypeInterface::getAccessCondition()}
     * if it should be included, if so it is wrapped using this factory and included in the result.
     *
     * @param ReadableTypeInterface|null $value If not null the type must be {@link TypeInterface::isAvailable() available} and {@link TypeInterface::isReferencable() referencable}.
     *
     * @param array<int, mixed> $context
     *
     * @throws PathException
     * @throws SliceException
     * @throws SortException
     */
    private function setValue(?ReadableTypeInterface &$value, string $propertyName, array $context): void
    {
        [$target, $depth, $aliases] = $context;
        $propertyPath = $aliases[$propertyName] ?? [$propertyName];
        $propertyValue = [] === $propertyPath
            ? $target
            : $this->propertyAccessor->getValueByPropertyPath($target, ...$propertyPath);
        $value = $this->propertyReader->determineValue(
            function (object $value, ReadableTypeInterface $relationship) use ($depth): ?array {
                return $this->createWrapperArray($value, $relationship, $depth - 1);
            },
            $value,
            $propertyValue
        );
    }
}
