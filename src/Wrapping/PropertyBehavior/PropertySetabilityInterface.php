<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\EntityDataInterface;

/**
 * Provides general accessibility information for a specific property.
 *
 * @template TEntity of object
 */
interface PropertySetabilityInterface extends PropertyConstrainingInterface
{
    /**
     * @param TEntity $entity must match all conditions in {@link self::getEntityConditions()}
     *
     * @return bool `true` if the update had side effects, i.e. it changed properties other than
     *               the one defined in the given entity data; `false` otherwise
     */
    public function updateProperty(object $entity, EntityDataInterface $entityData): bool;
}
