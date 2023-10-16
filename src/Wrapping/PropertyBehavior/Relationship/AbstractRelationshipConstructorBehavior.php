<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\RelationshipInterface;
use EDT\Wrapping\Contracts\Types\NamedTypeInterface;
use EDT\Wrapping\Contracts\Types\RelationshipFetchableTypeInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-implements RelationshipInterface<NamedTypeInterface&RelationshipFetchableTypeInterface<TCondition, TSorting, object>>
 */
abstract class AbstractRelationshipConstructorBehavior implements ConstructorBehaviorInterface, RelationshipInterface
{
    /**
     * @param non-empty-string $argumentName
     * @param non-empty-string $propertyName
     * @param TransferableTypeInterface<TCondition, TSorting, object> $relationshipType
     */
    public function __construct(
        protected readonly string $argumentName,
        protected readonly string $propertyName,
        protected readonly TransferableTypeInterface $relationshipType
    ) {}

    /**
     * @return NamedTypeInterface&RelationshipFetchableTypeInterface<TCondition, TSorting, object>
     */
    public function getRelationshipType(): NamedTypeInterface&RelationshipFetchableTypeInterface
    {
        return $this->relationshipType;
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
