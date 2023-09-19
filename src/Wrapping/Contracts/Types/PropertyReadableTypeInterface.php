<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\ResourceBehavior\ResourceReadability;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 */
interface PropertyReadableTypeInterface
{
    /**
     * Shall return all properties of this instance that are currently readable.
     *
     * The return may depend on the current state of the application and thus may change on consecutive calls.
     *
     * @return ResourceReadability<TCondition, TSorting, TEntity>
     */
    public function getReadability(): ResourceReadability;
}
