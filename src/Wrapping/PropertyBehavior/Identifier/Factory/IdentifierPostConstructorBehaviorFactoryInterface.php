<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier\Factory;

use EDT\Wrapping\PropertyBehavior\Identifier\IdentifierPostConstructorBehaviorInterface;

/**
 * @template TEntity of object
 */
interface IdentifierPostConstructorBehaviorFactoryInterface
{
    /**
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     *
     * @return IdentifierPostConstructorBehaviorInterface<TEntity>
     */
    public function createIdentifierPostConstructorBehavior(array $propertyPath, string $entityClass): IdentifierPostConstructorBehaviorInterface;
}
