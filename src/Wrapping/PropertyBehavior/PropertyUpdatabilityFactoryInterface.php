<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

/**
 * TODO: move the child implementations into the `createFactory` method of the corresponding behavior implementation if possible, to reduce the amount of boilerplate classes
 *
 * @template TEntity of object
 */
interface PropertyUpdatabilityFactoryInterface
{
    /**
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     *
     * @return PropertyUpdatabilityInterface<TEntity>
     */
    public function __invoke(string $name, array $propertyPath, string $entityClass): PropertyUpdatabilityInterface;

    /**
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     *
     * @return PropertyUpdatabilityInterface<TEntity>
     *
     * @deprecated call instance directly as callable instead (i.e. indirectly using {@link __invoke})
     */
    public function createUpdatability(string $name, array $propertyPath, string $entityClass): PropertyUpdatabilityInterface;
}
