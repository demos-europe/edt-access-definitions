<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Wrapping\Contracts\Types\FilteringTypeInterface;

/**
 * @template-extends AbstractProcessorConfig<FilteringTypeInterface>
 */
class ExternFilterableProcessorConfig extends AbstractProcessorConfig
{
    /**
     * @param FilteringTypeInterface&EntityBasedInterface<object> $rootType
     */
    public function __construct(
        FilteringTypeInterface&EntityBasedInterface $rootType
    ) {
        parent::__construct($rootType);
    }

    public function getProperties(object $type): array
    {
        return $type->getFilteringProperties();
    }
}
