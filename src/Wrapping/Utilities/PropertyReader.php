<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\SortException;
use EDT\Querying\Contracts\SortMethodInterface;
use EDT\Querying\Utilities\ConditionEvaluator;
use EDT\Querying\Utilities\Iterables;
use EDT\Querying\Utilities\Sorter;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
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
     * The given {@link WrapperFactoryInterface} will be used on the given `$value` and the result
     * returned.
     *
     * The {@link WrapperFactoryInterface} will only be used on a value
     * if the {@link TypeInterface::getAccessCondition() access condition} of the given
     * `$relationshipType` allows the access to the value. Otherwise, instead of the wrapped value
     * `null` will be returned.
     *
     * In case of a to-many relationship the entities will be sorted according to the definition
     * of {@link TypeInterface::getDefaultSortMethods()} of the relationship.
     *
     * @template TEntity of object
     *
     * @param TransferableTypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity> $relationshipType
     * @param TEntity|null                                                                     $value
     *
     * @return TEntity|null
     *
     * @throws PathException
     */
    public function determineToOneRelationshipValue(TransferableTypeInterface $relationshipType, ?object $value): ?object
    {
        // if null relationship return null
        if (null === $value) {
            return null;
        }

        $condition = $this->schemaPathProcessor->processAccessCondition($relationshipType);

        // if to-one relationship: if available return the value to wrap, otherwise return null
        return $this->conditionEvaluator->evaluateCondition($value, $condition)
            ? $value
            : null;
    }

    /**
     * Each value in `$values` will be wrapped using the given {@link WrapperFactoryInterface} and
     * the resulting array is returned.
     *
     * The {@link WrapperFactoryInterface} will only be used on a value
     * if the {@link TypeInterface::getAccessCondition() access condition} of the given
     * `$relationship` allows the access to the value. Otherwise, the value will be skipped
     * (not wrapped or returned).
     *
     * The entities will be sorted according to the definition of
     * {@link TypeInterface::getDefaultSortMethods()} of the relationship.
     *
     * @template TEntity of object
     *
     * @param TransferableTypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity> $relationshipType
     * @param iterable<TEntity>                                                                $values
     *
     * @return list<TEntity>
     *
     * @throws PathException
     * @throws SortException
     */
    public function determineToManyRelationshipValue(TransferableTypeInterface $relationshipType, iterable $values): array
    {
        $entities = $this->filter($relationshipType, Iterables::asArray($values));

        $sortMethods = $this->schemaPathProcessor->processDefaultSortMethods($relationshipType);
        if ([] !== $sortMethods) {
            $entities = $this->sorter->sortArray($entities, $sortMethods);
        }

        return $entities;
    }

    /**
     * @template TEntity of object
     *
     * @param TransferableTypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity> $relationship
     * @param array<int|string, TEntity>                                                       $entities
     *
     * @return list<TEntity>
     *
     * @throws PathException
     */
    private function filter(TypeInterface $relationship, array $entities): array
    {
        $condition = $this->schemaPathProcessor->processAccessCondition($relationship);

        // filter out restricted items
        $entities = $this->conditionEvaluator->filterArray($entities, $condition);

        return array_values($entities);
    }
}
