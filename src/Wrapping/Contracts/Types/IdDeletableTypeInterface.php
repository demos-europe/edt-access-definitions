<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

interface IdDeletableTypeInterface
{
    public function deleteEntityByIdentifier(string $entityId): void;
}
