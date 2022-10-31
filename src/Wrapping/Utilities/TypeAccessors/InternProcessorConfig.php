<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template-extends AbstractProcessorConfig<TypeInterface<PathsBasedInterface, PathsBasedInterface, object>>
 */
class InternProcessorConfig extends AbstractProcessorConfig
{
    public function getProperties(TypeInterface $type): array
    {
        return $type->getInternalProperties();
    }

    public function getRelationshipType(string $typeIdentifier): TypeInterface
    {
        return $this->typeProvider->requestType($typeIdentifier)->getTypeInstance();
    }
}
