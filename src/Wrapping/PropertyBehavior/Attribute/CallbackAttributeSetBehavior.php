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
}
