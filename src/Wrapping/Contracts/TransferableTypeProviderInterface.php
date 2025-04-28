<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 */
interface TransferableTypeProviderInterface
{
    /**
     * @return TransferableTypeInterface<TCondition, TSorting, TEntity>
     */
    public function getType(): TransferableTypeInterface;
}
