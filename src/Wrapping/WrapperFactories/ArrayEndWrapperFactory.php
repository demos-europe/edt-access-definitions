<?php

declare(strict_types=1);

namespace EDT\Wrapping\WrapperFactories;

use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\SortMethodInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;

/**
 * @template-implements WrapperFactoryInterface<FunctionInterface<bool>, SortMethodInterface>
 * @internal
 */
class ArrayEndWrapperFactory implements WrapperFactoryInterface
{
    /**
     * @return null
     */
    public function createWrapper(object $entity, TransferableTypeInterface $type)
    {
        return null;
    }
}
