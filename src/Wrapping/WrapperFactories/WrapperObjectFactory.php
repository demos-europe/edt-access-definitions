<?php

declare(strict_types=1);

namespace EDT\Wrapping\WrapperFactories;

use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\Contracts\SortMethodInterface;
use EDT\Querying\Utilities\ConditionEvaluator;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;
use EDT\Wrapping\Utilities\PropertyReader;

/**
 * @template-implements WrapperFactoryInterface<FunctionInterface<bool>, SortMethodInterface>
 */
class WrapperObjectFactory implements WrapperFactoryInterface
{
    private PropertyAccessorInterface $propertyAccessor;

    private PropertyReader $propertyReader;

    private ConditionEvaluator $conditionEvaluator;

    public function __construct(
        PropertyReader $propertyReader,
        PropertyAccessorInterface $propertyAccessor,
        ConditionEvaluator $conditionEvaluator
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->propertyReader = $propertyReader;
        $this->conditionEvaluator = $conditionEvaluator;
    }

    /**
     * @template TEntity of object
     *
     * @param TEntity                                                                          $entity
     * @param TransferableTypeInterface<FunctionInterface<bool>, SortMethodInterface, TEntity> $type
     *
     * @return WrapperObject<TEntity>
     */
    public function createWrapper(object $entity, TransferableTypeInterface $type): WrapperObject
    {
        return new WrapperObject(
            $entity,
            $this->propertyReader,
            $type,
            $this->propertyAccessor,
            $this->conditionEvaluator,
            $this
        );
    }
}
