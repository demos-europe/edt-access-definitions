<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Wrapping\Contracts\Types\TypeInterface;
use function get_class;

class RelationshipAccessException extends PropertyAccessException
{
    /**
     * @var class-string<TypeInterface>|string
     */
    protected $relationshipTypeClassOrIdentifier;

    public static function relationshipTypeAccess(TypeInterface $type, string $property, TypeRetrievalAccessException $previous): self
    {
        $typeClass = get_class($type);
        $relationshipTypeIdentifier = $previous->getTypeIdentifier();
        $self = new self("Property '$property' is available and a relationship in the type class '$typeClass', but its destination type '$relationshipTypeIdentifier' is not accessible.", 0, $previous);
        $self->propertyName = $property;
        $self->typeClassOrIdentifier = $typeClass;
        $self->relationshipTypeClassOrIdentifier = $relationshipTypeIdentifier;

        return $self;
    }

    /**
     * @param int|string $key
     */
    public static function toManyWithRestrictedItemNotSetable(TypeInterface $type, string $propertyName, string $deAliasedPropertyName, TypeInterface $relationshipType, $key): self
    {
        $relationshipTypeClass = get_class($relationshipType);
        $typeClass = get_class($type);
        $self = new self("Can't set a list into the to-many relationship '$propertyName' (de-aliased to '$deAliasedPropertyName') in type class '$typeClass' if said list contains a non-accessible (due to their type class '$typeClass') items stored under the key '$key'.");
        $self->propertyName = $propertyName;
        $self->typeClassOrIdentifier = $typeClass;
        $self->relationshipTypeClassOrIdentifier = $relationshipTypeClass;

        return $self;
    }

    public static function toOneWithRestrictedItemNotSetable(TypeInterface $type, string $propertyName, string $deAliasedPropertyName, TypeInterface $relationshipType): self
    {
        $relationshipTypeClass = get_class($relationshipType);
        $typeClass = get_class($type);
        $self = new self("Can't set an object into the to-one relationship '$propertyName' (de-aliased to '$deAliasedPropertyName') in type class '$typeClass' if said object is non-accessible due to its type class '$typeClass'.");
        $self->propertyName = $propertyName;
        $self->typeClassOrIdentifier = $typeClass;
        $self->relationshipTypeClassOrIdentifier = $relationshipTypeClass;

        return $self;
    }

    /**
     * @return class-string<TypeInterface>|string
     */
    public function getRelationshipTypeClassOrIdentifier(): string
    {
        return $this->relationshipTypeClassOrIdentifier;
    }
}
