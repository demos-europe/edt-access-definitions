<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany;

use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\EntityDataInterface;
use EDT\Wrapping\PropertyBehavior\IdUnrelatedTrait;
use EDT\Wrapping\PropertyBehavior\PropertyUpdatabilityInterface;
use EDT\Wrapping\PropertyBehavior\PropertyUpdaterTrait;
use InvalidArgumentException;

/**
 * @template TCondition of PathsBasedInterface
 * @template TEntity of object
 *
 * @template-implements PropertyUpdatabilityInterface<TCondition, TEntity>
 */
abstract class AbstractPropertySetBehavior implements PropertyUpdatabilityInterface
{
    use PropertyUpdaterTrait;
    use IdUnrelatedTrait;

    /**
     * @param non-empty-string $propertyName the exposed property name accepted by this instance
     * @param list<TCondition> $entityConditions
     */
    public function __construct(
        protected readonly string $propertyName,
        protected readonly array $entityConditions,
        protected readonly OptionalField $optional
    ) {}

    public function executeBehavior(object $entity, EntityDataInterface $entityData): array
    {
        if ($this->hasPropertyValue($entityData)) {
            // if the value is present, execute the behavior on the given entity
            return $this->setPropertyValue($entity, $entityData);
        }

        if ($this->optional->equals(OptionalField::NO)) {
            throw new InvalidArgumentException("No value present for non-optional property `$this->propertyName`.");
        }

        // if the value is not present and not required, do nothing
        return [];
    }

    public function getEntityConditions(EntityDataInterface $entityData): array
    {
        return $this->entityConditions;
    }

    abstract protected function hasPropertyValue(EntityDataInterface $entityData): bool;

    /**
     * @param TEntity $entity
     *
     * @return list<non-empty-string>
     */
    abstract protected function setPropertyValue(object $entity, EntityDataInterface $entityData): array;

    public function getRequiredAttributes(): array
    {
        return [];
    }

    public function getOptionalAttributes(): array
    {
        return [];
    }

    public function getRequiredToManyRelationships(): array
    {
        return [];
    }

    public function getOptionalToManyRelationships(): array
    {
        return [];
    }

    public function getRequiredToOneRelationships(): array
    {
        return [];
    }

    public function getOptionalToOneRelationships(): array
    {
        return [];
    }
}
