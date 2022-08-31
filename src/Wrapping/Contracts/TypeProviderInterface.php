<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * Returns {@link TypeInterface} instances for given Type identifiers.
 */
interface TypeProviderInterface
{
    /**
     * @template I of TypeInterface<object>
     * @param string $typeIdentifier The identifier of your type, used when referencing other types.
     * @param class-string<I> ...$implementations
     *
     * @return I
     * @throws TypeRetrievalAccessException
     */
    public function getType(string $typeIdentifier, string ...$implementations): TypeInterface;

    /**
     * @template I of TypeInterface<object>
     * @param string $typeIdentifier The identifier of your type, used when referencing other types.
     * @param class-string<I> ...$implementations The fully qualified namespaces that the type must implement.
     *
     * @return I
     * @throws TypeRetrievalAccessException Thrown if a type with the given identifier and implementations is not available.
     */
    public function getAvailableType(string $typeIdentifier, string ...$implementations): TypeInterface;
}
