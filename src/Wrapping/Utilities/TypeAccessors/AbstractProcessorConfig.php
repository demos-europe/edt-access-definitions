<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Querying\PropertyPaths\PropertyLink;

/**
 * Implementing this class allows to limit the access to properties by different
 * criteria, e.g. the {@link ExternFilterableProcessorConfig} will only allow access
 * to filterable properties and types.
 *
 * @template TType of object
 */
abstract class AbstractProcessorConfig
{
    /**
     * @param TType $rootType
     */
    public function __construct(
        protected readonly object $rootType
    ) {}

    /**
     * @return TType
     */
    public function getRootType(): object
    {
        return $this->rootType;
    }

    /**
     * Get actually available properties of the given instance.
     *
     * @param TType $type
     *
     * @return array<non-empty-string, PropertyLink<TType>>
     */
    abstract public function getProperties(object $type): array;
}
