<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\ConditionFactory\ConditionFactoryInterface;
use EDT\Querying\Contracts\EntityBasedInterface;
use Tests\data\AdModel\Birth;

class BirthType implements EntityBasedInterface
{
    public function __construct(
        protected readonly ConditionFactoryInterface $conditionFactory
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
