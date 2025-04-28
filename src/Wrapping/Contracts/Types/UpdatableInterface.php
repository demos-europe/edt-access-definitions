<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\JsonApi\RequestHandling\ExpectedPropertyCollectionInterface;
use EDT\JsonApi\RequestHandling\ModifiedEntity;
use EDT\Wrapping\EntityDataInterface;

/**
 * @template TEntity of object
 */
interface UpdatableInterface
{
    public function getExpectedUpdateProperties(): ExpectedPropertyCollectionInterface;

    /**
     * @param non-empty-string $entityId
     */
    public function updateEntity(string $entityId, EntityDataInterface $entityData): ModifiedEntity;
}
