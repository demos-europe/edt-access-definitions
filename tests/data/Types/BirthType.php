<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use Tests\data\AdModel\Birth;

class BirthType implements TypeInterface
{
    public function __construct(
        private readonly PathsBasedConditionFactoryInterface $conditionFactory
    ) {}


    public function getEntityClass(): string
    {
        return Birth::class;
    }

    public function getAccessCondition(): PathsBasedInterface
    {
        return $this->conditionFactory->true();
    }

    public function getAliases(): array
    {
        return [];
    }

    public function getInternalProperties(): array
    {
        return [
            'country' => null,
        ];
    }

    public function getDefaultSortMethods(): array
    {
        return [];
    }
}
