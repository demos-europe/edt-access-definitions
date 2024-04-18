<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier\Factory;

use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;

interface IdentifierConstructorBehaviorFactoryInterface
{
    /**
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string $entityClass
     */
    public function __invoke(array $propertyPath, string $entityClass): ConstructorBehaviorInterface;

    /**
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string $entityClass
     *
     * @deprecated call instance directly as callable instead (i.e. indirectly using {@link __invoke})
     */
    public function createIdentifierConstructorBehavior(array $propertyPath, string $entityClass): ConstructorBehaviorInterface;
}
