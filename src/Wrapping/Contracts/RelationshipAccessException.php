<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Querying\Contracts\EntityBasedInterface;
use Exception;
use Throwable;

/**
 * @template TType of EntityBasedInterface
 * @template-extends PropertyAccessException<TType>
 */
class RelationshipAccessException extends PropertyAccessException
{
    /**
     * @param TType $type
     * @param non-empty-string $propertyName
     * @param non-empty-string $message
     */
    protected function __construct(
        EntityBasedInterface $type,
        string $propertyName,
        string $message,
        Throwable $previous = null
    ) {
        parent::__construct($type, $propertyName, $message, $previous);
    }

    /**
     * @param TType $type
     * @param non-empty-string $property
     *
     * @return RelationshipAccessException<TType>
     */
    public static function relationshipTypeAccess(EntityBasedInterface $type, string $property, TypeRetrievalAccessException $previous): self
    {
        $typeClass = $type::class;

        return new self($type, $property, "Property '$property' is available and a relationship in the type class '$typeClass', but its destination type is not accessible.", $previous);
    }

    /**
     * @param TType $type
     * @param non-empty-string $propertyName
     *
     * @return RelationshipAccessException<TType>
     */
    public static function updateRelationshipCondition(EntityBasedInterface $type, string $propertyName, Exception $exception): self
    {
        return new self($type, $propertyName, "Failed to assert that the relationship instance is allowed to be set: $propertyName", $exception);
    }

    /**
     * @param TType $type
     * @param non-empty-string $propertyName
     *
     * @return RelationshipAccessException<TType>
     */
    public static function updateRelationshipsCondition(EntityBasedInterface $type, string $propertyName, Exception $exception): self
    {
        return new self($type, $propertyName, "Failed to assert that all relationship instances are allowed to be set: $propertyName", $exception);
    }
}
