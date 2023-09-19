<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\EntityVerificationTrait;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-implements ToOneRelationshipReadabilityInterface<TCondition, TSorting, TEntity, TRelationship>
 */
class CallbackToOneRelationshipReadability implements ToOneRelationshipReadabilityInterface
{
    use EntityVerificationTrait;

    /**
     * @param callable(TEntity): (TRelationship|null) $readCallback
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship> $relationshipType
     */
    public function __construct(
        protected readonly bool $defaultField,
        protected readonly bool $defaultInclude,
        protected readonly mixed $readCallback,
        protected readonly TransferableTypeInterface $relationshipType,
    ) {}

    public function getValue(object $entity, array $conditions): ?object
    {
        $relationshipEntity = ($this->readCallback)($entity);
        $relationshipClass = $this->relationshipType->getEntityClass();
        $relationshipEntity = $this->assertValidToOneValue($relationshipEntity, $relationshipClass);

        // TODO: how to disallow a `null` relationship? can it be done with a condition?
        return null === $relationshipEntity || $this->relationshipType->isMatchingEntity($relationshipEntity, $conditions)
            ? $relationshipEntity
            : null;
    }

    public function isDefaultField(): bool
    {
        return $this->defaultField;
    }

    public function getRelationshipType(): TransferableTypeInterface
    {
        return $this->relationshipType;
    }

    public function isDefaultInclude(): bool
    {
        return $this->defaultInclude;
    }
}
