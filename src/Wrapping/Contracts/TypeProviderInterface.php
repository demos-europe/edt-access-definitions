<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * Returns {@link TypeInterface} instances for given Type identifiers.
 *
 * @template C of \EDT\Querying\Contracts\PathsBasedInterface
 * @template S of \EDT\Querying\Contracts\PathsBasedInterface
 */
interface TypeProviderInterface
{
    /**
     * @template I of TypeInterface
     *
     * @param non-empty-string $typeIdentifier The identifier of your type, used when referencing other types.
     * @param class-string<I>  ...$implementations
     *
     * @return TypeInterface<C, S, object>&I
     *
     * @throws TypeRetrievalAccessException
     */
    public function getType(string $typeIdentifier, string ...$implementations): TypeInterface;

    /**
     * @template I of TypeInterface
     *
     * @param non-empty-string $typeIdentifier The identifier of your type, used when referencing other types.
     * @param class-string<I> ...$implementations The fully qualified namespaces that the type must implement.
     *
     * @return TypeInterface<C, S, object>&I
     *
     * @throws TypeRetrievalAccessException Thrown if a type with the given identifier and implementations is not available.
     */
    public function getAvailableType(string $typeIdentifier, string ...$implementations): TypeInterface;
}
