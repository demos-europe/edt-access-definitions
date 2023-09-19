<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;
use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends PropertySetabilityInterface<TEntity>
 */
interface PropertyUpdatabilityInterface extends PropertySetabilityInterface
{
    /**
     * The entity to access a property of must match these conditions to be accessible by this instance.
     *
     * The conditions are allowed to access any properties of the entity without restrictions.
     *
     * @return list<TCondition>
     */
    public function getEntityConditions(): array;
}
