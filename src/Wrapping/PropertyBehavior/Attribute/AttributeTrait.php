<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Attribute;

use Webmozart\Assert\Assert;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

trait AttributeTrait
{
    /**
     * @return simple_primitive|array<int|string, mixed>|null
     *
     * TODO: use stricter validation
     */
    protected function assertValidValue(mixed $attributeValue): int|float|string|bool|array|null
    {
        if (null === $attributeValue
            || is_string($attributeValue)
            || is_int($attributeValue)
            || is_float($attributeValue)
            || is_bool($attributeValue)) {
            return $attributeValue;
        }

        Assert::isArray($attributeValue);

        return $attributeValue;
    }
}
