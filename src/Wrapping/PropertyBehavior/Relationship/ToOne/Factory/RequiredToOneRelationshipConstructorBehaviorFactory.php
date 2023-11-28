<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne\Factory;

use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipConstructorBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RequiredRelationshipConstructorBehavior;

/**
 * @template-implements RelationshipConstructorBehaviorFactoryInterface<PathsBasedInterface>
 */
class RequiredToOneRelationshipConstructorBehaviorFactory implements RelationshipConstructorBehaviorFactoryInterface
{
    /**
     * @param callable(CreationDataInterface): array{mixed, list<non-empty-string>} $callback
     */
    public function __construct(
        protected readonly mixed $callback
    ){}

    public function createRelationshipConstructorBehavior(
        string $name,
        array $propertyPath,
        string $entityClass,
        ResourceTypeInterface $relationshipType,
    ): ConstructorBehaviorInterface {
        return new RequiredRelationshipConstructorBehavior($name, $this->callback, $relationshipType, true);
    }
}
