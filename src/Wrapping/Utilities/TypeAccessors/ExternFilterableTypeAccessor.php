<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\FilterableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template-extends AbstractTypeAccessor<FilterableTypeInterface<PathsBasedInterface, PathsBasedInterface, object>>
 */
class ExternFilterableTypeAccessor extends AbstractTypeAccessor
{
    public function getProperties(TypeInterface $type): array
    {
        return $type->getFilterableProperties();
    }

    public function getType(string $typeIdentifier): TypeInterface
    {
        return $this->typeProvider->requestType($typeIdentifier)
            ->instanceOf(FilterableTypeInterface::class)
            ->available(true)
            ->getTypeInstance();
    }
}
