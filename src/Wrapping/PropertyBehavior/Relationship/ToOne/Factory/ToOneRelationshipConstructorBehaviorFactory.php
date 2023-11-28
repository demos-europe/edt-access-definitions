<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne\Factory;

use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipConstructorBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToOne\ToOneRelationshipConstructorBehavior;

/**
 * @template TCondition of PathsBasedInterface
 *
 * @template-implements RelationshipConstructorBehaviorFactoryInterface<TCondition>
 */
class ToOneRelationshipConstructorBehaviorFactory implements RelationshipConstructorBehaviorFactoryInterface
{
    /**
     * @param non-empty-string|null $argumentName
     * @param list<TCondition> $relationshipConditions
     * @param null|callable(CreationDataInterface): array{object|null, list<non-empty-string>} $fallback
     */
    public function __construct(
        protected readonly ?string $argumentName,
        protected readonly array $relationshipConditions,
        protected readonly mixed $fallback
    ) {}

    public function createRelationshipConstructorBehavior(string $name, array $propertyPath, string $entityClass, ResourceTypeInterface $relationshipType): ConstructorBehaviorInterface
    {
        return new ToOneRelationshipConstructorBehavior(
            $this->argumentName ?? $name,
            $name,
            $relationshipType,
            $this->relationshipConditions,
            $this->fallback
        );
    }
}
