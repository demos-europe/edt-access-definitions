<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Wrapping\Contracts\Types\SortingTypeInterface;

/**
 * @template-extends AbstractProcessorConfig<SortingTypeInterface>
 */
class ExternSortableProcessorConfig extends AbstractProcessorConfig
{
    public function getProperties(object $type): array
    {
        return $type->getSortingProperties();
    }
}
