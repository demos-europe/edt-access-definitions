<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipReadabilityInterface;
use Exception;

/**
 * Provides readability information and behavior for a to-many relationship property.
 *
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends RelationshipReadabilityInterface<TransferableTypeInterface<TCondition, TSorting, TRelationship>>
 */
interface ToManyRelationshipReadabilityInterface extends RelationshipReadabilityInterface
{
    /**
     * Returns the relationship entities of this to-many relationship property.
     *
     * Conditions and sort methods are allowed to access any property of the entity.
     *
     * @param TEntity $entity
     * @param list<TCondition> $conditions
     * @param list<TSorting> $sortMethods
     *
     * @return list<TRelationship>
     *
     * @throws Exception
     */
    public function getValue(object $entity, array $conditions, array $sortMethods): array;
}
