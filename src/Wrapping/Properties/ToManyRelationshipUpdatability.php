<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractRelationshipUpdatability<TCondition, TransferableTypeInterface<TCondition, TSorting, TRelationship>>
 */
class ToManyRelationshipUpdatability extends AbstractRelationshipUpdatability
{
    /**
     * @param list<TCondition> $entityConditions
     * @param list<TCondition> $valueConditions
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship> $relationshipType
     * @param null|callable(TEntity, iterable<TRelationship>): void $customWriteFunction
     */
    public function __construct(
        array $entityConditions,
        array $valueConditions,
        TransferableTypeInterface $relationshipType,
        private $customWriteFunction
    ) {
        parent::__construct($entityConditions, $valueConditions, $relationshipType);
    }

    /**
     * @return null|callable(TEntity, iterable<TRelationship>): void
     */
    public function getCustomWriteFunction(): ?callable
    {
        return $this->customWriteFunction;
    }
}
