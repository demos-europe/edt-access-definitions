<?php

declare(strict_types=1);

namespace EDT\Querying\ObjectProviders;

use EDT\Querying\Contracts\ObjectProviderInterface;
use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PaginationException;
use EDT\Querying\Contracts\SortException;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Utilities\SchemaPathProcessor;

/**
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TEntity of object
 *
 * @template-implements ObjectProviderInterface<TCondition, TSorting, TEntity>
 *
 * @deprecated use the individual components manually and optimize them for your use-case
 */
class TypeRestrictedEntityProvider implements ObjectProviderInterface
{
    /**
     * @var ObjectProviderInterface<TCondition, TSorting, TEntity>
     */
    private $baseProvider;

    /**
     * @var ReadableTypeInterface<TCondition, TSorting, TEntity>
     */
    private $type;

    /**
     * @var SchemaPathProcessor
     */
    private $schemaPathProcessor;

    /**
     * @param ObjectProviderInterface<TCondition, TSorting, TEntity> $baseProvider
     * @param ReadableTypeInterface<TCondition, TSorting, TEntity>   $type
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
     * @return iterable<TEntity>
     *
     * {@inheritDoc}
     *
     * @throws PathException
     * @throws PaginationException
     * @throws SortException
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
