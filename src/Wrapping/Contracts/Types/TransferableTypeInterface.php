<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\EntityBasedInterface;

/**
 * @template TEntity of object
 *
 * @template-extends RelationshipFetchableTypeInterface<TEntity>
 * @template-extends EntityBasedInterface<TEntity>
 * @template-extends PropertyUpdatableTypeInterface<TEntity>
 * @template-extends PropertyReadableTypeInterface<TEntity>
 * @template-extends ReindexableTypeInterface<TEntity>
 * @template-extends UpdatableInterface<TEntity>
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
