<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\SortableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template-extends AbstractProcessorConfig<SortableTypeInterface<PathsBasedInterface, PathsBasedInterface, object>>
 */
class ExternSortableProcessorConfig extends AbstractProcessorConfig
{
    public function getProperties(TypeInterface $type): array
    {
        return $type->getSortableProperties();
    }

    public function getRelationshipType(string $typeIdentifier): TypeInterface
    {
        return $this->typeProvider->requestType($typeIdentifier)
            ->instanceOf(SortableTypeInterface::class)
            ->exposedAsRelationship()
            ->getInstanceOrThrow();
    }
}
