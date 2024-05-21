<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\Querying\Pagination\PagePagination;
use EDT\Querying\SortMethodFactories\SortMethodInterface;
use Pagerfanta\Pagerfanta;

/**
 * @template TEntity of object
 */
interface FetchableTypeInterface
{
    /**
     * Get all entities of this type that match all the given conditions.
     *
     * Implementations are responsible to not return instances with restricted accessibility.
     *
     * Implementations may add default sort criteria to the given ones.
     *
     * The given conditions and sort methods must only use properties that are allowed to be used
     * by an external request.
     *
     * @param list<DrupalFilterInterface> $conditions
     * @param list<SortMethodInterface> $sortMethods
     *
     * @return list<TEntity>
     */
    public function getEntities(array $conditions, array $sortMethods): array;

    /**
     * Get all entities of this type that match all the given conditions in a paginated manner.
     *
     * Implementations are responsible to not return instances with restricted accessibility.
     *
     * Implementations may add default sort criteria to the given ones.
     *
     * The given conditions and sort methods must only use properties that are allowed to be used
     * by an external request.
     *
     * @param list<DrupalFilterInterface> $conditions
     * @param list<SortMethodInterface> $sortMethods
     *
     * @return Pagerfanta<TEntity>
     */
    public function getEntitiesForPage(array $conditions, array $sortMethods, PagePagination $pagination): Pagerfanta;
}
