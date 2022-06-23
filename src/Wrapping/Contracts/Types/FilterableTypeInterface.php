<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

/**
 * @template T of object
 *
 * @template-extends TypeInterface<T>
 */
interface FilterableTypeInterface extends TypeInterface
{
    /**
     * All properties of this type that can be used to filter corresponding instances.
     *
     * In most use cases this method can return the same array as
     * {@link ReadableTypeInterface::getReadableProperties()} but you may want to limit
     * the properties further, eg. if filtering over some properties is computation heavy or not supported
     * at all. You may also want to allow more properties for filtering than you allowed for reading,
     * but be careful as this may allow guessing values of non-readable properties.
     *
     * @return array<string,string|null> The keys in the returned array are the names of the
     *                                   properties. Each value is the identifier of the target
     *                                   {@link TypeInterface} (by which it can be requested from your
     *                                   {@link TypeProviderInterface}), or `null` if the
     *                                   property is a non-relationship.
     */
    public function getFilterableProperties(): array;
}
