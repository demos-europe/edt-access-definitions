<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Wrapping\Contracts\Types\TypeInterface;
use function get_class;
use function is_object;

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
        $typeClass = $type::class;
        $relationshipTypeIdentifier = $previous->getTypeClass();
        $self = new self($property, "Property '$property' is available and a relationship in the type class '$typeClass', but its destination type '$relationshipTypeIdentifier' is not accessible.", 0, $previous);
        $self->typeClass = $typeClass;
        $self->relationshipTypeIdentifier = $relationshipTypeIdentifier;
        $self->relationshipTypeClass = $previous->getTypeClass();

        return $self;
    }

    /**
     * @param non-empty-string $propertyName
     */
    public static function toManyWithRestrictedItemNotSetable(TypeInterface $type, string $propertyName, string $deAliasedPropertyName, int|string $key): self
    {
        $typeClass = $type::class;
        $self = new self($propertyName, "Can't set a list into the to-many relationship '$propertyName' (de-aliased to '$deAliasedPropertyName') in type class '$typeClass' if said list contains a non-accessible (due to their type class '$typeClass') items stored under the key '$key'.");
        $self->typeClass = $typeClass;

        return $self;
    }

    /**
     * @param non-empty-string $propertyName
     * @param non-empty-string $deAliasedPropertyName
     */
    public static function toOneWithRestrictedItemNotSetable(TypeInterface $type, string $propertyName, string $deAliasedPropertyName): self
    {
        $typeClass = $type::class;
        $self = new self($propertyName, "Can't set an object into the to-one relationship '$propertyName' (de-aliased to '$deAliasedPropertyName') in type class '$typeClass' if said object is non-accessible due to its type class '$typeClass'.");
        $self->typeClass = $typeClass;

        return $self;
    }

    /**
     * @param non-empty-string $propertyName
     */
    public static function toManyNotIterable(string $propertyName, mixed $actualValue): self
    {
        $dataType = gettype($actualValue);
        return new self($propertyName, "Attempted to use non-iterable data ('$dataType') for a to-many relationship property '$propertyName'.");
    }

    /**
     * @param non-empty-string $propertyName
     * @param class-string $expectedType
     */
    public static function toOneNeitherObjectNorNull(string $propertyName, string $expectedType, mixed $actualValue): self
    {
        $additionalMessage = self::getToOneMessageType($actualValue);
        return new self($propertyName, "Attempted to use a value that was neither `null` nor the expected entity type '$expectedType' for a relationship property '$propertyName'. $additionalMessage");
    }

    /**
     * @param non-empty-string $propertyName
     * @param class-string $entityClass
     */
    public static function toManyIterableInvalidEntity(string $propertyName, string $entityClass, mixed $actualValue): self
    {
        $additionalMessage = self::getToOneMessageType($actualValue);
        return new self($propertyName, "Iterable in to-many relationship '$propertyName' does not contain '$entityClass' instances only. $additionalMessage");
    }

    /**
     * @return non-empty-string
     */
    private static function getToOneMessageType(mixed $actualValue): string
    {
        if (is_object($actualValue)) {
            $actualClass = get_class($actualValue);
            return "Found object with type '$actualClass'.";
        }

        $actualType = gettype($actualValue);

        return "Found non-object with type '$actualType'.";
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
