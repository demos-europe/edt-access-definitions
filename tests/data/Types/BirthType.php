<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use Tests\data\AdModel\Birth;

class BirthType implements TypeInterface
{
    private PathsBasedConditionFactoryInterface $conditionFactory;

    public function __construct(PathsBasedConditionFactoryInterface $conditionFactory)
    {
        $this->conditionFactory = $conditionFactory;
    }


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
