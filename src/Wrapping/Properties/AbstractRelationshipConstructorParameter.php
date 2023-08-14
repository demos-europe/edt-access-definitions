<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\NamedTypeInterface;
use EDT\Wrapping\Contracts\Types\RelationshipFetchableTypeInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-implements ConstructorParameterInterface<TCondition, TSorting>
 * @template-implements RelationshipAccessibilityInterface<TCondition>
 * @template-implements RelationshipInterface<NamedTypeInterface&RelationshipFetchableTypeInterface<TCondition, TSorting, object>>
 */
abstract class AbstractRelationshipConstructorParameter implements ConstructorParameterInterface, RelationshipAccessibilityInterface, RelationshipInterface
{
    /**
     * @param non-empty-string $parameterName
     * @param TransferableTypeInterface<TCondition, TSorting, object> $relationshipType
     * @param list<TCondition> $conditions
     */
    public function __construct(
        protected readonly string $parameterName,
        protected readonly TransferableTypeInterface $relationshipType,
        protected readonly array $conditions
    ) {}

    public function getRelationshipConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @return NamedTypeInterface&RelationshipFetchableTypeInterface<TCondition, TSorting, object>
     */
    public function getRelationshipType(): NamedTypeInterface&RelationshipFetchableTypeInterface
    {
        return $this->relationshipType;
    }
}
