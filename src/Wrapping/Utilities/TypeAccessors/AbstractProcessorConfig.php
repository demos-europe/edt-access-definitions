<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use function array_key_exists;

/**
 * Implementing this class allows to limit the access to properties by different
 * criteria, e.g. the {@link ExternFilterableProcessorConfig} will only allow access
 * to filterable properties and types.
 *
 * @template TType of TypeInterface<PathsBasedInterface, PathsBasedInterface, object>
 */
abstract class AbstractProcessorConfig
{
    /**
     * @param TypeProviderInterface<PathsBasedInterface, PathsBasedInterface> $typeProvider
     * @param TType                                                           $rootType
     */
    public function __construct(
        protected readonly TypeProviderInterface $typeProvider,
        private readonly TypeInterface $rootType
    ) {}

    /**
     * @return TType
     */
    public function getRootType(): TypeInterface
    {
        return $this->rootType;
    }

    /**
     * @param TType            $type
     * @param non-empty-string $property
     *
     * @return TType|null
     *
     * @throws PropertyAccessException
     */
    public function getPropertyType(TypeInterface $type, string $property): ?TypeInterface
    {
        $availableProperties = $this->getProperties($type);
        // abort if the (originally accessed/non-de-aliased) property is not available
        if (!array_key_exists($property, $availableProperties)) {
            $availablePropertyNames = array_keys($availableProperties);
            throw PropertyAccessException::propertyNotAvailableInType($property, $type, ...$availablePropertyNames);
        }

        return $availableProperties[$property];
    }

    /**
     * Get actually available properties of the given {@link TypeInterface type}.
     *
     * @param TType $type
     *
     * @return array<non-empty-string, TType|null>
     */
    abstract public function getProperties(TypeInterface $type): array;
}
