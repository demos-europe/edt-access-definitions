<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 */
abstract class AbstractUpdatability
{
    /**
     * @param list<TCondition> $entityConditions
     * @param list<TCondition> $valueConditions
     */
    public function __construct(
        private readonly array $entityConditions,
        private readonly array $valueConditions
    ) {}

    /**
     * The entity to update with some value must match these conditions.
     *
     * @return list<TCondition>
     */
    public function getEntityConditions(): array
    {
        return $this->entityConditions;
    }

    /**
     * The conditions that will be used to determine if the corresponding property
     * can be updated with the value to set into the property.
     *
     * I.e. the conditions will be evaluated against the value to set, not the entity to update.
     *
     * @return list<TCondition>
     */
    public function getValueConditions(): array
    {
        return $this->valueConditions;
    }
}
