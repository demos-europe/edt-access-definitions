<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\Contracts\SortMethodInterface;
use EDT\Wrapping\Contracts\Types\AliasableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use function array_key_exists;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use function is_object;

/**
 * Saves computational results to skip the computation when called again with the same parameters.
 *
 * Assumes that for the same {@link TypeInterface} instance the return of the following method stays
 * the same during the lifetime of the instance of this class:
 *
 * * {@link TypeInterface::getDefaultSortMethods()}
 * * {@link TypeInterface::getAccessCondition()}
 * * {@link TypeInterface::getInternalProperties()}
 * * {@link AliasableTypeInterface::getAliases()} (if implemented)
 *
 * Also assumes that the values behind the these property paths that are accessed by these
 * conditions and sort methods stay the same in the given entities (and their accessed nested
 * relationship entities) during the lifetime of the instance of this class.
 *
 * Note that the property values are read by a {@link PropertyAccessorInterface} instance, which
 * is assumed to not read the values differently for the same entities and properties as well.
 */
class CachingPropertyReader extends PropertyReader
{
    /**
     * @var array<non-empty-string, object|null>
     */
    private array $toOneValueCache = [];

    /**
     * @var array<non-empty-string, list<object>>
     */
    private array $toManyValueCache = [];

    public function determineToOneRelationshipValue(TransferableTypeInterface $relationshipType, object $relationshipEntity): ?object
    {
        $hash = $this->createHash($relationshipType, $relationshipEntity);
        if (!array_key_exists($hash, $this->toOneValueCache)) {
            $relationshipEntity = parent::determineToOneRelationshipValue($relationshipType, $relationshipEntity);
            $this->toOneValueCache[$hash] = $relationshipEntity;

            return $relationshipEntity;
        }

        return $this->toOneValueCache[$hash];
    }

    public function determineToManyRelationshipValue(TransferableTypeInterface $relationshipType, array $relationshipEntities): array
    {
        $hash = $this->createHash($relationshipType, $relationshipEntities);
        if (!array_key_exists($hash, $this->toManyValueCache)) {
            $relationshipEntities =  parent::determineToManyRelationshipValue($relationshipType, $relationshipEntities);
            $this->toManyValueCache[$hash] = $relationshipEntities;

            return $relationshipEntities;
        }

        return $this->toManyValueCache[$hash];
    }

    /**
     * Create cache hash from 2 values that need to be distinct to be cached.
     *
     * @template TEntity of object
     *
     * @param TransferableTypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity> $relationshipType
     * @param TEntity|list<TEntity> $relationshipValue
     *
     * @return non-empty-string
     */
    private function createHash(TransferableTypeInterface $relationshipType, object|array $relationshipValue): string
    {
        $hashRelationship = spl_object_hash($relationshipType);

        if (is_object($relationshipValue)) {
            $hashProperty = spl_object_hash($relationshipValue);
        } else {
            $hashProperty = serialize($relationshipValue);
        }

        return hash('sha256', $hashRelationship.$hashProperty);
    }
}
