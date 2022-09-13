<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

/**
 * @template C of \EDT\Querying\Contracts\PathsBasedInterface
 * @template S of \EDT\Querying\Contracts\PathsBasedInterface
 * @template T of object
 *
 * @template-extends TypeInterface<C, S, T>
 */
interface UpdatableTypeInterface extends TypeInterface
{
    /**
     * @param T $updateTarget
     *
     * @return array<non-empty-string, non-empty-string|null>
     */
    public function getUpdatableProperties(object $updateTarget): array;
}
