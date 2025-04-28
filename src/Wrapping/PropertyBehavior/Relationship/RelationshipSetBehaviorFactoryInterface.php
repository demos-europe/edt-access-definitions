<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship;

use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;

/**
 * @template TEntity of object
 * @template TRelationship of object
 */
interface RelationshipSetBehaviorFactoryInterface
{
    /**
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     * @param TransferableTypeInterface<TRelationship>|TransferableTypeProviderInterface<TRelationship> $relationshipType
     *
     * @return RelationshipSetBehaviorInterface<TEntity, TRelationship>
     */
    public function __invoke(string $name, array $propertyPath, string $entityClass, TransferableTypeInterface|TransferableTypeProviderInterface $relationshipType): RelationshipSetBehaviorInterface;

    /**
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     * @param ResourceTypeInterface<TRelationship> $relationshipType
     *
     * @return RelationshipSetBehaviorInterface<TEntity, TRelationship>
     *
     * @deprecated call instance directly as callable instead (i.e. indirectly using {@link __invoke})
     */
    public function createRelationshipSetBehavior(string $name, array $propertyPath, string $entityClass, ResourceTypeInterface $relationshipType): RelationshipSetBehaviorInterface;
}
