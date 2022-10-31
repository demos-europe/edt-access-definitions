<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Wrapping\Contracts\Types\TypeInterface;
use function get_class;

class RelationshipAccessException extends PropertyAccessException
{
    /**
     * @var non-empty-string|null
     */
    protected ?string $relationshipTypeIdentifier = null;

    /**
     * @var class-string<TypeInterface>|null
     */
    protected ?string $relationshipTypeClass = null;

    /**
     * @param non-empty-string $property
     */
    public static function relationshipTypeAccess(TypeInterface $type, string $property, TypeRetrievalAccessException $previous): self
    {
        $typeClass = get_class($type);
        $relationshipTypeIdentifier = $previous->getTypeClass();
        $self = new self("Property '$property' is available and a relationship in the type class '$typeClass', but its destination type '$relationshipTypeIdentifier' is not accessible.", 0, $previous);
        $self->propertyName = $property;
        $self->typeClass = $typeClass;
        $self->relationshipTypeIdentifier = $relationshipTypeIdentifier;
        $self->relationshipTypeClass = $previous->getTypeClass();

        return $self;
    }

    /**
     * @param non-empty-string $propertyName
     * @param int|string       $key
     */
    public static function toManyWithRestrictedItemNotSetable(TypeInterface $type, string $propertyName, string $deAliasedPropertyName, TypeInterface $relationshipType, $key): self
    {
        $relationshipTypeClass = get_class($relationshipType);
        $typeClass = get_class($type);
        $self = new self("Can't set a list into the to-many relationship '$propertyName' (de-aliased to '$deAliasedPropertyName') in type class '$typeClass' if said list contains a non-accessible (due to their type class '$typeClass') items stored under the key '$key'.");
        $self->propertyName = $propertyName;
        $self->typeClass = $typeClass;
        $self->relationshipTypeIdentifier = $relationshipTypeClass;

        return $self;
    }

    /**
     * @param non-empty-string $propertyName
     * @param non-empty-string $deAliasedPropertyName
     */
    public static function toOneWithRestrictedItemNotSetable(TypeInterface $type, string $propertyName, string $deAliasedPropertyName, TypeInterface $relationshipType): self
    {
        $relationshipTypeClass = get_class($relationshipType);
        $typeClass = get_class($type);
        $self = new self("Can't set an object into the to-one relationship '$propertyName' (de-aliased to '$deAliasedPropertyName') in type class '$typeClass' if said object is non-accessible due to its type class '$typeClass'.");
        $self->propertyName = $propertyName;
        $self->typeClass = $typeClass;
        $self->relationshipTypeIdentifier = $relationshipTypeClass;

        return $self;
    }

    /**
     * @return non-empty-string|null
     */
    public function getRelationshipTypeIdentifier(): ?string
    {
        return $this->relationshipTypeIdentifier;
    }

    /**
     * @return class-string<TypeInterface>|null
     */
    public function getRelationshipTypeClass(): ?string
    {
        return $this->relationshipTypeClass;
    }
}
