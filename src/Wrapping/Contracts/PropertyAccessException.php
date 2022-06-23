<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Contracts\Types\UpdatableTypeInterface;
use function get_class;

class PropertyAccessException extends AccessException
{
    /**
     * @var string
     */
    protected $propertyName;

    public static function propertyNotAvailableInType(string $property, TypeInterface $type, string ...$availableProperties): self
    {
        $typeClass = get_class($type);
        $propertyList = implode(', ', $availableProperties);
        $self = new self("No property '$property' is available in the type class '$typeClass'. Available properties are: $propertyList");
        $self->propertyName = $property;
        $self->typeClassOrIdentifier = $typeClass;

        return $self;
    }

    public static function propertyNotAvailableInReadableType(string $property, ReadableTypeInterface $type, string ...$availableProperties): self
    {
        $typeClass = get_class($type);
        $propertyList = implode(', ', $availableProperties);
        $self = new self("No property '$property' is available in the readable type class '$typeClass'. Available properties are: $propertyList");
        $self->propertyName = $property;
        $self->typeClassOrIdentifier = $typeClass;

        return $self;
    }

    public static function propertyNotAvailableInUpdatableType(string $property, UpdatableTypeInterface $type, string ...$availableProperties): self
    {
        $typeClass = get_class($type);
        $propertyList = implode(', ', $availableProperties);
        $self = new self("No property '$property' is available in the updatable type class '$typeClass'. Available properties are: $propertyList");
        $self->propertyName = $property;
        $self->typeClassOrIdentifier = $typeClass;

        return $self;
    }

    public static function nonRelationship(string $property, TypeInterface $type): self
    {
        $typeClass = get_class($type);
        $self = new self("The property '$property' exists in the type class '$typeClass' but it is not a relationship and the path continues after it. Check your access to the schema of the type.");
        $self->propertyName = $property;
        $self->typeClassOrIdentifier = $typeClass;

        return $self;
    }

    public static function pathDenied(TypeInterface $type, PropertyAccessException $previous, string ...$path): self
    {
        $pathString = implode('.', $path);
        $typeClass = get_class($type);
        $propertyName = $previous->getPropertyName();
        $self = new self("Access with the path '$pathString' into the type class '$typeClass' was denied because of the path segment '$propertyName'.", 0, $previous);
        $self->typeClassOrIdentifier = $typeClass;
        $self->propertyName = $propertyName;

        return $self;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
