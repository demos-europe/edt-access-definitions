<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship;

use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 */
interface RelationshipSetBehaviorFactoryInterface
{
    /**
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship>|TransferableTypeProviderInterface<TCondition, TSorting, TRelationship> $relationshipType
     *
     * @return RelationshipSetBehaviorInterface<TCondition, TSorting, TEntity, TRelationship>
     */
    public function __invoke(string $name, array $propertyPath, string $entityClass, TransferableTypeInterface|TransferableTypeProviderInterface $relationshipType): RelationshipSetBehaviorInterface;

    /**
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     * @param ResourceTypeInterface<TCondition, TSorting, TRelationship> $relationshipType
     *
     * @return RelationshipSetBehaviorInterface<TCondition, TSorting, TEntity, TRelationship>
     *
     * @deprecated call instance directly as callable instead (i.e. indirectly using {@link __invoke})
     */
    public function createRelationshipSetBehavior(string $name, array $propertyPath, string $entityClass, ResourceTypeInterface $relationshipType): RelationshipSetBehaviorInterface;
}
