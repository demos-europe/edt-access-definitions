<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

class TypeRetrievalAccessException extends AccessException
{
    /**
     * @param non-empty-string       $typeIdentifier
     * @param list<non-empty-string> $reasons
     */
    public static function notPresent(string $typeIdentifier, array $reasons): self
    {
        $reasonsString = implode(' ', $reasons);

        return new self("Type instance with identifier '$typeIdentifier' matching the defined criteria was not found due to the following reasons: $reasonsString");
    }
}
