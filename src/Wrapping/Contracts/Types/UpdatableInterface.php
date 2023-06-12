<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\JsonApi\RequestHandling\Body\UpdateRequestBody;
use EDT\JsonApi\RequestHandling\ExpectedPropertyCollection;
use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TEntity of object
 */
interface UpdatableInterface
{
    public function getExpectedUpdateProperties(): ExpectedPropertyCollection;

    /**
     * @return TEntity|null $entity `null` if the entity was updated exactly as defined in the request
     */
    public function updateEntity(UpdateRequestBody $requestBody): ?object;
}
