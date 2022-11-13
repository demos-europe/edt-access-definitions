<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Wrapping\Properties\UpdatableRelationship;

/**
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends TypeInterface<TCondition, TSorting, TEntity>
 */
interface TransferableTypeInterface extends TypeInterface
{
    /**
     * All properties of this type that are currently readable. May depend on authorizations of the accessing user.
     *
     * A restricted view on the properties of the {@link TypeInterface::getEntityClass() backing object}. Potentially
     * mapped via {@link AliasableTypeInterface::getAliases() aliases}.
     *
     * @return array<non-empty-string, TransferableTypeInterface<TCondition, TSorting, object>|null> The keys in the returned array are the names of the
     *                                   properties. Each value is the target
     *                                   {@link TypeInterface} or `null` if the
     *                                   property is a non-relationship.
     */
    public function getReadableProperties(): array;

    /**
     * TODO: add `UpdatableRelationship::getEntityConditions` and thus remove `$updateTarget` parameter
     *
     * @return array<non-empty-string, UpdatableRelationship<TCondition>|null>
     */
    public function getUpdatableProperties(object $updateTarget): array;
}
