<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\Querying\SortMethodFactories\SortMethodInterface;

/**
 * @template TEntity of object
 *
 * @template-extends FilterableTypeInterface<TEntity>
 */
interface ReindexableTypeInterface extends FilterableTypeInterface
{
    /**
     * Removes items from the given array that not accessible via this type or do not match the
     * given conditions. The remaining items are sorted according to the given sort methods and
     * the internal default sort method setting.
     *
     * @param list<TEntity> $entities
     * @param list<DrupalFilterInterface> $conditions
     * @param list<SortMethodInterface> $sortMethods
     *
     * @return list<TEntity>
     */
    public function reindexEntities(array $entities, array $conditions, array $sortMethods): array;
}
