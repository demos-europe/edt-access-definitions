<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;

/**
 * @template TEntity of object
 */
interface TransferableTypeProviderInterface
{
    /**
     * @return TransferableTypeInterface<TEntity>
     */
    public function getType(): TransferableTypeInterface;
}
