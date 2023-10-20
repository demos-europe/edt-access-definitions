<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier\Factory;

use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;

/**
 * @template TEntity of object
 */
interface IdentifierConstructorBehaviorFactoryInterface
{
    /**
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     */
    public function createIdentifierConstructorBehavior(array $propertyPath, string $entityClass): ConstructorBehaviorInterface;
}
