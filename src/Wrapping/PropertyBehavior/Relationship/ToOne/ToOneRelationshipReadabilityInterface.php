<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipReadabilityInterface;
use Exception;

/**
 * Provides readability information and behavior for a to-one relationship property.
 *
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends RelationshipReadabilityInterface<TransferableTypeInterface<TCondition, TSorting, TRelationship>>
 */
interface ToOneRelationshipReadabilityInterface extends RelationshipReadabilityInterface
{
    /**
     * Returns the relationship entity of this to-one relationship property.
     *
     * Shall return `null` if the relationship entity is set to `null`.
     *
     * Shall return `null` in case of relationship entities for which readability was denied or
     * that do not match all given conditions.
     *
     * @param TEntity $entity
     * @param list<TCondition> $conditions
     *
     * @return TRelationship|null
     *
     * @throws Exception
     */
    public function getValue(object $entity, array $conditions): ?object;
}
