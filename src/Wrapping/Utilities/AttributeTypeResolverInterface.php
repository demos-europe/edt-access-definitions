<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use ReflectionException;

interface AttributeTypeResolverInterface
{
    /**
     * @template TEntity of object
     *
     * @param callable(TEntity): mixed $callable
     *
     * @return array{type: string} valid `cebe\OpenApi` type declaration
     *
     * @throws ReflectionException
     */
    public function resolveReturnTypeFromCallable(callable $callable): array;

    /**
     * Return a valid `cebe\OpenApi` type declaration.
     *
     * @param class-string $rootEntityClass
     * @param non-empty-list<non-empty-string> $propertyPath
     *
     * @return array{type: non-empty-string, format?: non-empty-string, description?: string}
     *
     * @throws ReflectionException
     */
    public function resolveTypeFromEntityClass(
        string $rootEntityClass,
        array $propertyPath
    ): array;
}
