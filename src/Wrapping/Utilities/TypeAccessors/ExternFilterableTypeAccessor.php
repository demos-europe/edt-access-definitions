<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\FilterableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template-extends AbstractTypeAccessor<FilterableTypeInterface>
 */
class ExternFilterableTypeAccessor extends AbstractTypeAccessor
{
    /**
     * @var TypeProviderInterface
     */
    private $typeProvider;

    public function __construct(TypeProviderInterface $typeProvider)
    {
        $this->typeProvider = $typeProvider;
    }

    protected function getProperties(TypeInterface $type): array
    {
        return $type->getFilterableProperties();
    }

    protected function getType(string $typeIdentifier): TypeInterface
    {
        return $this->typeProvider->getAvailableType($typeIdentifier, FilterableTypeInterface::class);
    }
}
