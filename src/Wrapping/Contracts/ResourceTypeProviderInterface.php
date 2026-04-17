<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends TransferableTypeProviderInterface<TCondition, TSorting, TEntity>
 */
interface ResourceTypeProviderInterface extends TransferableTypeProviderInterface
{
    /**
     * @return ResourceTypeInterface<TCondition, TSorting, TEntity>
     */
    public function getType(): ResourceTypeInterface;
}
