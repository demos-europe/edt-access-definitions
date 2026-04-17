<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany;

use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\EntityDataInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\AbstractRelationshipSetBehavior;
use Exception;
use function array_key_exists;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractRelationshipSetBehavior<TCondition, TSorting, TEntity, TRelationship>
 */
abstract class AbstractToManyRelationshipSetBehavior extends AbstractRelationshipSetBehavior
{
    /**
     * @param non-empty-string $propertyName
     * @param list<TCondition> $entityConditions
     * @param list<TCondition> $relationshipConditions
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship>|TransferableTypeProviderInterface<TCondition, TSorting, TRelationship> $relationshipType
     */
    public function __construct(
        string $propertyName,
        array $entityConditions,
        protected readonly array $relationshipConditions,
        OptionalField $optional,
        TransferableTypeInterface|TransferableTypeProviderInterface $relationshipType
    ) {
        parent::__construct($propertyName, $entityConditions, $optional, $relationshipType);
    }

    protected function hasPropertyValue(EntityDataInterface $entityData): bool
    {
        return array_key_exists($this->propertyName, $entityData->getToManyRelationships());
    }

    protected function setPropertyValue(object $entity, EntityDataInterface $entityData): array
    {
        $relationshipReferences = $entityData->getToManyRelationships()[$this->propertyName];
        $relationshipValues = $this->determineToManyRelationshipValues(
            $this->getRelationshipType(),
            $this->relationshipConditions,
            $relationshipReferences
        );

        return $this->updateToManyRelationship($entity, $relationshipValues);
    }

    /**
     * Update the relationship property this instance corresponds to by replacing the list in the
     * given entity with the given list of relationship entities.
     *
     * The implementation must be able to handle the given relationship value (i.e. transform it
     * into a valid format to be stored in the attribute) or throw an exception.
     *
     * @param TEntity $entity
     * @param list<TRelationship> $relationships
     *
     * @return list<non-empty-string> non-empty if the update had side effects, i.e. it changed properties other than
     *              the one this instance corresponds to; empty otherwise
     *
     * @throws Exception
     */
    abstract protected function updateToManyRelationship(object $entity, array $relationships): array;

    public function getRequiredToManyRelationships(): array
    {
        return $this->optional->equals(OptionalField::YES)
            ? []
            : [$this->propertyName => $this->getRelationshipType()->getTypeName()];
    }

    public function getOptionalToManyRelationships(): array
    {
        return $this->optional->equals(OptionalField::YES)
            ? [$this->propertyName => $this->getRelationshipType()->getTypeName()]
            : [];
    }
}
