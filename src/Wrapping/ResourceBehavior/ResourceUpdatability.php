<?php

declare(strict_types=1);

namespace EDT\Wrapping\ResourceBehavior;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\EntityDataInterface;
use EDT\Wrapping\PropertyBehavior\PropertySetabilityInterface;
use EDT\Wrapping\PropertyBehavior\PropertyUpdatabilityInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetabilityInterface;
use InvalidArgumentException;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 */
class ResourceUpdatability extends AbstractResourceModifier
{
    /**
     * @param array<non-empty-string, PropertyUpdatabilityInterface<TCondition, TEntity>> $attributes
     * @param array<non-empty-string, RelationshipSetabilityInterface<TCondition, TSorting, TEntity, object>> $toOneRelationships
     * @param array<non-empty-string, RelationshipSetabilityInterface<TCondition, TSorting, TEntity, object>> $toManyRelationships
     */
    public function __construct(
        protected readonly array $attributes,
        protected readonly array $toOneRelationships,
        protected readonly array $toManyRelationships
    ) {}

    /**
     * Get all setabilities, that correspond to the given entity data.
     *
     * @param list<non-empty-string> $propertyNames
     *
     * @return list<PropertyUpdatabilityInterface<TCondition, TEntity>>
     */
    protected function getRelevantSetabilities(array $propertyNames): array
    {
        $allowedKeys = array_flip($propertyNames);

        return array_values(array_merge(
            array_intersect_key($this->attributes, $allowedKeys),
            array_intersect_key($this->toOneRelationships, $allowedKeys),
            array_intersect_key($this->toManyRelationships, $allowedKeys)
        ));
    }

    /**
     * @return list<non-empty-string>
     */
    public function getAttributeNames(): array
    {
        return array_keys($this->attributes);
    }

    /**
     * @return list<non-empty-string>
     */
    public function getToOneRelationshipNames(): array
    {
        return array_keys($this->toOneRelationships);
    }

    /**
     * @return list<non-empty-string>
     */
    public function getToManyRelationshipNames(): array
    {
        return array_keys($this->toManyRelationships);
    }

    /**
     * @param TEntity $entity
     */
    public function updateProperties(object $entity, EntityDataInterface $entityData): bool
    {
        $setabilities = $this->getRelevantSetabilities($entityData->getPropertyNames());

        return $this->getSetabilitiesSideEffect($setabilities, $entity, $entityData);
    }

    /**
     * Merges all entity conditions of the setabilities, that correspond to the given entity data.
     *
     * Does not process any paths, as the setability entity conditions are expected to
     * be hardcoded and not supplied via request.
     *
     * @param list<non-empty-string> $propertyNames the properties for which values are to be set
     *
     * @return list<TCondition>
     */
    public function getEntityConditions(array $propertyNames): array
    {
        $entityConditions = array_map(
            static fn (PropertySetabilityInterface $accessibility): array => $accessibility->getEntityConditions(),
            $this->getRelevantSetabilities($propertyNames)
        );

        return array_merge([], ...$entityConditions);
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return PropertyUpdatabilityInterface<TCondition, TEntity>
     */
    public function getAttribute(string $propertyName): PropertyUpdatabilityInterface
    {
        return $this->attributes[$propertyName]
            ?? throw new InvalidArgumentException("To-one relationship `$propertyName` not available.");
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return RelationshipSetabilityInterface<TCondition, TSorting, TEntity, object>
     */
    public function getToOneRelationship(string $propertyName): RelationshipSetabilityInterface
    {
        return $this->toOneRelationships[$propertyName]
            ?? throw new InvalidArgumentException("To-one relationship `$propertyName` not available.");
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return RelationshipSetabilityInterface<TCondition, TSorting, TEntity, object>
     */
    public function getToManyRelationship(string $propertyName): RelationshipSetabilityInterface
    {
        return $this->toManyRelationships[$propertyName]
            ?? throw new InvalidArgumentException("To-many relationship `$propertyName` not available");
    }

    protected function getParameterConstrains(): array
    {
        return array_values(array_merge($this->attributes, $this->toOneRelationships, $this->toManyRelationships));
    }
}
