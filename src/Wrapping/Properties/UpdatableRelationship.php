<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

/**
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 */
class UpdatableRelationship
{
    /**
     * @var list<TCondition>
     */
    private array $valueConditions;

    /**
     * @param list<TCondition> $valueConditions
     */
    public function __construct(array $valueConditions)
    {
        $this->valueConditions = $valueConditions;
    }

    /**
     * The conditions that will be used to determine if the corresponding property
     * can be updated with the value to set into the property.
     *
     * I.e. the conditions will be evaluated against the value to set, not the entity to update.
     *
     * @return list<TCondition>
     */
    public function getRelationshipConditions(): array
    {
        return $this->valueConditions;
    }
}
