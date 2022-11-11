<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Utilities\Iterables;
use InvalidArgumentException;
use function array_key_exists;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use function gettype;
use function is_object;

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

    public function determineToOneRelationshipValue(TransferableTypeInterface $relationshipType, ?object $value): ?object
    {
        $hash = $this->createHash($relationshipType, $value);
        if (!array_key_exists($hash, $this->toOneValueCache)) {
            $value = parent::determineToOneRelationshipValue($relationshipType, $value);
            $this->toOneValueCache[$hash] = $value;

            return $value;
        }

        return $this->toOneValueCache[$hash];
    }

    public function determineToManyRelationshipValue(TransferableTypeInterface $relationshipType, iterable $values): array
    {
        $hash = $this->createHash($relationshipType, $values);
        if (!array_key_exists($hash, $this->toManyValueCache)) {
            $values =  parent::determineToManyRelationshipValue($relationshipType, $values);
            $this->toManyValueCache[$hash] = $values;

            return $values;
        }

        return $this->toManyValueCache[$hash];
    }

    /**
     * Create cache hash from 3 values that need to be distinct to be cached.
     *
     * @param mixed|null $propertyValue
     *
     * @return non-empty-string
     */
    private function createHash(TransferableTypeInterface $relationship, $propertyValue): string
    {
        $hashRelationship = spl_object_hash($relationship);

        if (null === $propertyValue) {
            $hashProperty = '';
        } elseif (is_scalar($propertyValue)) {
            // int, float, string or bool
            $hashProperty = (string) $propertyValue;
        } elseif (is_object($propertyValue)) {
            $hashProperty = spl_object_hash($propertyValue);
        } elseif (is_iterable($propertyValue)) {
            $hashProperty = serialize(Iterables::asArray($propertyValue));
        } else {
            $valueType = gettype($propertyValue);
            throw new InvalidArgumentException("Unexpected value type '$valueType'.");
        }

        return hash('sha256', $hashRelationship.$hashProperty);
    }
}
