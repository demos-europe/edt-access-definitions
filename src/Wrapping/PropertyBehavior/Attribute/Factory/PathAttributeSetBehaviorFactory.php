<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Attribute\Factory;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\PropertyBehavior\Attribute\PathAttributeSetBehavior;
use EDT\Wrapping\PropertyBehavior\PropertyUpdatabilityFactoryInterface;
use EDT\Wrapping\PropertyBehavior\PropertyUpdatabilityInterface;

/**
 * @template TCondition of PathsBasedInterface
 *
 * @template-implements PropertyUpdatabilityFactoryInterface<TCondition>
 */
class PathAttributeSetBehaviorFactory implements PropertyUpdatabilityFactoryInterface
{
    /**
     * @param list<TCondition> $entityConditions
     */
    public function __construct(
        protected readonly PropertyAccessorInterface $propertyAccessor,
        protected readonly array $entityConditions,
        protected readonly bool $optional
    ) {}

    /**
     * @template TEntity of object
     *
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     *
     * @return PropertyUpdatabilityInterface<TCondition, TEntity>
     */
    public function createUpdatability(string $name, array $propertyPath, string $entityClass): PropertyUpdatabilityInterface
    {
        return new PathAttributeSetBehavior(
            $name,
            $entityClass,
            $this->entityConditions,
            $propertyPath,
            $this->propertyAccessor,
            $this->optional
        );
    }
}
