<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\ExposableRelationshipTypeInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Contracts\Types\UpdatableTypeInterface;
use EDT\Wrapping\TypeProviders\TypeRequirement;

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
     * @param ReadableTypeInterface&TypeInterface<TCondition, TSorting, object> $type
     *
     * @return array<non-empty-string, (ExposableRelationshipTypeInterface&ReadableTypeInterface<TCondition, TSorting, object>)|null>
     *
     * @throws TypeRetrievalAccessException
     */
    public function getAccessibleReadableProperties(TypeInterface $type): array
    {
        $allowedProperties = [];
        foreach ($type->getReadableProperties() as $propertyName => $typeIdentifier) {
            if (null === $typeIdentifier) {
                // access to attributes is not restricted by further considerations
                $allowedProperties[$propertyName] = null;
            } else {
                // access to relationships depends on readability and "exposedness"
                $relationshipType = $this->typeProvider->requestType($typeIdentifier)
                    ->instanceOf(ReadableTypeInterface::class)
                    ->exposedAsRelationship()
                    ->getInstanceOrNull();
                if (null !== $relationshipType) {
                    $allowedProperties[$propertyName] = $relationshipType;
                }
            }
        }

        return $allowedProperties;
    }

    /**
     * Collects the properties of the given type that are updatable.
     *
     * @template TEntity of object
     *
     * @param UpdatableTypeInterface<TEntity> $type
     * @param TEntity                         $updateTarget
     *
     * @return array<non-empty-string, (ExposableRelationshipTypeInterface&TypeInterface<TCondition, TSorting, object>)|null>
     */
    public function getAccessibleUpdatableProperties(UpdatableTypeInterface $type, object $updateTarget): array
    {
        $updatableProperties = [];
        foreach ($type->getUpdatableProperties($updateTarget) as $propertyName => $relationshipTypeIdentifier) {
            if (null === $relationshipTypeIdentifier) {
                $updatableProperties[$propertyName] = null;
            } else {
                 // $relationshipType is the relationship property to set, not the type of the instance which is updated
                 $relationshipType = $this->typeProvider->requestType($relationshipTypeIdentifier)
                     ->exposedAsRelationship()
                     ->getInstanceOrNull();
                 if (null !== $relationshipType) {
                     $updatableProperties[$propertyName] = $relationshipType;
                 }
            }
        }

        return $updatableProperties;
    }

    /**
     * @param non-empty-string $typeIdentifier
     *
     * @return TypeRequirement<TypeInterface<TCondition, TSorting, object>>
     */
    public function requestType(string $typeIdentifier): TypeRequirement
    {
        return $this->typeProvider->requestType($typeIdentifier);
    }
}
