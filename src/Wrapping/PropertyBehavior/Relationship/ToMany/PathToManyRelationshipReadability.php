<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany;

use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\EntityVerificationTrait;

/**
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-implements ToManyRelationshipReadabilityInterface<TEntity, TRelationship>>
 */
class PathToManyRelationshipReadability implements ToManyRelationshipReadabilityInterface
{
    use EntityVerificationTrait;
    /**
     * @param class-string<TEntity> $entityClass
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param TransferableTypeInterface<TRelationship>|TransferableTypeProviderInterface<TRelationship> $relationshipType
     */
    public function __construct(
        protected readonly string                                                      $entityClass,
        protected readonly array                                                       $propertyPath,
        protected readonly bool                                                        $defaultField,
        protected readonly bool                                                        $defaultInclude,
        protected readonly TransferableTypeInterface|TransferableTypeProviderInterface $relationshipType,
        protected readonly PropertyAccessorInterface                                   $propertyAccessor
    ) {}

    public function isDefaultInclude(): bool
    {
        return $this->defaultInclude;
    }

    public function getRelationshipType(): TransferableTypeInterface
    {
        return $this->relationshipType instanceof TransferableTypeInterface
            ? $this->relationshipType
            : $this->relationshipType->getType();
    }

    public function isDefaultField(): bool
    {
        return $this->defaultField;
    }

    public function getValue(object $entity, array $conditions, array $sortMethods): array
    {
        $relationshipEntities = $this->propertyAccessor->getValuesByPropertyPath($entity, 1, $this->propertyPath);
        $relationshipClass = $this->getRelationshipType()->getEntityClass();
        $relationshipEntities = $this->assertValidToManyValue($relationshipEntities, $relationshipClass);

        return $this->getRelationshipType()->reindexEntities($relationshipEntities, $conditions, $sortMethods);
    }
}
