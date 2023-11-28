<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\Wrapping\EntityDataInterface;

/**
 * Provides general accessibility information for a specific property.
 *
 * @template TEntity of object
 */
interface PropertySetBehaviorInterface extends PropertyConstrainingInterface
{
    /**
     * @param TEntity $entity must match all conditions in {@link self::getEntityConditions()}
     *
     * @return list<non-empty-string> non-empty if the execution did not change any properties in a different way than requested;
     *                                otherwise it will contain the names of the properties that were not adjusted according to the request
     */
    public function executeBehavior(object $entity, EntityDataInterface $entityData): array;

    /**
     * @return non-empty-string
     */
    public function getDescription(): string;
}
