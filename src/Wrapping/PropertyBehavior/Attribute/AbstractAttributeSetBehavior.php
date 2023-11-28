<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Attribute;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\EntityDataInterface;
use EDT\Wrapping\PropertyBehavior\PropertyUpdatabilityInterface;
use Exception;
use Webmozart\Assert\Assert;
use function array_key_exists;

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

    public function getEntityConditions(EntityDataInterface $entityData): array
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
     * @return list<non-empty-string> non-empty if the update had side effects, i.e. it changed properties other than
     *              the one this instance corresponds to; otherwise a list containing these properties
     *
     * @throws Exception
     */
    abstract protected function updateAttributeValue(object $entity, mixed $value): array;

    public function executeBehavior(object $entity, EntityDataInterface $entityData): array
    {
        $attributes = $entityData->getAttributes();
        if (array_key_exists($this->propertyName, $attributes)) {
            $attributeValue = $attributes[$this->propertyName];

            return $this->updateAttributeValue($entity, $attributeValue);
        }

        Assert::true($this->optional, "No value present for non-optional attribute `$this->propertyName`.");

        return [];
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
