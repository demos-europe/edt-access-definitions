<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship;

use EDT\Wrapping\Contracts\Types\NamedTypeInterface;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\FixedConstructorBehavior;

class RequiredRelationshipConstructorBehavior extends FixedConstructorBehavior
{
    /**
     * @param non-empty-string $argumentName
     * @param callable(CreationDataInterface): array{mixed, list<non-empty-string>} $callback
     */
    public function __construct(
        string $argumentName,
        callable $callback,
        protected readonly NamedTypeInterface $relationshipType,
        protected readonly bool $toOne
    ) {
        parent::__construct($argumentName, $callback);
    }

    public function getRequiredToOneRelationships(): array
    {
        return $this->toOne ? [$this->argumentName => $this->relationshipType->getTypeName()] : [];
    }

    public function getRequiredToManyRelationships(): array
    {
        return !$this->toOne ? [$this->argumentName => $this->relationshipType->getTypeName()] : [];
    }
}
