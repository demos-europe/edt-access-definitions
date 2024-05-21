<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\EntityVerificationTrait;

/**
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-implements ToOneRelationshipReadabilityInterface<TEntity, TRelationship>
 */
class PathToOneRelationshipReadability implements ToOneRelationshipReadabilityInterface
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
        protected readonly PropertyAccessorInterface                                   $propertyAccessor,
    ) {}

    public function getValue(object $entity, array $conditions): ?object
    {
        $relationship = $this->propertyAccessor->getValueByPropertyPath($entity, ...$this->propertyPath);
        $relationshipClass = $this->getRelationshipType()->getEntityClass();
        $relationship = $this->assertValidToOneValue($relationship, $relationshipClass);

        // TODO: how to disallow a `null` relationship? can it be done with a condition?
        return null === $relationship || $this->getRelationshipType()->isMatchingEntity($relationship, $conditions)
            ? $relationship
            : null;
    }

    public function isDefaultField(): bool
    {
        return $this->defaultField;
    }

    public function getRelationshipType(): TransferableTypeInterface
    {
        return $this->relationshipType instanceof TransferableTypeInterface
            ? $this->relationshipType
            : $this->relationshipType->getType();
    }

    public function isDefaultInclude(): bool
    {
        return $this->defaultInclude;
    }
}
