<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier\Factory;

use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\PropertyBehavior\Identifier\IdentifierPostConstructorBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Identifier\PathIdentifierPostConstructorBehavior;

/**
 * @template TEntity of object
 *
 * @template-implements IdentifierPostConstructorBehaviorFactoryInterface<TEntity>
 */
class PathIdentifierPostConstructorBehaviorFactory implements IdentifierPostConstructorBehaviorFactoryInterface
{
    public function __construct(
        protected readonly bool $optional,
        protected readonly PropertyAccessorInterface $propertyAccessor
    ){}

    public function createIdentifierPostConstructorBehavior(array $propertyPath, string $entityClass): IdentifierPostConstructorBehaviorInterface
    {
        return new PathIdentifierPostConstructorBehavior($entityClass, $propertyPath, $this->propertyAccessor, $this->optional);
    }
}
