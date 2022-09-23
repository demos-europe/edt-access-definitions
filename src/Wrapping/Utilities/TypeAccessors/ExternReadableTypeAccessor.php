<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template-extends AbstractTypeAccessor<ReadableTypeInterface<PathsBasedInterface, PathsBasedInterface, object>>
 */
class ExternReadableTypeAccessor extends AbstractTypeAccessor
{
    /**
     * @var bool
     */
    private $allowAttribute;

    /**
     * @param TypeProviderInterface<PathsBasedInterface, PathsBasedInterface> $typeProvider
     */
    public function __construct(TypeProviderInterface $typeProvider, bool $allowAttribute)
    {
        parent::__construct($typeProvider);
        $this->allowAttribute = $allowAttribute;
    }

    public function getProperties(TypeInterface $type): array
    {
        $properties = $type->getReadableProperties();
        if (!$this->allowAttribute) {
            $properties = array_filter(
                $properties,
                static function (?string $property): bool {
                    return null !== $property;
                }
            );
        }

        return $properties;
    }

    public function getType(string $typeIdentifier): TypeInterface
    {
        return $this->typeProvider->requestType($typeIdentifier)
            ->instanceOf(ReadableTypeInterface::class)
            ->getTypeInstance();
    }
}
