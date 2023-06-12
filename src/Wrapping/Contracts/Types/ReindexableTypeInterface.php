<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends FilterableTypeInterface<TCondition, TEntity>
 */
interface ReindexableTypeInterface extends SortableTypeInterface, FilterableTypeInterface
{
    /**
     * Removes items from the given array that not accessible via this type or do not match the
     * given conditions. The remaining items are sorted according to the given sort methods and
     * the internal default sort method setting.
     *
     * @param list<TEntity> $entities
     * @param list<TCondition> $conditions
     * @param list<TSorting> $sortMethods
     *
     * @return list<TEntity>
     */
    public function reindexEntities(array $entities, array $conditions, array $sortMethods): array;
}
