<?php

declare(strict_types=1);

namespace EDT\Querying\ObjectProviders;

use EDT\Querying\Contracts\ObjectProviderInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Utilities\SchemaPathProcessor;

/**
 * @template C of \EDT\Querying\Contracts\PathsBasedInterface
 * @template S of \EDT\Querying\Contracts\PathsBasedInterface
 * @template T of object
 *
 * @template-implements ObjectProviderInterface<C, S, T>
 */
class TypeRestrictedEntityProvider implements ObjectProviderInterface
{
    /**
     * @var ObjectProviderInterface<C, S, T>
     */
    private $baseProvider;

    /**
     * @var ReadableTypeInterface<C, S, T>
     */
    private $type;

    /**
     * @var SchemaPathProcessor
     */
    private $schemaPathProcessor;

    /**
     * @param ObjectProviderInterface<C, S, T> $baseProvider
     * @param ReadableTypeInterface<C, S, T>   $type
     */
    public function __construct(
        ObjectProviderInterface $baseProvider,
        ReadableTypeInterface $type,
        SchemaPathProcessor $schemaPathProcessor
    ) {
        $this->baseProvider = $baseProvider;
        $this->type = $type;
        $this->schemaPathProcessor = $schemaPathProcessor;
    }

    /**
     * @return iterable<T>
     *
     * {@inheritDoc}
     *
     * @throws AccessException
     */
    public function getObjects(array $conditions, array $sortMethods = [], int $offset = 0, int $limit = null): iterable
    {
        if (!$this->type->isAvailable()) {
            throw AccessException::typeNotAvailable($this->type);
        }

        $conditions = $this->schemaPathProcessor->mapConditions($this->type, ...$conditions);
        $sortMethods = $this->schemaPathProcessor->mapSortMethods($this->type, ...$sortMethods);

        // get the actual entities
        return $this->baseProvider->getObjects($conditions, $sortMethods, $offset, $limit);
    }
}
