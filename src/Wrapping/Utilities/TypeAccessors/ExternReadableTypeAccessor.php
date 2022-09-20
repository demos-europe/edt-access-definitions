<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template C of \EDT\Querying\Contracts\PathsBasedInterface
 * @template S of \EDT\Querying\Contracts\PathsBasedInterface
 *
 * @template-extends AbstractTypeAccessor<ReadableTypeInterface<C, S, object>>
 */
class ExternReadableTypeAccessor extends AbstractTypeAccessor
{
    /**
     * @var TypeProviderInterface<C, S>
     */
    private $typeProvider;

    /**
     * @var bool
     */
    private $allowAttribute;

    /**
     * @param TypeProviderInterface<C, S> $typeProvider
     */
    public function __construct(TypeProviderInterface $typeProvider, bool $allowAttribute)
    {
        $this->typeProvider = $typeProvider;
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
        return $this->typeProvider->getType($typeIdentifier, ReadableTypeInterface::class);
    }
}
