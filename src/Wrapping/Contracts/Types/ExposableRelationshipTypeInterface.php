<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

interface ExposableRelationshipTypeInterface
{
    /**
     * Determines if this instance is usable for external accesses when used as a relationship
     * in other types.
     *
     * This affects if instances of this type can be accessed (e.g. read, if readable) through
     * a relationship referencing this type.
     *
     * If for example the types `A`, `B` and `C` have a relationship to Type `X` each and only specific
     * users should be able to know that Type `X` exists you don't need to hide the
     * relationship using {@link ReadableTypeInterface::getReadableProperties()} in A, B and C
     * but can instead use this method in type `X`.
     *
     * If this method returns false all relationships to the type will be hidden and no
     * access to them will be possible via any other type. This does not affect direct
     * accesses but relationships only.
     *
     * @deprecated will be removed to reduce usage complexity, evaluate the conditions when returning relationships in methods like {@link ReadableTypeInterface::getReadableProperties()} instead
     */
    public function isExposedAsRelationship(): bool;
}
