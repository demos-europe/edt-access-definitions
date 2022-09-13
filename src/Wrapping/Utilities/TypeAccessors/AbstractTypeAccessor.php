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
 */
abstract class AbstractTypeAccessor
{
    /**
     * Returns the corresponding type, assuming the property is available as relationship.
     *
     * Implementing this method allows to limit the access to properties by different
     * criteria, e.g. if the instance has the context of filtering then only filterable
     * properties should be considered.
     *
     * @param T                $type
     * @param non-empty-string $propertyName
     *
     * @return T
     *
     * @throws PropertyAccessException if the property isn't an available relationship
     */
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

    /**
     * If the given property name is just an alias for a different path, then
     * that path will be returned as array. Otherwise, an array containing only the given
     * property name will be returned.
     *
     * @param non-empty-string $propertyName
     *
     * @return non-empty-list<non-empty-string>
     */
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
     * @return array<non-empty-string, non-empty-string|null>
     */
    abstract protected function getProperties(TypeInterface $type): array;

    /**
     * @param non-empty-string $typeIdentifier
     *
     * @return T
     *
     * @throws TypeRetrievalAccessException
     */
    abstract protected function getType(string $typeIdentifier): TypeInterface;
}
