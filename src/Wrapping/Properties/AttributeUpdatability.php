<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends AbstractUpdatability<TCondition>
 */
abstract class AttributeUpdatability extends AbstractUpdatability
{
    /**
     * @param list<TCondition> $entityConditions
     * @param list<TCondition> $valueConditions
     * @param null|callable(TEntity, mixed): void $customWriteCallback
     */
    public function __construct(
        array $entityConditions,
        array $valueConditions,
        private readonly mixed $customWriteCallback
    ) {
        parent::__construct($entityConditions, $valueConditions);
    }

    /**
     * @return null|callable(TEntity, mixed): void
     */
    public function getCustomWriteCallback(): ?callable
    {
        return $this->customWriteCallback;
    }

    abstract public function isValidValue(mixed $attributeValue): bool;
}
