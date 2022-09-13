<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Contracts\Types\UpdatableTypeInterface;

/**
 * Provides utility methods to access processed information of a given {@link TypeInterface}.
 *
 * As {@link TypeInterface} provides raw information only some of them need to be processed
 * before decisions within the application logic can be made. This class encapsulates the
 * most basic processing of these raw information.
 *
 * @template C of \EDT\Querying\Contracts\PathsBasedInterface
 * @template S of \EDT\Querying\Contracts\PathsBasedInterface
 */
class TypeAccessor
{
    /**
     * @var TypeProviderInterface<C, S>
     */
    private $typeProvider;

    /**
     * @param TypeProviderInterface<C, S> $typeProvider
     */
    public function __construct(TypeProviderInterface $typeProvider)
    {
        $this->typeProvider = $typeProvider;
    }

    /**
     * @param TypeInterface<C, S, object> $type
     *
     * @return array<non-empty-string, ReadableTypeInterface<C, S, object>|null>
     *
     * @throws TypeRetrievalAccessException
     */
    public function getAccessibleReadableProperties(TypeInterface $type): array
    {
        if (!$type instanceof ReadableTypeInterface) {
            return [];
        }
        $readableProperties = $type->getReadableProperties();
        $readableProperties = array_map([$this, 'getTypeInstance'], $readableProperties);
        $readableProperties = array_filter($readableProperties, [$this, 'isReadableProperty']);

        return $readableProperties;
    }

    /**
     * Collects the properties of the given type that are updatable.
     *
     * If the given type itself is not an instance of {@link UpdatableTypeInterface} an empty array
     * will be returned.
     *
     * @template T of object
     *
     * @param TypeInterface<C, S, T> $type
     * @param T                   $updateTarget
     *
     * @return array<non-empty-string, TypeInterface<C, S, object>|null>
     */
    public function getAccessibleUpdatableProperties(TypeInterface $type, object $updateTarget): array
    {
        if (!$type instanceof UpdatableTypeInterface) {
            return [];
        }
        $updatableProperties = $type->getUpdatableProperties($updateTarget);
        $updatableProperties = array_map([$this, 'getTypeInstance'], $updatableProperties);
        return array_filter($updatableProperties, [$this, 'isUpdatableProperty']);
    }

    /**
     * @param non-empty-string|null $typeIdentifier
     *
     * @return TypeInterface<C, S, object>|null
     *
     * @throws TypeRetrievalAccessException
     */
    private function getTypeInstance(?string $typeIdentifier): ?TypeInterface
    {
        if (null === $typeIdentifier) {
            return null;
        }

        return $this->typeProvider->getType($typeIdentifier);
    }

    /**
     * @param TypeInterface<C, S, object>|null $type
     */
    private function isReadableProperty(?TypeInterface $type): bool
    {
        return null === $type || ($type->isAvailable() && $type->isReferencable() && $type instanceof ReadableTypeInterface);
    }

    /**
     * @param TypeInterface<C, S, object>|null $type
     */
    private function isUpdatableProperty(?TypeInterface $type): bool
    {
        // TODO: add instanceof check?
        return null === $type || ($type->isAvailable() && $type->isReferencable());
    }
}
