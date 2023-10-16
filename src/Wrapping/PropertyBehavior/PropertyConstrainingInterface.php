<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\Wrapping\EntityDataInterface;

/**
 * Sets constraints regarding the properties that must and may be present in {@link EntityDataInterface}
 * when passed to the {@link PropertySetBehaviorInterface::executeBehavior()} or
 * {@link ConstructorBehaviorInterface::getArguments()} methods.
 *
 * Regarding the initialization of entities this information also serves to determine which properties
 * are allowed in the provided {@link EntityDataInterface} at all. If a property is not supported
 * by any instance, it must not be present in {@link EntityDataInterface}.
 *
 * Please note that both methods named above will still be invoked and may apply changes, even if
 * {@link self::getRequiredAttributes()} and {@link self::getOptionalAttributes()} both return an
 * empty array, as the {@link EntityDataInterface} instance may not be needed at all for specific
 * {@link PropertyConstrainingInterface} instances to execute its logic.
 */
interface PropertyConstrainingInterface
{
    /**
     * The attributes required by this instance.
     *
     * The returned list must contain all attribute names that are mandatory for this instance.
     *
     * @return list<non-empty-string> the names of the attributes that must be present in {@link EntityDataInterface}
     */
    public function getRequiredAttributes(): array;

    /**
     * The attributes supported beside the ones returned by {@link self::getRequiredAttributes()}.
     *
     * @return list<non-empty-string> the additional names of the attributes, that may be present in {@link EntityDataInterface}
     */
    public function getOptionalAttributes(): array;

    /**
     * The to-one relationships required by this instance.
     *
     * The returned list must contain all to-one relationship names that are mandatory for this instance.
     *
     * @return array<non-empty-string, non-empty-string> mapping from the names of the to-one relationship properties that must be present in {@link EntityDataInterface} to their relationship type name
     */
    public function getRequiredToOneRelationships(): array;

    /**
     * The to-one relationships supported beside the ones returned by {@link self::getRequiredToOneRelationships()}.
     *
     * @return array<non-empty-string, non-empty-string> the mapping of additionally supported to-one relationship property names to the name of their types that may be present in {@link EntityDataInterface}
     */
    public function getOptionalToOneRelationships(): array;

    /**
     * The to-many relationships required by this instance.
     *
     * The returned list must contain all to-many relationship names that are mandatory for this instance.
     *
     * @return array<non-empty-string, non-empty-string> mapping from the names of the to-many relationship properties that must be present in {@link EntityDataInterface} to their relationship type name
     */
    public function getRequiredToManyRelationships(): array;

    /**
     * The to-many relationships supported beside the ones returned by {@link self::getRequiredToManyRelationships()}.
     *
     * @return array<non-empty-string, non-empty-string> the mapping of additionally supported to-many relationship property names to the name of their types that may be present in {@link EntityDataInterface}
     */
    public function getOptionalToManyRelationships(): array;
}
