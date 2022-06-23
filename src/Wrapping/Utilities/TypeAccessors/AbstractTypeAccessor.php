<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\RelationshipAccessException;
use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use function array_key_exists;

/**
 * @template T of TypeInterface
 *
 * @template-implements ContextualizedTypeAccessorInterface<T>
 */
abstract class AbstractTypeAccessor implements ContextualizedTypeAccessorInterface
{
    public function getPropertyType(TypeInterface $type, string $propertyName): TypeInterface
    {
        $availableProperties = $this->getProperties($type);
        // abort if the (originally accessed/non-de-aliased) property is not available
        if (!array_key_exists($propertyName, $availableProperties)) {
            $availablePropertyNames = array_keys($availableProperties);
            throw PropertyAccessException::propertyNotAvailableInType($propertyName, $type, ...$availablePropertyNames);
        }

        $propertyTypeIdentifier = $availableProperties[$propertyName];

        if (null === $propertyTypeIdentifier) {
            throw PropertyAccessException::nonRelationship($propertyName, $type);
        }

        try {
            return $this->getType($propertyTypeIdentifier);
        } catch (TypeRetrievalAccessException $exception) {
            throw RelationshipAccessException::relationshipTypeAccess($type, $propertyName, $exception);
        }
    }

    public function getDeAliasedPath(TypeInterface $type, string $propertyName): array
    {
        $aliases = $type->getAliases();

        return $aliases[$propertyName] ?? [$propertyName];
    }

    /**
     * Get actually available properties of the given {@link TypeInterface type}.
     *
     * @param T $type
     *
     * @return array<string, string|null>
     */
    abstract protected function getProperties(TypeInterface $type): array;

    /**
     * @param string $typeIdentifier
     *
     * @return T
     *
     * @throws TypeRetrievalAccessException
     */
    abstract protected function getType(string $typeIdentifier): TypeInterface;
}
