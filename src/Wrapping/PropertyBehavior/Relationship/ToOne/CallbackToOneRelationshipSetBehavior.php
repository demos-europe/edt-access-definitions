<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractToOneRelationshipSetBehavior<TCondition, TSorting, TEntity, TRelationship>
 */
class CallbackToOneRelationshipSetBehavior extends AbstractToOneRelationshipSetBehavior
{
    /**
     * @param non-empty-string $propertyName
     * @param list<TCondition> $entityConditions
     * @param list<TCondition> $relationshipConditions
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship> $relationshipType
     * @param callable(TEntity, TRelationship|null): bool $setterCallback
     */
    public function __construct(
        string $propertyName,
        array $entityConditions,
        array $relationshipConditions,
        protected readonly TransferableTypeInterface $relationshipType,
        protected readonly mixed $setterCallback,
        bool $optional
    ) {
        parent::__construct($propertyName, $entityConditions, $relationshipConditions, $optional);
    }

    public function getRelationshipType(): TransferableTypeInterface
    {
        return $this->relationshipType;
    }

    public function updateToOneRelationship(object $entity, ?object $relationship): bool
    {
        return ($this->setterCallback)($entity, $relationship);
    }
}
