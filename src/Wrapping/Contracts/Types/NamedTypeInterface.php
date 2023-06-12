<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

interface NamedTypeInterface
{
    /**
     * @return non-empty-string
     */
    public function getTypeName(): string;
}
