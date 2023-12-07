<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\EntityVerificationTrait;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-implements ToManyRelationshipReadabilityInterface<TCondition, TSorting, TEntity, TRelationship>>
 */
class CallbackToManyRelationshipReadability implements ToManyRelationshipReadabilityInterface
{
    use EntityVerificationTrait;

    /**
     * @param callable(TEntity): iterable<TRelationship> $readCallback
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship> $relationshipType
     */
    public function __construct(
        protected readonly bool $defaultField,
        protected readonly bool $defaultInclude,
        protected readonly mixed $readCallback,
        protected readonly TransferableTypeInterface $relationshipType,
    ) {}

    public function isDefaultInclude(): bool
    {
        return $this->defaultInclude;
    }

    public function getRelationshipType(): TransferableTypeInterface
    {
        return $this->relationshipType;
    }

    public function isDefaultField(): bool
    {
        return $this->defaultField;
    }

    public function getValue(object $entity, array $conditions, array $sortMethods): array
    {
        $relationshipEntities = ($this->readCallback)($entity);
        $relationshipClass = $this->relationshipType->getEntityClass();
        $relationshipEntities = $this->assertValidToManyValue($relationshipEntities, $relationshipClass);

        return $this->relationshipType->reindexEntities($relationshipEntities, $conditions, $sortMethods);
    }
}
