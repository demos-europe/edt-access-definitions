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
    public function __construct(
        private readonly PropertyReader $propertyReader,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly ConditionEvaluator $conditionEvaluator
    ) {}

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
