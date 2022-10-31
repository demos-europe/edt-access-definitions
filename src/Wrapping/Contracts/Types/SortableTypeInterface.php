<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

/**
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends TypeInterface<TCondition, TSorting, TEntity>
 */
interface SortableTypeInterface extends TypeInterface
{
    /**
     * All properties of this type that can be used to sort corresponding instances.
     *
     * In most use cases this method can return the same array as
     * {@link ReadableTypeInterface::getReadableProperties()} but you may want to limit
     * the properties further, e.g. if sorting over some properties is computation heavy or not supported
     * at all. You may also want to allow more properties for sorting than you allowed for reading,
     * but be careful as this may allow guessing values of non-readable properties.
     *
     * @return array<non-empty-string, non-empty-string|null> The keys in the returned array are the names of the
     *                                   properties. Each value is the identifier of the target
     *                                   {@link TypeInterface} (by which it can be requested from your
     *                                   {@link TypeProviderInterface}), or `null` if the
     *                                   property is a non-relationship.
     */
    public function getSortableProperties(): array;
}
