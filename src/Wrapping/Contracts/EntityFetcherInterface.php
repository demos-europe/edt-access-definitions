<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\Pagination\PagePagination;
use Exception;
use Pagerfanta\Pagerfanta;

/**
 * Allows to fetch entities and handle given ones regarding matching conditions and sort methods.
 *
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 */
interface EntityFetcherInterface
{
    /**
     * @param non-empty-string $id
     * @param list<TCondition> $conditions
     *
     * @return TEntity
     */
    public function getEntityByIdentifier(string $id, array $conditions): object;

    /**
     * @param list<non-empty-string> $identifiers
     * @param list<TCondition> $conditions
     * @param list<TSorting> $sortMethods
     *
     * @return list<TEntity>
     */
    public function getEntitiesByIdentifiers(
        array $identifiers,
        array $conditions,
        array $sortMethods
    ): array;

    /**
     * @param list<TCondition> $conditions
     * @param list<TSorting> $sortMethods
     *
     * @return list<TEntity>
     */
    public function getEntities(array $conditions, array $sortMethods): array;

    /**
     * @param list<TCondition> $conditions
     * @param list<TSorting> $sortMethods
     *
     * @return Pagerfanta<TEntity>
     */
    public function getEntitiesForPage(array $conditions, array $sortMethods, PagePagination $pagination): Pagerfanta;

    /**
     * @param list<TEntity> $entities
     * @param list<TCondition> $conditions
     * @param list<TSorting> $sortMethods
     *
     * @return list<TEntity>
     */
    public function reindexEntities(array $entities, array $conditions, array $sortMethods): array;

    /**
     * @param list<TEntity> $entities
     *
     * @param list<TCondition> $conditions
     *
     * @throws Exception
     */
    public function assertMatchingEntities(array $entities, array $conditions): void;

    /**
     * @param TEntity $entity
     * @param list<TCondition> $conditions
     */
    public function isMatchingEntity(object $entity, array $conditions): bool;

    /**
     * @param TEntity $entity
     *
     * @param list<TCondition> $conditions
     *
     * @throws Exception
     */
    public function assertMatchingEntity(object $entity, array $conditions): void;
}
