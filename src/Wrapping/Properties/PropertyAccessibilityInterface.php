<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * Provides general accessibility information for a specific property.
 *
 * @template TCondition of PathsBasedInterface
 */
interface PropertyAccessibilityInterface
{
    /**
     * The entity to access a property of must match these conditions to be accessible by this instance.
     *
     * The conditions are allowed to access any properties of the entity without restrictions.
     *
     * @return list<TCondition>
     */
    public function getEntityConditions(): array;
}
