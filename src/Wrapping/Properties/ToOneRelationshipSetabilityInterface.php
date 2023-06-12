<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use Exception;

/**
 * Provides updatability information and behavior for a to-many relationship property.
 *
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends PropertyAccessibilityInterface<TCondition>
 * @template-extends RelationshipAccessibilityInterface<TCondition>
 * @template-extends RelationshipInterface<TransferableTypeInterface<TCondition, TSorting, TRelationship>>
 */
interface ToOneRelationshipSetabilityInterface extends PropertyAccessibilityInterface, RelationshipAccessibilityInterface, RelationshipInterface
{
    /**
     * Update the relationship property this instance corresponds to by replacing the value in the
     * given entity with the given relationship entity.
     *
     * The implementation must be able to handle the given value (i.e. transform it into a valid
     * format to be stored in the attribute) or throw an exception.
     *
     * @param TEntity $entity
     * @param TRelationship|null $relationship
     *
     * @return bool `true` if the update had side effects, i.e. it changed properties other than
     *              the one this instance corresponds to; `false` otherwise
     *
     * @throws Exception
     */
    public function updateToOneRelationship(object $entity, ?object $relationship): bool;
}
