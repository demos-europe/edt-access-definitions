<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Attribute;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Wrapping\PropertyBehavior\Attribute\Factory\CallbackAttributeSetBehaviorFactory;

/**
 * @template TEntity of object
 *
 * @template-extends AbstractAttributeSetBehavior<TEntity>
 */
class CallbackAttributeSetBehavior extends AbstractAttributeSetBehavior
{
    use AttributeTrait;

    /**
     * @param non-empty-string $propertyName the exposed resource property name
     * @param list<DrupalFilterInterface> $entityConditions
     * @param callable(TEntity, simple_primitive|array<int|string, mixed>|null): list<non-empty-string> $setterCallback
     */
    public function __construct(
        string $propertyName,
        array $entityConditions,
        protected readonly mixed $setterCallback,
        OptionalField $optional
    ) {
        parent::__construct($propertyName, $entityConditions, $optional);
    }

    /**
     * @template TEnt of object
     *
     * @param list<DrupalFilterInterface> $entityConditions
     * @param callable(TEnt, simple_primitive|array<int|string, mixed>|null): list<non-empty-string> $updateCallback
     *
     * @return CallbackAttributeSetBehaviorFactory<TEnt>
     */
    public static function createFactory(array $entityConditions, mixed $updateCallback, OptionalField $optional): CallbackAttributeSetBehaviorFactory
    {
        return new CallbackAttributeSetBehaviorFactory($entityConditions, $updateCallback, $optional);
    }

    protected function updateAttributeValue(object $entity, mixed $value): array
    {
        $value = $this->assertValidValue($value);
        return ($this->setterCallback)($entity, $value);
    }

    public function getDescription(): string
    {
        return ($this->optional->equals(OptionalField::YES)
                ? "Allows an attribute `$this->propertyName` to be present in the request body, but does not require it. "
                : "Requires an attribute `$this->propertyName` to be present in the request body.")
            . 'If the property is present in the request body it will be passed to a callback, which is able to adjust the target entity or execute side effects.'
            . ([] === $this->entityConditions
                ? 'The target entity does not need to '
                : 'The target entity must ')
            . 'match additional conditions beside the ones defined by its type.';
    }
}
