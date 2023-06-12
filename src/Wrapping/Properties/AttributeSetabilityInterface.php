<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;
use Exception;

/**
 * Provides updatability information and behavior for an attribute (i.e. a non-relationship) property.
 *
 * @template TCondition of PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends PropertyAccessibilityInterface<TCondition>
 */
interface AttributeSetabilityInterface extends PropertyAccessibilityInterface
{
    /**
     * Update the attribute property this instance corresponds to with the given value.
     *
     * The implementation must be able to handle the given value (i.e. transform it into a valid
     * format to be stored in the attribute if necessary) or throw an exception.
     *
     * @param TEntity $entity
     *
     * @return bool `true` if the update had side effects, i.e. it changed properties other than
     *              the one this instance corresponds to; `false` otherwise
     *
     * @throws Exception
     */
    public function updateAttributeValue(object $entity, mixed $value): bool;
}
