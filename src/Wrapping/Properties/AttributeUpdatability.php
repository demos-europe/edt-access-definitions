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
class AttributeUpdatability extends AbstractUpdatability
{
    /**
     * @param list<TCondition> $entityConditions
     * @param list<TCondition> $valueConditions
     * @param null|callable(TEntity, simple_primitive|array<int|string, mixed>|null): void $customWriteFunction
     */
    public function __construct(
        array $entityConditions,
        array $valueConditions,
        private $customWriteFunction
    ) {
        parent::__construct($entityConditions, $valueConditions);
    }

    /**
     * @return null|callable(TEntity, simple_primitive|array<int|string, mixed>|null): void
     */
    public function getCustomWriteFunction(): ?callable
    {
        return $this->customWriteFunction;
    }
}
