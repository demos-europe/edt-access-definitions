<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use InvalidArgumentException;
use Throwable;

/**
 * @template TType of object
 */
class AccessException extends InvalidArgumentException
{
    /**
     * @param TType $type
     * @param non-empty-string $message
     */
    protected function __construct(
        protected readonly object $type,
        string $message,
        Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @param TType $type
     *
     * @return self<TType>
     */
    public static function typeNotDirectlyAccessible(object $type): self
    {
        $typeClass = $type::class;

        return new self($type, "Type '$typeClass' not directly accessible.");
    }

    /**
     * @param TType $type
     *
     * @return AccessException<TType>
     */
    public static function unexpectedArguments(object $type, int $expected, int $actual): self
    {
        $typeClass = $type::class;

        return new self($type, "Unexpected arguments received for type class '$typeClass'. Expected $expected arguments, got $actual arguments.");
    }

    /**
     * @param TType $type
     *
     * @return self<TType>
     */
    public static function typeNotAvailable(object $type): self
    {
        $typeClass = $type::class;

        return new self($type, "Type class '$typeClass' not available.");
    }

    /**
     * @param TType $type
     *
     * @return AccessException<TType>
     */
    public static function typeNotFilterable(object $type): self
    {
        $typeClass = $type::class;

        return new self($type, "The type class you try to access is not exposed as filterable relationship: $typeClass");
    }

    /**
     * @param TType $type
     *
     * @return AccessException<TType>
     */
    public static function typeNotSortable(object $type): self
    {
        $typeClass = $type::class;

        return new self($type, "The type class you try to access is not exposed as sortable relationship: $typeClass");
    }

    /**
     * @param TType $type
     *
     * @return AccessException<TType>
     */
    public static function multipleEntitiesByIdentifier(object $type): self
    {
        $typeClass = $type::class;

        return new self($type, "Multiple entities were found for the given identifier when accessing type class '$typeClass'. The identifier must be unique.");
    }

    /**
     * @param TType $type
     *
     * @return AccessException<TType>
     */
    public static function noEntityByIdentifier(object $type): self
    {
        $typeClass = $type::class;

        return new self($type, "No entity could be found when accessing type class '$typeClass'. Either no one exists for the given identifier or the given types access condition restricts the access.");
    }

    /**
     * @param TType $type
     * @param non-empty-string $methodName
     *
     * @return AccessException<TType>
     */
    public static function failedToParseAccessor(object $type, string $methodName): self
    {
        $typeClass = $type::class;

        return new self($type, "The method you've called during the processing of '$typeClass' is not supported: '$methodName'");
    }

    /**
     * @param TType $type
     * @param Throwable $previous
     * @param non-empty-list<non-empty-string> $path
     *
     * @return AccessException<TType>
     */
    public static function pathDenied(object $type, Throwable $previous, array $path): self
    {
        $pathString = implode('.', $path);
        $typeClass = $type::class;

        return new self($type, "Access with the path '$pathString' into the type class '$typeClass' was denied.", $previous);
    }

    /**
     * @return TType
     */
    public function getType(): object
    {
        return $this->type;
    }
}
