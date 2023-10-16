<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipConstructorBehaviorFactoryInterface;

/**
 * @template-implements RelationshipConstructorBehaviorFactoryInterface<PathsBasedInterface>
 */
class FixedConstructorBehaviorFactory implements RelationshipConstructorBehaviorFactoryInterface, NonRelationshipConstructorBehaviorFactoryInterface
{
    /**
     * @param callable(CreationDataInterface): mixed $callback
     */
    public function __construct(
        protected readonly mixed $callback
    ){}

    public function createNonRelationshipConstructorBehavior(string $name, array $propertyPath, string $entityClass): ConstructorBehaviorInterface
    {
        return new FixedConstructorBehavior($name, $this->callback);
    }

    public function createRelationshipConstructorBehavior(string $name, array $propertyPath, string $entityClass, ResourceTypeInterface $relationshipType): ConstructorBehaviorInterface
    {
        return new FixedConstructorBehavior($name, $this->callback);
    }
}
