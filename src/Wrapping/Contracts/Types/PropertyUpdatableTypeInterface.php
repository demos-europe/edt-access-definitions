<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\ResourceBehavior\ResourceUpdatability;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 */
interface PropertyUpdatableTypeInterface
{
    /**
     * Shall return all properties of this instance that are currently updatable.
     *
     * The return may depend on the current state of the application and thus may change on consecutive calls.
     *
     * Implementations must return the nested arrays with keys that do not conflict with each other.
     *
     * Hint: You can merge the returned nested arrays via `array_merge(...$type->getUpdatableProperties())`.
     *
     * @return ResourceUpdatability<TCondition, TSorting, TEntity>
     */
    public function getUpdatability(): ResourceUpdatability;
}
