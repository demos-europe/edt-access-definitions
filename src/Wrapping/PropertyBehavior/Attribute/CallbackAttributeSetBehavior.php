<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Attribute;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends AbstractAttributeSetBehavior<TCondition, TEntity>
 */
class CallbackAttributeSetBehavior extends AbstractAttributeSetBehavior
{
    use AttributeTrait;

    /**
     * @param non-empty-string $propertyName
     * @param list<TCondition> $entityConditions
     * @param callable(TEntity, simple_primitive|array<int|string, mixed>|null): bool $setterCallback
     */
    public function __construct(
        string $propertyName,
        array $entityConditions,
        protected readonly mixed $setterCallback,
        bool $optional
    ) {
        parent::__construct($propertyName, $entityConditions, $optional);
    }

    protected function updateAttributeValue(object $entity, mixed $value): bool
    {
        $value = $this->assertValidValue($value);
        return ($this->setterCallback)($entity, $value);
    }

    public function getDescription(): string
    {
        return ($this->optional
                ? "Allows an attribute `$this->propertyName` to be present in the request body, but does not require it. "
                : "Requires an attribute `$this->propertyName` to be present in the request body.")
            . 'If the property is present in the request body it will be passed to a callback, which is able to adjust the target entity or execute side effects.'
            . ([] === $this->entityConditions
                ? 'The target entity does not need to '
                : 'The target entity must ')
            . 'match additional conditions beside the ones defined by its type.';
    }
}
