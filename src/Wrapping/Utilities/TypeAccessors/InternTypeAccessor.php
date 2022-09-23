<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template-extends AbstractTypeAccessor<TypeInterface<PathsBasedInterface, PathsBasedInterface, object>>
 */
class InternTypeAccessor extends AbstractTypeAccessor
{
    public function getProperties(TypeInterface $type): array
    {
        return $type->getInternalProperties();
    }

    public function getType(string $typeIdentifier): TypeInterface
    {
        return $this->typeProvider->requestType($typeIdentifier)->getTypeInstance();
    }
}
