<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Contracts\Types\UpdatableTypeInterface;
use EDT\Wrapping\TypeProviders\OptionalTypeRequirementInterface;

/**
 * Provides utility methods to access processed information of a given {@link TypeInterface}.
 *
 * As {@link TypeInterface} provides raw information only some of them need to be processed
 * before decisions within the application logic can be made. This class encapsulates the
 * most basic processing of these raw information.
 *
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 */
class TypeAccessor
{
    /**
     * @var TypeProviderInterface<TCondition, TSorting>
     */
    private TypeProviderInterface $typeProvider;

    /**
     * @param TypeProviderInterface<TCondition, TSorting> $typeProvider
     */
    public function __construct(TypeProviderInterface $typeProvider)
    {
        $this->typeProvider = $typeProvider;
    }

    /**
     * @param TypeInterface<TCondition, TSorting, object> $type
     *
     * @return array<non-empty-string, ReadableTypeInterface<TCondition, TSorting, object>|null>
     *
     * @throws TypeRetrievalAccessException
     */
    public function getAccessibleReadableProperties(TypeInterface $type): array
    {
        if (!$type instanceof ReadableTypeInterface) {
            return [];
        }
        $readableProperties = $type->getReadableProperties();
        $readableProperties = array_map([$this, 'getTypeInstanceOrNull'], $readableProperties);
        $readableProperties = array_filter($readableProperties, [$this, 'isReadableProperty']);

        return $readableProperties;
    }

    /**
     * Collects the properties of the given type that are updatable.
     *
     * If the given type itself is not an instance of {@link UpdatableTypeInterface} an empty array
     * will be returned.
     *
     * @template TEntity of object
     *
     * @param TypeInterface<TCondition, TSorting, TEntity> $type
     * @param TEntity                   $updateTarget
     *
     * @return array<non-empty-string, TypeInterface<TCondition, TSorting, object>|null>
     */
    public function getAccessibleUpdatableProperties(TypeInterface $type, object $updateTarget): array
    {
        if (!$type instanceof UpdatableTypeInterface) {
            return [];
        }
        $updatableProperties = $type->getUpdatableProperties($updateTarget);
        $updatableProperties = array_map([$this, 'getTypeInstanceOrNull'], $updatableProperties);
        return array_filter($updatableProperties, [$this, 'isUpdatableProperty']);
    }

    /**
     * @param non-empty-string $typeIdentifier
     *
     * @return OptionalTypeRequirementInterface<TypeInterface<TCondition, TSorting, object>>
     */
    public function requestType(string $typeIdentifier): OptionalTypeRequirementInterface
    {
        return $this->typeProvider->requestType($typeIdentifier);
    }

    /**
     * @param TypeInterface<TCondition, TSorting, object> $type
     */
    private function isReadableRelationship(TypeInterface $type): bool
    {
        return $type->isAvailable() && $type->isReferencable() && $type instanceof ReadableTypeInterface;
    }

    /**
     * @param non-empty-string|null $typeIdentifier
     *
     * @return TypeInterface<TCondition, TSorting, object>|null
     *
     * @throws TypeRetrievalAccessException
     */
    private function getTypeInstanceOrNull(?string $typeIdentifier): ?TypeInterface
    {
        if (null === $typeIdentifier) {
            return null;
        }

        return $this->typeProvider->requestType($typeIdentifier)->getTypeInstance();
    }

    /**
     * @param TypeInterface<TCondition, TSorting, object>|null $type
     */
    private function isReadableProperty(?TypeInterface $type): bool
    {
        return null === $type || $this->isReadableRelationship($type);
    }

    /**
     * @param TypeInterface<TCondition, TSorting, object>|null $type
     */
    private function isUpdatableProperty(?TypeInterface $type): bool
    {
        // TODO: add `instanceof UpdatableTypeInterface` check?
        return null === $type || ($type->isAvailable() && $type->isReferencable());
    }
}
