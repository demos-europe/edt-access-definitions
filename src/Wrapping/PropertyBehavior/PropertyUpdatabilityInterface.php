<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;
use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\Wrapping\EntityDataInterface;

/**
 * @template TEntity of object
 *
 * @template-extends PropertySetBehaviorInterface<TEntity>
 *
 * TODO: try to merge this interface with its parent, make sure `getEntityConditions` is used where necessary
 */
interface PropertyUpdatabilityInterface extends PropertySetBehaviorInterface
{
    /**
     * The entity to access a property of must match these conditions to be accessible by this instance.
     *
     * The conditions are allowed to access any properties of the entity without restrictions.
     *
     * @return list<DrupalFilterInterface>
     */
    public function getEntityConditions(EntityDataInterface $entityData): array;
}
