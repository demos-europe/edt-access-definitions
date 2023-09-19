<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

/**
 * @template TRelationshipType of object
 */
interface RelationshipInterface
{
    /**
     * Get the type definition of the relationship property represented by this instance.
     *
     * Currently, all relationships in to-many relationship properties must have the same type
     * definition. Polymorphic to-many relationships are not supported yet.
     *
     * @return TRelationshipType
     */
    public function getRelationshipType(): object;
}
