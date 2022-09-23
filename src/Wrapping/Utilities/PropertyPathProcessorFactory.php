<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Wrapping\Utilities\TypeAccessors\AbstractTypeAccessor;

class PropertyPathProcessorFactory
{
    /**
     * @template T of \EDT\Wrapping\Contracts\Types\TypeInterface<\EDT\Querying\Contracts\PathsBasedInterface, \EDT\Querying\Contracts\PathsBasedInterface, object>
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
