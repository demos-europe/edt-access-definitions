<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\PropertyPaths\PropertyLink;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 */
interface SortingTypeInterface
{
    /**
     * All properties of this type that can be used to sort corresponding instances.
     *
     * In most use cases this method can return the same array as
     * {@link TransferableTypeInterface::getReadableProperties()} but you may want to limit
     * the properties further, e.g. if sorting over some properties is computation heavy or not supported
     * at all. You may also want to allow more properties for sorting than you allowed for reading,
     * but be careful as this may allow guessing values of non-readable properties.
     *
     * @return array<non-empty-string, PropertyLink<SortingTypeInterface<TCondition, TSorting>>> The keys in the returned array are the names of the properties.
     */
    public function getSortingProperties(): array;
}
