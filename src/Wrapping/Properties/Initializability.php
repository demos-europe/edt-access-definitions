<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 */
class Initializability
{
    /**
     * @param list<TCondition> $conditions
     */
    public function __construct(
        private readonly array $conditions,
        private readonly bool $requiredForEntityCreation
    ) {}

    public function isRequiredForEntityCreation(): bool
    {
        return $this->requiredForEntityCreation;
    }

    /**
     * @return list<TCondition>
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }
}
