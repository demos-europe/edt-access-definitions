<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Wrapping\Contracts\Types\NamedTypeInterface;
use RuntimeException;
use Throwable;

class ExternReadableRelationshipSchemaVerificationException extends RuntimeException
{
    /**
     * @param NamedTypeInterface $type
     * @param non-empty-list<non-empty-string> $path
     * @param Throwable $previous
     */
    public function __construct(
        protected readonly NamedTypeInterface $type,
        protected readonly array $path,
        Throwable $previous
    ) {
        parent::__construct('Failed to verify correct external usage of readable relationship', 0, $previous);
    }

    public function getType(): NamedTypeInterface
    {
        return $this->type;
    }

    /**
     * @return non-empty-list<non-empty-string>
     */
    public function getPath(): array
    {
        return $this->path;
    }
}
