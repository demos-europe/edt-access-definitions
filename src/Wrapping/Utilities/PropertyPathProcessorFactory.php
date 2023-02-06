<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Utilities\TypeAccessors\AbstractProcessorConfig;

class PropertyPathProcessorFactory
{
    /**
     * @template TType of TypeInterface<PathsBasedInterface, PathsBasedInterface, object>
     *
     * @param AbstractProcessorConfig<TType> $processorConfig
     *
     * @return PropertyPathProcessor<TType>
     */
    public function createPropertyPathProcessor(AbstractProcessorConfig $processorConfig): PropertyPathProcessor
    {
        return new PropertyPathProcessor($processorConfig);
    }
}
