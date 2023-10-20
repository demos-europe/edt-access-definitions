<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne\Factory;

use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToOne\PathToOneRelationshipSetBehavior;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-implements RelationshipSetBehaviorFactoryInterface<TCondition, TSorting, TEntity, TRelationship>
 */
class PathToOneRelationshipSetBehaviorFactory implements RelationshipSetBehaviorFactoryInterface
{
    /**
     * @param list<TCondition> $relationshipConditions
     * @param list<TCondition> $entityConditions
     */
    public function __construct(
        protected readonly array $relationshipConditions,
        protected readonly bool $optional,
        protected readonly PropertyAccessorInterface $propertyAccessor,
        protected readonly array $entityConditions
    ) {}

    public function createRelationshipSetBehavior(string $name, array $propertyPath, string $entityClass, ResourceTypeInterface $relationshipType): RelationshipSetBehaviorInterface
    {
        return new PathToOneRelationshipSetBehavior(
            $name,
            $entityClass,
            $this->entityConditions,
            $this->relationshipConditions,
            $relationshipType,
            $propertyPath,
            $this->propertyAccessor,
            $this->optional
        );
    }
}
