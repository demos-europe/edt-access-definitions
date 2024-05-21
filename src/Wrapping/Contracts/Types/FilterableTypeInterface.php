<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\ConditionFactory\DrupalFilterInterface;
use Exception;

/**
 * @template TEntity of object
 */
interface FilterableTypeInterface
{
    /**
     * Will throw an exception if the given entity does not match any given conditions or does
     * not correspond to this instance.
     *
     * Conditions are allowed to access any property of the entity.
     *
     * If no conditions are given access may still be restricted by conditions internally
     * imposed by the implementation.
     *
     * @param TEntity $entity
     * @param list<DrupalFilterInterface> $conditions
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
     * If no conditions are given access may still be restricted by conditions internally
     * imposed by the implementation.
     *
     * @param TEntity $entity
     * @param list<DrupalFilterInterface> $conditions
     */
    public function isMatchingEntity(object $entity, array $conditions): bool;
}
