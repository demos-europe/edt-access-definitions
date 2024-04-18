<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

interface ConstructorBehaviorFactoryInterface
{
    /**
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string $entityClass
     */
    public function __invoke(string $name, array $propertyPath, string $entityClass): ConstructorBehaviorInterface;

    /**
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string $entityClass
     *
     * @deprecated call instance directly as callable instead (i.e. indirectly using {@link __invoke})
     */
    public function createConstructorBehavior(string $name, array $propertyPath, string $entityClass): ConstructorBehaviorInterface;
}
