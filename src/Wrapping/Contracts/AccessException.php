<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Wrapping\Contracts\Types\IdentifiableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use InvalidArgumentException;
use function get_class;

class AccessException extends InvalidArgumentException
{
    /**
     * @var class-string<TypeInterface>|null
     */
    protected $typeClass;

    /**
     * @var non-empty-string|null
     */
    protected $typeIdentifier;

    /**
     * @param TypeInterface $type
     */
    public static function typeNotDirectlyAccessible(TypeInterface $type): self
    {
        $typeClass = get_class($type);
        $self = new self("Type '$typeClass' not directly accessible.");
        $self->typeClass = $typeClass;

        return $self;
    }

    public static function unexpectedArguments(TypeInterface $type, int $expected, int $actual): self
    {
        $typeClass = get_class($type);
        $self = new self("Unexpected arguments received for type class '$typeClass'. Expected $expected arguments, got $actual arguments.");
        $self->typeClass = $typeClass;

        return $self;
    }

    public static function typeNotAvailable(TypeInterface $type): self
    {
        $typeClass = get_class($type);
        $self = new self("Type class '$typeClass' not available.");
        $self->typeClass = $typeClass;

        return $self;
    }

    public static function typeNotReadable(TypeInterface $type): self
    {
        $typeClass = get_class($type);
        $self = new self("The type class you try to access is not readable: $typeClass");
        $self->typeClass = $typeClass;

        return $self;
    }

    public static function typeNotUpdatable(TypeInterface $type): self
    {
        $typeClass = get_class($type);
        $self = new self("The type class you try to access is not updatable: $typeClass");
        $self->typeClass = $typeClass;

        return $self;
    }

    public static function multipleEntitiesByIdentifier(IdentifiableTypeInterface $type): self
    {
        $typeClass = get_class($type);
        $self = new self("Multiple entities were found for the given identifier when accessing type class '$typeClass'. The identifier must be unique.");
        $self->typeClass = $typeClass;

        return $self;
    }

    public static function noEntityByIdentifier(IdentifiableTypeInterface $type): self
    {
        $typeClass = get_class($type);
        $self = new self("No entity could be found when accessing type class '$typeClass'. Either no one exists for the given identifier or the given types access condition restricts the access.");
        $self->typeClass = $typeClass;

        return $self;
    }

    /**
     * @param non-empty-string $methodName
     */
    public static function failedToParseAccessor(TypeInterface $type, string $methodName): self
    {
        $typeClass = get_class($type);
        $self = new self("The method you've called is not supported: '$methodName'");
        $self->typeClass = $typeClass;

        return $self;
    }

    /**
     * @return class-string<TypeInterface>|null
     */
    public function getTypeClass(): ?string
    {
        return $this->typeClass;
    }

    /**
     * @return non-empty-string|null
     */
    public function getTypeIdentifier(): ?string
    {
        return $this->typeIdentifier;
    }
}
