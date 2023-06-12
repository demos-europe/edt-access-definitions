<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\PathsBasedInterface;
use Exception;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 */
interface IdRetrievableTypeInterface
{
    /**
     * Get an instance of the entity corresponding to this type with the given identifier that matches the
     * given conditions.
     *
     * Implementations are responsible to not return instances with restricted accessibility.
     *
     * The given conditions must only access properties that are allowed for external filtering usage.
     *
     * @param non-empty-string $identifier
     * @param list<TCondition> $conditions
     *
     * @return TEntity
     *
     * @throws Exception
     */
    public function getEntityByIdentifier(string $identifier, array $conditions): object;
}
