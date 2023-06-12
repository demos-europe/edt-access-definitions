<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 */
interface RelationshipFetchableTypeInterface
{
    /**
     * Get all entities with the given IDs that *also* match the given conditions.
     *
     * Implementations are responsible to not return instances with restricted accessibility.
     *
     * The given conditions and sort methods will be given full access to the properties of the
     * entity. I.e. they are not limited to the properties returned by (a potentially implemented)
     * {@link FilteringTypeInterface::getFilteringProperties()} nor are any of these properties
     * supported if they are not present in the entity.
     *
     * @param non-empty-list<non-empty-string> $identifiers
     * @param list<TCondition> $conditions
     * @param list<TSorting> $sortMethods
     *
     * @return list<TEntity>
     */
    public function getEntitiesForRelationship(array $identifiers, array $conditions, array $sortMethods): array;

    /**
     * Get the entities with the given ID that *also* match the given conditions.
     *
     * Implementations are responsible to not return instances with restricted accessibility.
     *
     * @param non-empty-string $identifier
     * @param list<TCondition> $conditions will be given full access to the properties of the entity
     *
     * @return TEntity
     */
    public function getEntityForRelationship(string $identifier, array $conditions): object;
}
