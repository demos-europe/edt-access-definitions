<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;
use function implode;

class TypeRetrievalAccessException extends AccessException
{
    /**
     * @param non-empty-string $typeIdentifier
     * @param list<non-empty-string> $availableTypeIdentifiers
     */
    public static function unknownTypeIdentifier(string $typeIdentifier, array $availableTypeIdentifiers): self
    {
        $typeIdentifiersString = implode(', ', $availableTypeIdentifiers);
        $self = new self("Type identifier '$typeIdentifier' not known. Known type identifiers are: $typeIdentifiersString.");
        $self->typeIdentifier = $typeIdentifier;

        return $self;
    }

    /**
     * @template T of \EDT\Wrapping\Contracts\Types\TypeInterface
     *
     * @param non-empty-string $typeIdentifier
     * @param class-string<T> $implementation
     */
    public static function noNameWithImplementation(string $typeIdentifier, string $implementation): self
    {
        $self = new self("Type with identifier '$typeIdentifier' exists but does not implement $implementation.");
        $self->typeIdentifier = $typeIdentifier;

        return $self;
    }

    /**
     * @param non-empty-string $typeIdentifier
     */
    public static function typeExistsButNotAvailable(string $typeIdentifier): self
    {
        $self = new self("Type '$typeIdentifier' exists but is not available.");
        $self->typeIdentifier = $typeIdentifier;

        return $self;
    }
}
