<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use function get_class;

class PropertyAccessException extends AccessException
{
    /**
     * @var non-empty-string
     */
    protected string $propertyName;

    /**
     * @param non-empty-string $property
     * @param non-empty-string ...$availableProperties
     */
    public static function propertyNotAvailableInType(string $property, TypeInterface $type, string ...$availableProperties): self
    {
        $typeClass = get_class($type);
        $propertyList = implode(', ', $availableProperties);
        $self = new self("No property '$property' is available in the type class '$typeClass'. Available properties are: $propertyList");
        $self->propertyName = $property;
        $self->typeClass = $typeClass;

        return $self;
    }

    /**
     * @param non-empty-string $property
     * @param non-empty-string ...$availableProperties
     */
    public static function propertyNotAvailableInReadableType(string $property, TransferableTypeInterface $type, string ...$availableProperties): self
    {
        $typeClass = get_class($type);
        $propertyList = implode(', ', $availableProperties);
        $self = new self("No property '$property' is available in the readable type class '$typeClass'. Available properties are: $propertyList");
        $self->propertyName = $property;
        $self->typeClass = $typeClass;

        return $self;
    }

    /**
     * @param non-empty-string          $property
     * @param TransferableTypeInterface $type
     * @param non-empty-string          ...$availableProperties
     */
    public static function propertyNotAvailableInUpdatableType(string $property, TransferableTypeInterface $type, string ...$availableProperties): self
    {
        $typeClass = get_class($type);
        $propertyList = implode(', ', $availableProperties);
        $self = new self("No property '$property' is available in the updatable type class '$typeClass'. Available properties are: $propertyList");
        $self->propertyName = $property;
        $self->typeClass = $typeClass;

        return $self;
    }

    /**
     * @param non-empty-string $property
     */
    public static function nonRelationship(string $property, TypeInterface $type): self
    {
        $typeClass = get_class($type);
        $self = new self("The property '$property' exists in the type class '$typeClass' but it is not a relationship and the path continues after it. Check your access to the schema of the type.");
        $self->propertyName = $property;
        $self->typeClass = $typeClass;

        return $self;
    }

    /**
     * @param PropertyAccessException          $previous
     * @param non-empty-list<non-empty-string> $path
     */
    public static function pathDenied(TypeInterface $type, PropertyAccessException $previous, array $path): self
    {
        $pathString = implode('.', $path);
        $typeClass = get_class($type);
        $propertyName = $previous->getPropertyName();
        $self = new self("Access with the path '$pathString' into the type class '$typeClass' was denied because of the path segment '$propertyName'.", 0, $previous);
        $self->typeClass = $typeClass;
        $self->propertyName = $propertyName;

        return $self;
    }

    /**
     * @return non-empty-string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
