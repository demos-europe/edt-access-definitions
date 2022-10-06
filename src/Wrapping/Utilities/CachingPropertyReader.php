<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Utilities\Iterables;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;
use InvalidArgumentException;
use function array_key_exists;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use function gettype;
use function is_object;

class CachingPropertyReader extends PropertyReader
{
    /**
     * @var array<non-empty-string, mixed>
     */
    private $valueCache = [];

    public function determineRelationshipValue(ReadableTypeInterface $type, $valueOrValues)
    {
        $hash = $this->createHash($type, $valueOrValues);
        if (!array_key_exists($hash, $this->valueCache)) {
            $value = parent::determineRelationshipValue($type, $valueOrValues);
            $this->valueCache[$hash] = $value;
        }

        return $this->valueCache[$hash];
    }

    /**
     * Create cache hash from 3 values that need to be distinct to be cached.
     *
     * @param mixed|null $propertyValue
     *
     * @return non-empty-string
     */
    private function createHash(ReadableTypeInterface $relationship, $propertyValue): string
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
