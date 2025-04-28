<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne\Factory;

use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToOne\CallbackToOneRelationshipSetBehavior;

/**
 * @template TEntity of object
 * @template TRelationship of object
 * @template TCondition of PathsBasedInterface
 *
 * @template-implements RelationshipSetBehaviorFactoryInterface<TCondition, PathsBasedInterface, TEntity, TRelationship>
 */
class CallbackToOneRelationshipSetBehaviorFactory implements RelationshipSetBehaviorFactoryInterface
{
    /**
     * @param callable(TEntity, TRelationship|null): list<non-empty-string> $setBehaviorCallback
     * @param list<TCondition> $relationshipConditions
     * @param list<TCondition> $entityConditions
     */
    public function __construct(
        protected readonly mixed $setBehaviorCallback,
        protected readonly array $relationshipConditions,
        protected readonly OptionalField $optional,
        protected readonly array $entityConditions
    ) {}

    public function __invoke(string $name, array $propertyPath, string $entityClass, TransferableTypeInterface|TransferableTypeProviderInterface $relationshipType): RelationshipSetBehaviorInterface
    {
        return new CallbackToOneRelationshipSetBehavior(
            $name,
            $this->entityConditions,
            $this->relationshipConditions,
            $relationshipType,
            $this->setBehaviorCallback,
            $this->optional
        );
    }

    public function createRelationshipSetBehavior(string $name, array $propertyPath, string $entityClass, ResourceTypeInterface $relationshipType): RelationshipSetBehaviorInterface
    {
        return $this($name, $propertyPath, $entityClass, $relationshipType);
    }
}
