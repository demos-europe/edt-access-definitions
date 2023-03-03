<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

class AttributeAccessException extends PropertyAccessException
{
    /**
     * @param non-empty-string $propertyName
     * @param non-empty-list<non-empty-string> $propertyPath
     */
    public static function attributeValueForPathInvalid(string $propertyName, array $propertyPath): self
    {
        $stringPath = implode('.', $propertyPath);
        return new self($propertyName, "Value retrieved via property path '$stringPath' is not allowed by readability settings.");
    }

    /**
     * @param non-empty-string $propertyName
     */
    public static function attributeValueForNameInvalid(string $propertyName, mixed $actualValue): self
    {
        $valueType = gettype($actualValue);
        return new self($propertyName, "The type '$valueType' retrieved from property '$propertyName' is not valid as type of attribute according to the readability settings.");
    }
}
