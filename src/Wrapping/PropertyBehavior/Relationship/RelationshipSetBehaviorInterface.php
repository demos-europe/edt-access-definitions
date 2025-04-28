<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\RelationshipInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\PropertyUpdatabilityInterface;

/**
 * Provides updatability information and behavior for a to-many relationship property.
 *
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends PropertyUpdatabilityInterface<TCondition, TEntity>
 * @template-extends RelationshipInterface<TransferableTypeInterface<TCondition, TSorting, TRelationship>>
 */
interface RelationshipSetBehaviorInterface extends PropertyUpdatabilityInterface, RelationshipInterface
{
}
