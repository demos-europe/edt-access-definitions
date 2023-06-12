<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 */
interface RelationshipAccessibilityInterface
{
    /**
     * Usage of the relationship property corresponding to this instance shall be denied if
     * any of the relationship entities do not match any conditions in the returned list.
     *
     * The conditions are allowed to access any property of the entity without restrictions.
     *
     * @return list<TCondition>
     */
    public function getRelationshipConditions(): array;
}
