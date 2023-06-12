<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\PathsBasedInterface;
use Exception;

/**
 * @template TCondition of PathsBasedInterface
 * @template TEntity of object
 */
interface FilterableTypeInterface
{
    /**
     * Removes items not matching the given conditions from the given list and sort the remaining
     * items by the given sort methods and by an internal default sorting.
     *
     * Implementations are also responsible to not return instances with restricted accessibility.
     *
     * Conditions are allowed to access any property of the entity.
     *
     * @param list<TEntity> $entities
     * @param list<TCondition> $conditions
     *
     * @throws Exception
     */
    public function assertMatchingEntities(array $entities, array $conditions): void;

    /**
     * Will throw an exception if the given entity does not match any given conditions or does
     * not correspond to this instance.
     *
     * Conditions are allowed to access any property of the entity.
     *
     * @param TEntity $entity
     * @param list<TCondition> $conditions
     *
     * @throws Exception
     */
    public function assertMatchingEntity(object $entity, array $conditions): void;

    /**
     * Will return `true` if the given entity matches all given conditions and does correspond to
     * this instance. Will return `false` otherwise.
     *
     * Conditions are allowed to access any property of the entity.
     *
     * @param TEntity $entity
     * @param list<TCondition> $conditions
     */
    public function isMatchingEntity(object $entity, array $conditions): bool;
}
