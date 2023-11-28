<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use Webmozart\Assert\Assert;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractToOneRelationshipSetBehavior<TCondition, TSorting, TEntity, TRelationship>
 */
class PathToOneRelationshipSetBehavior extends AbstractToOneRelationshipSetBehavior
{
    /**
     * @param non-empty-string $propertyName
     * @param class-string<TEntity> $entityClass
     * @param list<TCondition> $entityConditions
     * @param list<TCondition> $relationshipConditions
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship> $relationshipType
     * @param non-empty-list<non-empty-string> $propertyPath
     */
    public function __construct(
        string $propertyName,
        protected readonly string $entityClass,
        array $entityConditions,
        array $relationshipConditions,
        protected readonly TransferableTypeInterface $relationshipType,
        protected readonly array $propertyPath,
        protected readonly PropertyAccessorInterface $propertyAccessor,
        bool $optional
    ) {
        parent::__construct($propertyName, $entityConditions, $relationshipConditions, $optional);
    }

    public function getRelationshipType(): TransferableTypeInterface
    {
        return $this->relationshipType;
    }

    public function updateToOneRelationship(object $entity, ?object $relationship): array
    {
        $propertyPath = $this->propertyPath;
        $propertyName = array_pop($propertyPath);
        $target = [] === $propertyPath
            ? $entity
            : $this->propertyAccessor->getValueByPropertyPath($entity, ...$propertyPath);
        Assert::object($target);
        $this->propertyAccessor->setValue($target, $relationship, $propertyName);

        return [];
    }
    public function getDescription(): string
    {
        $propertyPathString = implode('.', $this->propertyPath);
        $relationshipType = $this->relationshipType->getTypeName();

        return
            ($this->optional
                ? "Allows a to-one relationship `$this->propertyName` of type `$relationshipType` to be present in the request body, but does not require it. "
                : "Requires a to-one relationship `$this->propertyName` of type `$relationshipType` to be present in the request body.")
            . "The relationship will be stored in $this->entityClass::$propertyPathString. "
            . ([] === $this->entityConditions
                ? 'The entity does not need to '
                : 'The entity must ')
            . 'match additional conditions beside the ones defined by its type. '
            . ([] === $this->relationshipConditions
                ? 'The relationship do not need to '
                : 'The relationship must ')
            . 'match additional conditions beside the ones defined by its type.';
    }
}
