<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template-extends AbstractProcessorConfig<ReadableTypeInterface<PathsBasedInterface, PathsBasedInterface, object>>
 */
class ExternReadableProcessorConfig extends AbstractProcessorConfig
{
    private bool $allowAttribute;

    /**
     * @param TypeProviderInterface<PathsBasedInterface, PathsBasedInterface>         $typeProvider
     * @param ReadableTypeInterface<PathsBasedInterface, PathsBasedInterface, object> $rootType
     */
    public function __construct(TypeProviderInterface $typeProvider, ReadableTypeInterface $rootType, bool $allowAttribute)
    {
        parent::__construct($typeProvider, $rootType);
        $this->allowAttribute = $allowAttribute;
    }

    public function getProperties(TypeInterface $type): array
    {
        $properties = $type->getReadableProperties();
        if (!$this->allowAttribute) {
            $properties = array_filter(
                $properties,
                static fn (?string $property): bool => null !== $property
            );
        }

        return $properties;
    }

    public function getRelationshipType(string $typeIdentifier): TypeInterface
    {
        return $this->typeProvider->requestType($typeIdentifier)
            ->instanceOf(ReadableTypeInterface::class)
            ->getTypeInstance();
    }
}
