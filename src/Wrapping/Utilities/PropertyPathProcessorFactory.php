<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Wrapping\Utilities\TypeAccessors\AbstractTypeAccessor;

class PropertyPathProcessorFactory
{
    /**
     * @template T of \EDT\Wrapping\Contracts\Types\TypeInterface
     *
     * @param AbstractTypeAccessor<T> $typeAccessor
     *
     * @return PropertyPathProcessor<T>
     */
    public function createPropertyPathProcessor(AbstractTypeAccessor $typeAccessor): PropertyPathProcessor
    {
        return new PropertyPathProcessor($typeAccessor);
    }
}
