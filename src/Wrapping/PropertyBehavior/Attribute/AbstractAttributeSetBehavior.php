<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Attribute;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\EntityDataInterface;
use EDT\Wrapping\PropertyBehavior\PropertyUpdatabilityInterface;
use Exception;

/**
 * @template TCondition of PathsBasedInterface
 * @template TEntity of object
 *
 * @template-implements PropertyUpdatabilityInterface<TCondition, TEntity>
 */
abstract class AbstractAttributeSetBehavior implements PropertyUpdatabilityInterface
{
    /**
     * @param non-empty-string $propertyName the exposed property name accepted by this instance
     * @param list<TCondition> $entityConditions
     */
    public function __construct(
        protected readonly string $propertyName,
        protected readonly array $entityConditions,
        protected readonly bool $optional
    ) {}

    public function getEntityConditions(): array
    {
        return $this->entityConditions;
    }

    /**
     * Update the attribute property this instance corresponds to with the given value.
     *
     * The implementation must be able to handle the given value (i.e. transform it into a valid
     * format to be stored in the attribute if necessary) or throw an exception.
     *
     * @param TEntity $entity
     *
     * @return bool `true` if the update had side effects, i.e. it changed properties other than
     *              the one this instance corresponds to; `false` otherwise
     *
     * @throws Exception
     */
    abstract protected function updateAttributeValue(object $entity, mixed $value): bool;

    public function executeBehavior(object $entity, EntityDataInterface $entityData): bool
    {
        $attributes = $entityData->getAttributes();
        $attributeValue = $attributes[$this->propertyName];

        return $this->updateAttributeValue($entity, $attributeValue);
    }

    public function getRequiredAttributes(): array
    {
        return $this->optional ? [] : [$this->propertyName];
    }

    public function getOptionalAttributes(): array
    {
        return $this->optional ? [$this->propertyName] : [];
    }

    public function getRequiredToOneRelationships(): array
    {
        return [];
    }

    public function getOptionalToOneRelationships(): array
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
}
