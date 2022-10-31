<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * Implementing this class allows to limit the access to properties by different
 * criteria, e.g. the {@link ExternFilterableProcessorConfig} will only allow access
 * to filterable properties and types.
 *
 * @template TType of TypeInterface<\EDT\Querying\Contracts\PathsBasedInterface, \EDT\Querying\Contracts\PathsBasedInterface, object>
 */
abstract class AbstractProcessorConfig
{
    /**
     * @var TypeProviderInterface<PathsBasedInterface, PathsBasedInterface>
     */
    protected TypeProviderInterface $typeProvider;

    /**
     * @var TType
     */
    private TypeInterface $rootType;

    /**
     * @param TypeProviderInterface<PathsBasedInterface, PathsBasedInterface> $typeProvider
     * @param TType                                                           $rootType
     */
    public function __construct(TypeProviderInterface $typeProvider, TypeInterface $rootType)
    {
        $this->typeProvider = $typeProvider;
        $this->rootType = $rootType;
    }

    /**
     * @return TType
     */
    public function getRootType(): TypeInterface
    {
        return $this->rootType;
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
     * @param TType $type
     *
     * @return array<non-empty-string, non-empty-string|null>
     */
    abstract public function getProperties(TypeInterface $type): array;

    /**
     * @param non-empty-string $typeIdentifier
     *
     * @return TType
     *
     * @throws TypeRetrievalAccessException
     */
    abstract public function getRelationshipType(string $typeIdentifier): TypeInterface;
}
