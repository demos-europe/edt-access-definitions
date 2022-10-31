<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PaginationException;
use EDT\Querying\Contracts\SortException;
use EDT\Querying\Contracts\SortMethodInterface;
use EDT\Querying\Utilities\ConditionEvaluator;
use EDT\Querying\Utilities\Iterables;
use EDT\Querying\Utilities\Sorter;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;
use Exception;
use function count;

/**
 * Provides helper methods to determine the access rights to properties of an entity based on the implementation of a given {@link TypeInterface}.
 */
class PropertyReader
{
    private SchemaPathProcessor $schemaPathProcessor;

    private ConditionEvaluator $conditionEvaluator;

    private Sorter $sorter;

    public function __construct(
        SchemaPathProcessor $schemaPathProcessor,
        ConditionEvaluator $conditionEvaluator,
        Sorter $sorter
    ) {
        $this->schemaPathProcessor = $schemaPathProcessor;
        $this->conditionEvaluator = $conditionEvaluator;
        $this->sorter = $sorter;
    }

    /**
     * If the given `$propertyValue` is iterable then we
     * assume the relationship is a to-many. In this case every item in the iterable `$propertyValue`
     * will be wrapped using the given {@link WrapperFactoryInterface} and the resulting array returned.
     *
     * If the given `$propertyValue` is not iterable then
     * we assume the relationship is a to-one. In this case the given {@link WrapperFactoryInterface}
     * will be used on the `$propertyValue` and the result returned.
     *
     * The {@link WrapperFactoryInterface} will only be used on a value
     * if the {@link TypeInterface::getAccessCondition() access condition} of the given
     * `$relationship` allows the access to the value. Otherwise, for a to-many relationship
     * the value will be skipped (not wrapped or returned) and for a to-one relationship
     * instead of the wrapped value `null` will be returned.
     *
     * In case of a to-many relationship the entities will be sorted according to the definition
     * of {@link TypeInterface::getDefaultSortMethods()} of the relationship.
     *
     * @template TEntity of object
     *
     * @param ReadableTypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity> $type
     * @param TEntity|iterable<TEntity>|null                                               $valueOrValues
     *
     * @return TEntity|list<TEntity>|null
     *
     * @throws PathException
     * @throws PaginationException
     * @throws SortException
     */
    public function determineRelationshipValue(ReadableTypeInterface $type, $valueOrValues)
    {
        // if to-many relationship, wrap each available value and return them
        if (is_iterable($valueOrValues)) {
            return $this->filterAndSort($type, Iterables::asArray($valueOrValues));
        }

        // if null relationship return null
        if (null === $valueOrValues) {
            return null;
        }

        // if to-one relationship wrap the value if available and return it, otherwise return null
        $entitiesToWrap = $this->filterAndSort($type, [$valueOrValues]);

        switch (count($entitiesToWrap)) {
            case 1:
                // if available to-one relationship return the wrapped object
                return array_pop($entitiesToWrap);
            case 0:
                // if restricted to-one relationship use null instead
                return null;
            default:
                throw new Exception('Unexpected count: '.count($entitiesToWrap));
        }
    }

    /**
     * @template TEntity of object
     *
     * @param ReadableTypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity> $relationship
     * @param array<int|string, TEntity>                                                   $entities
     *
     * @return list<TEntity>
     *
     * @throws PaginationException
     * @throws PathException
     * @throws SortException
     */
    private function filterAndSort(ReadableTypeInterface $relationship, array $entities): array
    {
        if (!$relationship->isAvailable()) {
            throw AccessException::typeNotAvailable($relationship);
        }

        $condition = $this->schemaPathProcessor->processAccessCondition($relationship);
        $sortMethods = $this->schemaPathProcessor->processDefaultSortMethods($relationship);

        // filter out restricted items and sort the remainders
        $entities = $this->conditionEvaluator->filterArray($entities, $condition);
        if ([] !== $sortMethods) {
            $entities = $this->sorter->sortArray($entities, $sortMethods);
        }

        return array_values($entities);
    }
}
