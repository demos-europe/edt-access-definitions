<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template T of TypeInterface
 */
interface ContextualizedTypeAccessorInterface
{
    /**
     * Returns the corresponding type, assuming the property is available as relationship.
     *
     * Implementing this method allows to limit the access to properties by different
     * criteria, e.g. if the instance has the context of filtering then only filterable
     * properties should be considered.
     *
     * @param T $type
     *
     * @return T
     *
     * @throws PropertyAccessException if the property isn't an available relationship
     */
    public function getPropertyType(TypeInterface $type, string $propertyName): TypeInterface;

    /**
     * If the given property name is just an alias for a different path, then
     * that path will be returned as array. Otherwise, an array containing only the given
     * property name will be returned.
     *
     * @return array<int, string>
     */
    public function getDeAliasedPath(TypeInterface $type, string $propertyName): array;
}
