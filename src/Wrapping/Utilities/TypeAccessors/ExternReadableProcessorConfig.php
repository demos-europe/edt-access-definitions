<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities\TypeAccessors;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Properties\AbstractReadability;
use EDT\Wrapping\Properties\AbstractRelationshipReadability;

/**
 * @template-extends AbstractProcessorConfig<TransferableTypeInterface<PathsBasedInterface, PathsBasedInterface, object>>
 */
class ExternReadableProcessorConfig extends AbstractProcessorConfig
{
    /**
     * @param TypeProviderInterface<PathsBasedInterface, PathsBasedInterface>             $typeProvider
     * @param TransferableTypeInterface<PathsBasedInterface, PathsBasedInterface, object> $rootType
     */
    public function __construct(
        TypeProviderInterface $typeProvider,
        TransferableTypeInterface $rootType,
        private readonly bool $allowAttribute
    ) {
        parent::__construct($typeProvider, $rootType);
    }

    public function getProperties(TypeInterface $type): array
    {
        $readableProperties = $type->getReadableProperties();

        return $this->allowAttribute
            ? array_map(
                static fn (AbstractReadability $property): ?TypeInterface => $property instanceof AbstractRelationshipReadability
                    ? $property->getRelationshipType()
                    : null,
                array_merge(...$readableProperties)
            )
            : array_map(
                static fn (AbstractRelationshipReadability $property): TypeInterface => $property->getRelationshipType(),
                array_merge($readableProperties[1], $readableProperties[2])
            );
    }
}
