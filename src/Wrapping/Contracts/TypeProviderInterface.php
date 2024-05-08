<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * Returns {@link EntityBasedInterface} instances for given Type identifiers.
 *
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 */
interface TypeProviderInterface
{
    /**
     * @param non-empty-string $typeIdentifier
     *
     * @return EntityBasedInterface<object>|null
     *
     * @throws TypeRetrievalAccessException
     */
    public function getTypeByIdentifier(string $typeIdentifier): ?EntityBasedInterface;

    /**
     * @return list<non-empty-string>
     */
    public function getTypeIdentifiers(): array;
}
