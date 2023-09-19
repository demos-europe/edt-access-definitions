<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany;

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
 * @template-extends AbstractToManyRelationshipSetability<TCondition, TSorting, TEntity, TRelationship>
 */
class PathToManyRelationshipSetability extends AbstractToManyRelationshipSetability
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

    public function updateToManyRelationship(object $entity, array $relationships): bool
    {
        $propertyPath = $this->propertyPath;
        $propertyName = array_pop($propertyPath);
        $target = [] === $propertyPath
            ? $entity
            : $this->propertyAccessor->getValueByPropertyPath($entity, ...$propertyPath);
        Assert::object($target);
        $this->propertyAccessor->setValue($target, $relationships, $propertyName);

        return false;
    }
}
