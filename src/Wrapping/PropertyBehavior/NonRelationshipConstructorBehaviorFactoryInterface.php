<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

interface NonRelationshipConstructorBehaviorFactoryInterface
{
    /**
     * @template TEntity of object
     *
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     */
    public function createNonRelationshipConstructorBehavior(string $name, array $propertyPath, string $entityClass): ConstructorBehaviorInterface;
}
