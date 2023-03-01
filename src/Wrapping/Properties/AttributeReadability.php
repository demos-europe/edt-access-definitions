<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

/**
 * @template TEntity of object
 */
abstract class AttributeReadability extends AbstractReadability
{
    /**
     * @param null|callable(TEntity): mixed $customReadCallback to be set if this property needs special handling when read
     */
    public function __construct(
        bool $defaultField,
        bool $allowingInconsistencies,
        private readonly mixed $customReadCallback
    ) {
        parent::__construct($defaultField, $allowingInconsistencies);
    }

    /**
     * @return null|callable(TEntity): mixed
     */
    public function getCustomReadCallback(): ?callable
    {
        return $this->customReadCallback;
    }

    abstract public function isValidValue(mixed $attributeValue): bool;
}
