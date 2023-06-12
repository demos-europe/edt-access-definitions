<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\NamedTypeInterface;
use EDT\Wrapping\Contracts\Types\RelationshipFetchableTypeInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use InvalidArgumentException;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-implements ConstructorParameterInterface<TCondition, TSorting>
 */
class ConstructorParameter implements ConstructorParameterInterface
{
    /**
     * @param array{toMany: bool, relationshipType: TransferableTypeInterface<TCondition, TSorting, object>, conditions: list<TCondition>}|null $relationship
     */
    public function __construct(
        protected readonly ?array $relationship
    ) {}

    public function isAttribute(): bool
    {
        return null === $this->relationship;
    }

    public function isToManyRelationship(): bool
    {
        return $this->relationship['toMany'] ?? false;
    }

    public function isToOneRelationship(): bool
    {
        return !$this->isAttribute() && !$this->isToManyRelationship();
    }

    public function getRelationshipConditions(): array
    {
        return $this->relationship['conditions']
            ?? throw new InvalidArgumentException("Can't retrieve relationship conditions from attribute.");
    }

    /**
     * @return NamedTypeInterface&RelationshipFetchableTypeInterface<TCondition, TSorting, object>
     */
    public function getRelationshipType(): NamedTypeInterface&RelationshipFetchableTypeInterface
    {
        return $this->relationship['relationshipType']
            ?? throw new InvalidArgumentException("Can't retrieve relationship type from attribute.");
    }
}
