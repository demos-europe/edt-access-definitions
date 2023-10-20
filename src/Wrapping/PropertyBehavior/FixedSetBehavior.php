<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\Wrapping\EntityDataInterface;

/**
 * @template TEntity of object
 *
 * @template-implements PropertySetBehaviorInterface<TEntity>
 */
class FixedSetBehavior implements PropertySetBehaviorInterface
{
    /**
     * @param callable(TEntity, EntityDataInterface): bool $callback
     */
    public function __construct(
        protected readonly mixed $callback
    ){}

    public function executeBehavior(object $entity, EntityDataInterface $entityData): bool
    {
        return ($this->callback)($entity, $entityData);
    }

    public function getRequiredAttributes(): array
    {
        return [];
    }

    public function getOptionalAttributes(): array
    {
        return [];
    }

    public function getRequiredToOneRelationships(): array
    {
        return [];
    }

    public function getOptionalToOneRelationships(): array
    {
        return [];
    }

    public function getRequiredToManyRelationships(): array
    {
        return [];
    }

    public function getOptionalToManyRelationships(): array
    {
        return [];
    }

    public function getDescription(): string
    {
        return 'Executes a callback that is provided with the request and the targeted entity. '
            . 'The behavior does not define any required properties in the request or conditions that the entity must match.';
    }
}
