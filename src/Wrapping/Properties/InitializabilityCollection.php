<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;

/**
 * @template TEntity of object
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 */
class InitializabilityCollection
{
    /**
     * @param array<non-empty-string, ConstructorParameterInterface<TCondition, TSorting>> $orderedRequiredConstructorParameters
     * @param array<non-empty-string, ConstructorParameterInterface<TCondition, TSorting>> $orderedOptionalConstructorParameters
     * @param array<non-empty-string, AttributeInitializabilityInterface<TCondition, TEntity>> $attributeInitializabilities
     * @param array<non-empty-string, ToOneRelationshipInitializabilityInterface<TCondition, TSorting, TEntity, object>> $toOneRelationshipInitializabilities
     * @param array<non-empty-string, ToManyRelationshipInitializabilityInterface<TCondition, TSorting, TEntity, object>> $toManyRelationshipInitializabilities
     */
    public function __construct(
        protected readonly array $orderedRequiredConstructorParameters,
        protected readonly array $orderedOptionalConstructorParameters,
        protected readonly array $attributeInitializabilities,
        protected readonly array $toOneRelationshipInitializabilities,
        protected readonly array $toManyRelationshipInitializabilities
    ) {}

    /**
     * @return array<non-empty-string, mixed>
     */
    public function getRequiredAttributes(): array
    {
        return array_merge(
            $this->filterRequiredProperties($this->attributeInitializabilities),
            $this->filterAttributeProperties($this->orderedRequiredConstructorParameters)
        );
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function getRequiredToOneRelationshipIdentifiers(): array
    {
        return array_merge(
            $this->getTypeIdentifiers($this->filterRequiredProperties($this->toOneRelationshipInitializabilities)),
            $this->getToOneRelationshipIdentifiers($this->orderedRequiredConstructorParameters)
        );
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function getRequiredToManyRelationshipIdentifiers(): array
    {
        return array_merge(
            $this->getTypeIdentifiers($this->filterRequiredProperties($this->toManyRelationshipInitializabilities)),
            $this->filterToManyRelationshipIdentifiers($this->orderedRequiredConstructorParameters)
        );
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function getOptionalAttributes(): array
    {
        return array_merge(
            $this->filterOptionalProperties($this->attributeInitializabilities),
            $this->filterAttributeProperties($this->orderedOptionalConstructorParameters)
        );
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function getOptionalToOneRelationshipIdentifiers(): array
    {
        return array_merge(
            $this->getTypeIdentifiers($this->filterOptionalProperties($this->toOneRelationshipInitializabilities)),
            $this->getToOneRelationshipIdentifiers($this->orderedOptionalConstructorParameters)
        );
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function getOptionalToManyRelationshipIdentifiers(): array
    {
        return array_merge(
            $this->getTypeIdentifiers($this->filterOptionalProperties($this->toManyRelationshipInitializabilities)),
            $this->filterToManyRelationshipIdentifiers($this->orderedOptionalConstructorParameters)
        );
    }

    /**
     * @return array<non-empty-string, ConstructorParameterInterface<TCondition, TSorting>>
     */
    public function getOrderedConstructorArguments(): array
    {
        return array_merge(
            $this->orderedRequiredConstructorParameters,
            $this->orderedOptionalConstructorParameters
        );
    }

    /**
     * @return array<non-empty-string, AttributeSetabilityInterface<TCondition, TEntity>>
     */
    public function getNonConstructorAttributeSetabilities(): array
    {
        return $this->attributeInitializabilities;
    }

    /**
     * @return array<non-empty-string, ToOneRelationshipSetabilityInterface<TCondition, TSorting, TEntity, object>>
     */
    public function getNonConstructorToOneRelationshipSetabilities(): array
    {
        return $this->toOneRelationshipInitializabilities;
    }

    /**
     * @return array<non-empty-string, ToManyRelationshipSetabilityInterface<TCondition, TSorting, TEntity, object>>
     */
    public function getNonConstructorToManyRelationshipSetabilities(): array
    {
        return $this->toManyRelationshipInitializabilities;
    }

    /**
     * Returns all required items of the given array.
     *
     * @template TValue of PropertyInitializabilityInterface
     *
     * @param array<non-empty-string, TValue> $array
     *
     * @return array<non-empty-string, TValue>
     */
    protected function filterRequiredProperties(array $array): array
    {
        return array_filter(
            $array,
            static fn (PropertyInitializabilityInterface $item): bool => !$item->isOptional()
        );
    }

    /**
     * Returns all required items of the given array.
     *
     * @template TValue of PropertyInitializabilityInterface
     *
     * @param array<non-empty-string, TValue> $array
     *
     * @return array<non-empty-string, TValue>
     */
    protected function filterOptionalProperties(array $array): array
    {
        return array_filter(
            $array,
            static fn (PropertyInitializabilityInterface $item): bool => $item->isOptional()
        );
    }

    /**
     * @template TValue of ConstructorParameterInterface
     *
     * @param array<non-empty-string, TValue> $array
     *
     * @return array<non-empty-string, TValue>
     */
    protected function filterAttributeProperties(array $array): array
    {
        return array_filter(
            $array,
            static fn (ConstructorParameterInterface $item): bool => $item->isAttribute()
        );
    }

    /**
     * @param array<non-empty-string, ConstructorParameterInterface<TCondition, TSorting>> $array
     *
     * @return array<non-empty-string, non-empty-string>
     */
    protected function getToOneRelationshipIdentifiers(array $array): array
    {
        return array_map(
            static fn (ConstructorParameterInterface $item) => $item->getRelationshipType()->getTypeName(),
            array_filter($array, static fn (ConstructorParameterInterface $item): bool => $item->isToOneRelationship())
        );
    }

    /**
     * @param array<non-empty-string, ConstructorParameterInterface<TCondition, TSorting>> $array
     *
     * @return array<non-empty-string, non-empty-string>
     */
    protected function filterToManyRelationshipIdentifiers(array $array): array
    {
        return array_map(
            static fn (ConstructorParameterInterface $item) => $item->getRelationshipType()->getTypeName(),
            array_filter($array, static fn (ConstructorParameterInterface $item): bool => $item->isToManyRelationship()
        ));
    }

    /**
     * @param array<non-empty-string, RelationshipInterface<TransferableTypeInterface<TCondition, TSorting, object>>> $array
     *
     * @return array<non-empty-string, non-empty-string>
     */
    protected function getTypeIdentifiers(array $array): array
    {
        return array_map(
            static fn (RelationshipInterface $item): string => $item->getRelationshipType()->getTypeName(),
            $array
        );
    }
}
