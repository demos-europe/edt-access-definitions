<?php

declare(strict_types=1);

namespace EDT\Wrapping\WrapperFactories;

use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\Utilities\ConditionEvaluator;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;
use EDT\Wrapping\Utilities\PropertyReader;
use EDT\Wrapping\Utilities\TypeAccessor;

/**
 * @template O of object
 *
 * @template-implements WrapperFactoryInterface<O,WrapperObject<O>>
 */
class WrapperObjectFactory implements WrapperFactoryInterface
{
    /**
     * @var TypeAccessor
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

    public function createWrapper(object $object, ReadableTypeInterface $type): WrapperObject
    {
        return new WrapperObject(
            $object,
            $this->propertyReader,
            $type, $this->typeAccessor,
            $this->propertyAccessor,
            $this->conditionEvaluator,
            $this
        );
    }
}
