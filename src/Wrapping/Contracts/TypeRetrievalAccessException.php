<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;
use function implode;

class TypeRetrievalAccessException extends AccessException
{
    /**
     * @param array<int, string> $availableTypeIdentifiers
     */
    public static function unknownTypeIdentifier(string $typeIdentifier, array $availableTypeIdentifiers): self
    {
        $typeIdentifiersString = implode(', ', $availableTypeIdentifiers);
        $self = new self("Type identifier '$typeIdentifier' not known. Known type identifiers are: $typeIdentifiersString.");
        $self->typeClassOrIdentifier = $typeIdentifier;

        return $self;
    }

    /**
     * @template T of \EDT\Wrapping\Contracts\Types\TypeInterface<object>
     * @param class-string<T> $implementation
     */
    public static function noNameWithImplementation(string $typeIdentifier, string $implementation): self
    {
        $self = new self("Type with identifier '$typeIdentifier' exists but does not implement $implementation.");
        $self->typeClassOrIdentifier = $typeIdentifier;

        return $self;
    }

    public static function typeExistsButNotAvailable(string $typeIdentifier): self
    {
        $self = new self("Type '$typeIdentifier' exists but is not available.");
        $self->typeClassOrIdentifier = $typeIdentifier;

        return $self;
    }

    public function getTypeIdentifier(): string
    {
        return $this->typeClassOrIdentifier;
    }
}
