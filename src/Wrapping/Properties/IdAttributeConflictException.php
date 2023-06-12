<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use Exception;

class IdAttributeConflictException extends Exception
{
    /**
     * @param non-empty-string $typeIdentifier
     */
    public static function create(string $typeIdentifier): self
    {
        return new self("An attribute MUST NOT be named 'id'. Found in type with identifier '$typeIdentifier'.");
    }
}
