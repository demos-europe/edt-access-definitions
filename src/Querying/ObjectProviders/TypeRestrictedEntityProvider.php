<?php

declare(strict_types=1);

namespace EDT\Querying\ObjectProviders;

use EDT\Querying\Contracts\ObjectProviderInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Utilities\SchemaPathProcessor;

/**
 * @template T of object
 *
 * @template-implements ObjectProviderInterface<T>
 */
class TypeRestrictedEntityProvider implements ObjectProviderInterface
{
    /**
     * @var ObjectProviderInterface<T>
     */
    private $baseProvider;
    /**
     * @var ReadableTypeInterface<T>
     */
    private $type;
    /**
     * @var SchemaPathProcessor
     */
    private $schemaPathProcessor;

    /**
     * @param ObjectProviderInterface<T> $baseProvider
     * @param ReadableTypeInterface<T>   $type
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
