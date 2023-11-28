<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\EntityDataInterface;
use EDT\Wrapping\PropertyBehavior\PropertyUpdaterTrait;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorInterface;
use Exception;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-implements RelationshipSetBehaviorInterface<TCondition, TSorting, TEntity, TRelationship>
 */
abstract class AbstractToOneRelationshipSetBehavior implements RelationshipSetBehaviorInterface
{
    use PropertyUpdaterTrait;

    /**
     * @param non-empty-string $propertyName
     * @param list<TCondition> $entityConditions
     * @param list<TCondition> $relationshipConditions
     */
    public function __construct(
        protected readonly string $propertyName,
        protected readonly array $entityConditions,
        protected readonly array $relationshipConditions,
        protected readonly bool $optional
    ) {}

    public function getEntityConditions(EntityDataInterface $entityData): array
    {
        return $this->entityConditions;
    }

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
     * @return list<non-empty-string> non-empty if the update had side effects, i.e. it changed properties other than
     *               the one this instance corresponds to; empty otherwise
     *
     * @throws Exception
     */
    abstract protected function updateToOneRelationship(object $entity, ?object $relationship): array;

    public function executeBehavior(object $entity, EntityDataInterface $entityData): array
    {
        $requestRelationships = $entityData->getToOneRelationships();
        $relationshipReference = $requestRelationships[$this->propertyName];
        $relationshipValue = $this->determineToOneRelationshipValue(
            $this->getRelationshipType(),
            $this->relationshipConditions,
            $relationshipReference
        );

        return $this->updateToOneRelationship($entity, $relationshipValue);
    }

    public function getRequiredAttributes(): array
    {
        return [];
    }

    public function getOptionalAttributes(): array
    {
        return [];
    }

    public function getRequiredToOneRelationships(): array
    {
        return $this->optional
            ? []
            : [$this->propertyName => $this->getRelationshipType()->getTypeName()];
    }

    public function getOptionalToOneRelationships(): array
    {
        return $this->optional
            ? [$this->propertyName => $this->getRelationshipType()->getTypeName()]
            : [];
    }

    public function getRequiredToManyRelationships(): array
    {
        return [];
    }

    public function getOptionalToManyRelationships(): array
    {
        return [];
    }
}
