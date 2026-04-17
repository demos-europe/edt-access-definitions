<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany\Factory;

use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToMany\PathToManyRelationshipSetBehavior;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-implements RelationshipSetBehaviorFactoryInterface<TCondition, TSorting, TEntity, TRelationship>
 */
class PathToManyRelationshipSetBehaviorFactory implements RelationshipSetBehaviorFactoryInterface
{
    /**
     * @param list<TCondition> $relationshipConditions
     * @param list<TCondition> $entityConditions
     */
    public function __construct(
        protected readonly array $relationshipConditions,
        protected readonly OptionalField $optional,
        protected readonly PropertyAccessorInterface $propertyAccessor,
        protected readonly array $entityConditions
    ) {}

    public function __invoke(string $name, array $propertyPath, string $entityClass, TransferableTypeInterface|TransferableTypeProviderInterface $relationshipType): RelationshipSetBehaviorInterface
    {
        return new PathToManyRelationshipSetBehavior(
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

    public function createRelationshipSetBehavior(string $name, array $propertyPath, string $entityClass, ResourceTypeInterface $relationshipType): RelationshipSetBehaviorInterface
    {
        return $this($name, $propertyPath, $entityClass, $relationshipType);
    }
}
