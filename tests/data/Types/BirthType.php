<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use Tests\data\AdModel\Birth;

class BirthType implements EntityBasedInterface
{
    public function __construct(
        protected readonly PathsBasedConditionFactoryInterface $conditionFactory
    ) {}


    public function getEntityClass(): string
    {
        return Birth::class;
    }

    public function getAccessConditions(): array
    {
        return [];
    }
}
