<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\Querying\Contracts\ConditionFactoryInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use Tests\data\AdModel\Birth;

class BirthType implements TypeInterface
{
    /**
     * @var ConditionFactoryInterface
     */
    private $conditionFactory;

    public function __construct(ConditionFactoryInterface $conditionFactory)
    {
        $this->conditionFactory = $conditionFactory;
    }


    public function getEntityClass(): string
    {
        return Birth::class;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function isReferencable(): bool
    {
        return true;
    }

    public function isDirectlyAccessible(): bool
    {
        return true;
    }

    public function getAccessConditions(): array
    {
        return [];
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
