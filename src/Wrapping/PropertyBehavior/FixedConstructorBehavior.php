<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\Wrapping\CreationDataInterface;

class FixedConstructorBehavior implements ConstructorBehaviorInterface
{
    /**
     * @param non-empty-string $argumentName
     * @param callable(CreationDataInterface): array{mixed, list<non-empty-string>} $callback
     */
    public function __construct(
        protected readonly string $argumentName,
        protected readonly mixed $callback
    ){}

    public function getArguments(CreationDataInterface $entityData): array
    {
        [$argument, $deviatingProperties] = ($this->callback)($entityData);

        return [$this->argumentName => [$argument, $deviatingProperties]];
    }

    public function getRequiredAttributes(): array
    {
        return [];
    }

    public function getOptionalAttributes(): array
    {
        return [];
    }

    public function getRequiredToOneRelationships(): array
    {
        return [];
    }

    public function getOptionalToOneRelationships(): array
    {
        return [];
    }

    public function getRequiredToManyRelationships(): array
    {
        return [];
    }

    public function getOptionalToManyRelationships(): array
    {
        return [];
    }
}
