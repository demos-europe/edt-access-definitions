<?php

declare(strict_types=1);

namespace EDT\Wrapping\WrapperFactories;

use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\Contracts\SortException;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\ContentField;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Attribute\AttributeReadabilityInterface;
use EDT\Wrapping\PropertyBehavior\IdAttributeConflictException;
use EDT\Wrapping\PropertyBehavior\Identifier\IdentifierReadabilityInterface;
use InvalidArgumentException;
use function array_key_exists;

/**
 * Creates a wrapper around an instance of a {@link EntityBasedInterface::getEntityClass() backing object}.
 */
class WrapperArrayFactory
{
    /**
     * @param int<0, max> $depth
     *
     * @throws InvalidArgumentException Thrown if the given depth is negative.
     */
    public function __construct(
        protected readonly PropertyAccessorInterface $propertyAccessor,
        protected readonly int $depth
    ) {}

    /**
     * Converts the given object into an `array` with the object's property names as array keys and the
     * property values as array values. Only properties that are defined as readable by
     * {@link TransferableTypeInterface::getReadability()} are included. Relationships to
     * other types will be copied recursively in the same manner, but only if access is granted.
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
     * Each attribute value corresponding to the readable property name will be replaced
     * with the value read using the property path corresponding to the $propertyName.
     *
     * For each relationship the same will be done, but additionally it will be recursively
     * wrapped using this factory until the depth set in this instance is reached.
     * If access is not granted it will be replaced by `null`.
     *
     * @param TransferableTypeInterface<object> $type
     *
     * @return array<non-empty-string, mixed> an array containing the readable properties of the given type
     *
     * @throws AccessException Thrown if $type is not available.
     * @throws PathException
     * @throws SortException
     */
    public function createWrapper(object $entity, TransferableTypeInterface $type): array
    {
        // we only include properties in the result array that are actually accessible
        $readableProperties = $type->getReadability();

        // TODO (#153): respect $readability settings (default field, default include)?
        // TODO (#153): add sparse fieldset support

        $idReadability = $readableProperties->getIdentifierReadability();
        $attributes = $readableProperties->getAttributes();
        if (array_key_exists(ContentField::ID, $attributes)) {
            throw IdAttributeConflictException::create($type->getTypeName());
        }
        $attributes[ContentField::ID] = $idReadability;

        $wrapperArray = array_map(
            static fn (AttributeReadabilityInterface|IdentifierReadabilityInterface $readability) => $readability->getValue($entity),
            $attributes
        );

        $relationshipWrapperFactory = $this->getNextWrapperFactory();

        foreach ($readableProperties->getToOneRelationships() as $propertyName => $readability) {
            $targetEntity = $readability->getValue($entity, []);
            if (null === $targetEntity) {
                $wrapperArray[$propertyName] = null;
            } else {
                $relationshipType = $readability->getRelationshipType();
                $wrapperArray[$propertyName] = $relationshipWrapperFactory
                    ->createWrapper($targetEntity, $relationshipType);
            }
        }

        foreach ($readableProperties->getToManyRelationships() as $propertyName => $readability) {
            $relationshipType = $readability->getRelationshipType();
            $relationshipEntities = $readability->getValue($entity, [], []);
            $wrapperArray[$propertyName] = array_map(
                static fn (object $objectToWrap): ?array => $relationshipWrapperFactory
                    ->createWrapper($objectToWrap, $relationshipType),
                $relationshipEntities
            );
        }

        return $wrapperArray;
    }

    protected function getNextWrapperFactory(): self|ArrayEndWrapperFactory
    {
        $newDepth = $this->depth - 1;

        return 0 > $newDepth
            ? new ArrayEndWrapperFactory()
            : new self($this->propertyAccessor, $newDepth);
    }
}
