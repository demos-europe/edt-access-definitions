<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany\Factory;

use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipConstructorBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToMany\ToManyRelationshipConstructorBehavior;

/**
 * @template TCondition of PathsBasedInterface
 *
 * @template-implements RelationshipConstructorBehaviorFactoryInterface<TCondition>
 */
class ToManyRelationshipConstructorBehaviorFactory implements RelationshipConstructorBehaviorFactoryInterface
{
    /**
     * @param non-empty-string|null $argumentName will fall back to the property name if `null`
     * @param list<TCondition> $relationshipConditions
     * @param null|callable(CreationDataInterface): array{list<object>, list<non-empty-string>} $fallback if `null` the property will be set to be required in the request, otherwise will be used if no value was provided in the request
     */
    public function __construct(
        protected readonly ?string $argumentName,
        protected readonly array $relationshipConditions,
        protected readonly mixed $fallback
    ) {}

    public function createRelationshipConstructorBehavior(string $name, array $propertyPath, string $entityClass, ResourceTypeInterface $relationshipType): ConstructorBehaviorInterface
    {
        return new ToManyRelationshipConstructorBehavior(
            $this->argumentName ?? $name,
            $name,
            $relationshipType,
            $this->relationshipConditions,
            $this->fallback
        );
    }
}
