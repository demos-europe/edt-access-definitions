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
     * @param TEntity $entity
     *
     * @return list<non-empty-string> non-empty if the execution did not change any properties in a different way than requested;
     *                                otherwise it will contain the names of the properties that were not adjusted according to the request
     */
    public function executeBehavior(object $entity, EntityDataInterface $entityData): array;

    /**
     * TODO: move this method to a place where implementations of {@link ConstructorBehaviorInterface} can use it
     * TODO: use this method for the OpenAPI schema generation
     *
     * @return non-empty-string
     */
    public function getDescription(): string;
}
