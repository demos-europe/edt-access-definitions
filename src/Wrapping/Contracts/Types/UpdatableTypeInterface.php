<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

/**
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends TypeInterface<TCondition, TSorting, TEntity>
 */
interface UpdatableTypeInterface extends TypeInterface
{
    /**
     * @param TEntity $updateTarget
     *
     * @return array<non-empty-string, non-empty-string|null>
     */
    public function getUpdatableProperties(object $updateTarget): array;
}
