<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Wrapping\Contracts\TypeProviderInterface;

/**
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends TypeInterface<TCondition, TSorting, TEntity>
 */
interface ReadableTypeInterface extends TypeInterface
{
    /**
     * All properties of this type that are currently readable. May depend on authorizations of the accessing user.
     *
     * A restricted view on the properties of the {@link TypeInterface::getEntityClass() backing object}. Potentially
     * mapped via {@link TypeInterface::getAliases() aliases}.
     *
     * @return array<non-empty-string, non-empty-string|null> The keys in the returned array are the names of the
     *                                   properties. Each value is the identifier of the target
     *                                   {@link TypeInterface} (by which it can be requested from your
     *                                   {@link TypeProviderInterface}), or `null` if the
     *                                   property is a non-relationship.
     */
    public function getReadableProperties(): array;
}
