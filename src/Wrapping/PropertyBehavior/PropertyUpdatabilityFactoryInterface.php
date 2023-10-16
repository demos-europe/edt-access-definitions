<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 */
interface PropertyUpdatabilityFactoryInterface
{
    /**
     * @template TEntity of object
     *
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     *
     * @return PropertyUpdatabilityInterface<TCondition, TEntity>
     */
    public function createUpdatability(string $name, array $propertyPath, string $entityClass): PropertyUpdatabilityInterface;
}
