<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Wrapping\Utilities\TypeAccessors\AbstractProcessorConfig;

class PropertyPathProcessorFactory
{
    /**
     * @template TType of \EDT\Wrapping\Contracts\Types\TypeInterface<\EDT\Querying\Contracts\PathsBasedInterface, \EDT\Querying\Contracts\PathsBasedInterface, object>
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
