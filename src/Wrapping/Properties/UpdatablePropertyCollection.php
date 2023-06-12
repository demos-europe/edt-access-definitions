<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 */
class UpdatablePropertyCollection
{
    /**
     * @param array<non-empty-string, AttributeSetabilityInterface<TCondition, TEntity>> $attributes
     * @param array<non-empty-string, ToOneRelationshipSetabilityInterface<TCondition, TSorting, TEntity, object>> $toOneRelationships
     * @param array<non-empty-string, ToManyRelationshipSetabilityInterface<TCondition, TSorting, TEntity, object>> $toManyRelationships
     */
    public function __construct(
        protected readonly array $attributes,
        protected readonly array $toOneRelationships,
        protected readonly array $toManyRelationships
    ) {}

    /**
     * @return array<non-empty-string, AttributeSetabilityInterface<TCondition, TEntity>>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<non-empty-string, ToOneRelationshipSetabilityInterface<TCondition, TSorting, TEntity, object>>
     */
    public function getToOneRelationships(): array
    {
        return $this->toOneRelationships;
    }

    /**
     * @return array<non-empty-string, ToManyRelationshipSetabilityInterface<TCondition, TSorting, TEntity, object>>
     */
    public function getToManyRelationships(): array
    {
        return $this->toManyRelationships;
    }
}
