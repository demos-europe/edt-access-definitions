<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Attribute;

use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\EntityDataInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToMany\AbstractPropertySetBehavior;
use Exception;
use function array_key_exists;

/**
 * @template TCondition of PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends  AbstractPropertySetBehavior<TCondition, TEntity>
 */
abstract class AbstractAttributeSetBehavior extends AbstractPropertySetBehavior
{
    protected function hasPropertyValue(EntityDataInterface $entityData): bool
    {
        return array_key_exists($this->propertyName, $entityData->getAttributes());
    }

    protected function setPropertyValue(object $entity, EntityDataInterface $entityData): array
    {
        return $this->updateAttributeValue($entity, $entityData->getAttributes()[$this->propertyName]);
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

    public function getRequiredAttributes(): array
    {
        return $this->optional->equals(OptionalField::YES) ? [] : [$this->propertyName];
    }

    public function getOptionalAttributes(): array
    {
        return $this->optional->equals(OptionalField::YES) ? [$this->propertyName] : [];
    }
}
