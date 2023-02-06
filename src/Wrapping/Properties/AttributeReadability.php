<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

/**
 * @template TEntity of object
 */
class AttributeReadability extends AbstractReadability
{
    /**
     * @param null|callable(TEntity): (simple_primitive|array<int|string, mixed>|null) $customValueFunction to be set if this property needs special handling when read
     */
    public function __construct(
        bool $defaultField,
        bool $allowingInconsistencies,
        private $customValueFunction
    ) {
        parent::__construct($defaultField, $allowingInconsistencies);
    }

    /**
     * @return null|callable(TEntity): (simple_primitive|array<int|string, mixed>|null)
     */
    public function getCustomValueFunction(): ?callable
    {
        return $this->customValueFunction;
    }
}
