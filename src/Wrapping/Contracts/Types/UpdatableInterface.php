<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\JsonApi\RequestHandling\ExpectedPropertyCollection;
use EDT\JsonApi\RequestHandling\ModifiedEntity;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\EntityDataInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TEntity of object
 */
interface UpdatableInterface
{
    public function getExpectedUpdateProperties(): ExpectedPropertyCollection;

    /**
     * @param non-empty-string $entityId
     */
    public function updateEntity(string $entityId, EntityDataInterface $entityData): ModifiedEntity;
}
