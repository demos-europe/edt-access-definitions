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

    protected function determineRelationshipValue(
        WrapperFactoryInterface $wrapperFactory,
        ReadableTypeInterface $relationship,
        $propertyValue
    ) {
        $hash = $this->createHash($wrapperFactory, $relationship, $propertyValue);
        if (!array_key_exists($hash, $this->valueCache)) {
            $value = parent::determineRelationshipValue($wrapperFactory, $relationship, $propertyValue);
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
    private function createHash(WrapperFactoryInterface $wrapperFactory, ReadableTypeInterface $relationship, $propertyValue): string
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

        $hashWrapper = spl_object_hash($wrapperFactory);

        return hash('sha256', $hashRelationship.$hashProperty.$hashWrapper);
    }
}
