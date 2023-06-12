<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use Exception;
use Throwable;

/**
 * @template TType of object
 * @template-extends AccessException<TType>
 */
class PropertyAccessException extends AccessException
{
    /**
     * @param TType $type
     * @param non-empty-string $propertyName
     * @param non-empty-string $message
     */
    protected function __construct(
        object $type,
        protected string $propertyName,
        string $message,
        Throwable $previous = null
    ) {
        parent::__construct($type, $message, $previous);
    }

    /**
     * @param non-empty-string $property
     * @param TType $type
     * @param list<non-empty-string> $availableProperties
     *
     * @return PropertyAccessException<TType>
     */
    public static function propertyNotAvailableInType(string $property, object $type, array $availableProperties): self
    {
        $typeClass = $type::class;
        $propertyList = implode(', ', $availableProperties);

        return new self($type, $property, "No property '$property' is available in the type class '$typeClass'. Available properties are: $propertyList");
    }

    /**
     * @param non-empty-string $property
     * @param TType $type
     * @param list<non-empty-string> $availableProperties
     *
     * @return PropertyAccessException<TType>
     */
    public static function propertyNotAvailableInReadableType(string $property, object $type, array $availableProperties): self
    {
        $typeClass = $type::class;
        $propertyList = implode(', ', $availableProperties);

        return new self($type, $property, "No property '$property' is available in the readable type class '$typeClass'. Available properties are: $propertyList");
    }

    /**
     * @param non-empty-string $property
     * @param TType $type
     * @param non-empty-string ...$availableProperties
     *
     * @return PropertyAccessException<TType>
     */
    public static function propertyNotAvailableInUpdatableType(string $property, object $type, string ...$availableProperties): self
    {
        $typeClass = $type::class;
        $propertyList = implode(', ', $availableProperties);

        return new self($type, $property, "No property '$property' is available in the updatable type class '$typeClass'. Available properties are: $propertyList");
    }

    /**
     * @param non-empty-string $property
     * @param TType $type
     *
     * @return PropertyAccessException<TType>
     */
    public static function nonRelationship(string $property, object $type): self
    {
        $typeClass = $type::class;

        return new self($type, $property, "The property '$property' exists in the type class '$typeClass' but it is not a relationship and the path continues after it. Check your access to the schema of the type.");
    }

    /**
     * @param TType $type
     * @param non-empty-string $propertyName
     *
     * @return PropertyAccessException<TType>
     */
    public static function update(object $type, string $propertyName, Exception $cause): self
    {
        return new self($type, $propertyName, "Failed to update property '$propertyName'.", $cause);
    }

    /**
     * @return non-empty-string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
