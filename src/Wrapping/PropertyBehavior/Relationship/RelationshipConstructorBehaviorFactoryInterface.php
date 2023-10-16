<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship;

use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;

/**
 * @template TCondition of PathsBasedInterface
 */
interface RelationshipConstructorBehaviorFactoryInterface
{
    /**
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string $entityClass
     * @param ResourceTypeInterface<TCondition, PathsBasedInterface, object> $relationshipType
     */
    public function createRelationshipConstructorBehavior(string $name, array $propertyPath, string $entityClass, ResourceTypeInterface $relationshipType): ConstructorBehaviorInterface;
}
