<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

/**
 * @template TEntity of object
 */
interface UpdatableTypeInterface
{
    /**
     * @param TEntity $updateTarget
     *
     * @return array<non-empty-string, non-empty-string|null>
     */
    public function getUpdatableProperties(object $updateTarget): array;
}
