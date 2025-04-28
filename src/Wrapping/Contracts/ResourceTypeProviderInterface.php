<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;

/**
 * @template TEntity of object
 *
 * @template-extends TransferableTypeProviderInterface<TEntity>
 */
interface ResourceTypeProviderInterface extends TransferableTypeProviderInterface
{
    /**
     * @return ResourceTypeInterface<TEntity>
     */
    public function getType(): ResourceTypeInterface;
}
