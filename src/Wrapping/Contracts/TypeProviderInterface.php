<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\TypeProviders\TypeRequirement;

/**
 * Returns {@link TypeInterface} instances for given Type identifiers.
 *
 * @template C of \EDT\Querying\Contracts\PathsBasedInterface
 * @template S of \EDT\Querying\Contracts\PathsBasedInterface
 */
interface TypeProviderInterface
{
    /**
     * @param non-empty-string $typeIdentifier
     *
     * @return TypeRequirement<TypeInterface<C, S, object>>
     */
    public function requestType(string $typeIdentifier): TypeRequirement;

    /**
     * @return list<non-empty-string>
     */
    public function getTypeIdentifiers(): array;
}
