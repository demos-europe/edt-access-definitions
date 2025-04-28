<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends RelationshipFetchableTypeInterface<TCondition, TSorting, TEntity>
 * @template-extends EntityBasedInterface<TEntity>
 * @template-extends PropertyUpdatableTypeInterface<TCondition, TSorting, TEntity>
 * @template-extends PropertyReadableTypeInterface<TCondition, TSorting, TEntity>
 * @template-extends ReindexableTypeInterface<TCondition, TSorting, TEntity>
 * @template-extends UpdatableInterface<TCondition, TEntity>
 */
interface TransferableTypeInterface extends
    NamedTypeInterface,
    RelationshipFetchableTypeInterface,
    EntityBasedInterface,
    PropertyUpdatableTypeInterface,
    PropertyReadableTypeInterface,
    UpdatableInterface,
    ReindexableTypeInterface
{
}
