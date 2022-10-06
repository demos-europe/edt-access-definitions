<?php

declare(strict_types=1);

namespace EDT\Wrapping\WrapperFactories;

use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\Contracts\SortMethodInterface;
use EDT\Querying\Utilities\ConditionEvaluator;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;
use EDT\Wrapping\Utilities\PropertyReader;
use EDT\Wrapping\Utilities\TypeAccessor;

/**
 * @template-implements WrapperFactoryInterface<FunctionInterface<bool>, SortMethodInterface>
 */
class WrapperObjectFactory implements WrapperFactoryInterface
{
    /**
     * @var TypeAccessor<FunctionInterface<bool>, SortMethodInterface>
     */
    private $typeAccessor;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var PropertyReader
     */
    private $propertyReader;

    /**
     * @var ConditionEvaluator
     */
    private $conditionEvaluator;

    /**
     * @param TypeAccessor<FunctionInterface<bool>, SortMethodInterface> $typeAccessor
     */
    public function __construct(
        TypeAccessor $typeAccessor,
        PropertyReader $propertyReader,
        PropertyAccessorInterface $propertyAccessor,
        ConditionEvaluator $conditionEvaluator
    ) {
        $this->typeAccessor = $typeAccessor;
        $this->propertyAccessor = $propertyAccessor;
        $this->propertyReader = $propertyReader;
        $this->conditionEvaluator = $conditionEvaluator;
    }

    /**
     * @template TEntity of object
     *
     * @param TEntity                                                                      $entity
     * @param ReadableTypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity> $type
     *
     * @return WrapperObject<TEntity>
     */
    public function createWrapper(object $entity, ReadableTypeInterface $type): WrapperObject
    {
        return new WrapperObject(
            $entity,
            $this->propertyReader,
            $type,
            $this->typeAccessor,
            $this->propertyAccessor,
            $this->conditionEvaluator,
            $this
        );
    }
}
