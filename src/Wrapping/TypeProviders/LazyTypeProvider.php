<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use InvalidArgumentException;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-implements TypeProviderInterface<TCondition, TSorting>
 */
class LazyTypeProvider implements TypeProviderInterface
{
    /**
     * @var array<non-empty-string, EntityBasedInterface<object>>
     */
    protected array $types = [];

    public function getTypeByIdentifier(string $typeIdentifier): ?EntityBasedInterface
    {
        return $this->types[$typeIdentifier] ?? null;
    }

    public function getTypeIdentifiers(): array
    {
        return array_keys($this->types);
    }

    /**
     * @param non-empty-string $identifier
     * @param EntityBasedInterface<object> $type
     */
    public function setType(string $identifier, EntityBasedInterface $type): void
    {
        $this->types[$identifier] = $type;
    }

    /**
     * @param TypeProviderInterface<TCondition, TSorting> $typeProvider
     */
    public function setAllTypes(TypeProviderInterface $typeProvider): void
    {
        foreach ($typeProvider->getTypeIdentifiers() as $typeIdentifier) {
            $this->types[$typeIdentifier] = $typeProvider->getTypeByIdentifier($typeIdentifier)
                ?? throw new InvalidArgumentException("No type instance found for identifier '$typeIdentifier'.");
        }
    }
}
