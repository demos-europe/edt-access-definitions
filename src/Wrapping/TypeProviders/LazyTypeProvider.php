<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-extends AbstractTypeProvider<TCondition, TSorting>
 */
class LazyTypeProvider extends AbstractTypeProvider
{
    /**
     * @var array<non-empty-string, TypeInterface<TCondition, TSorting, object>>
     */
    protected array $types = [];

    protected function getTypeByIdentifier(string $typeIdentifier): ?TypeInterface
    {
        return $this->types[$typeIdentifier] ?? null;
    }

    public function getTypeIdentifiers(): array
    {
        return array_keys($this->types);
    }

    /**
     * @param non-empty-string                            $identifier
     * @param TypeInterface<TCondition, TSorting, object> $type
     */
    public function setType(string $identifier, TypeInterface $type): void
    {
        $this->types[$identifier] = $type;
    }

    /**
     * @param TypeProviderInterface<TCondition, TSorting> $typeProvider
     */
    public function setAllTypes(TypeProviderInterface $typeProvider): void
    {
        foreach ($typeProvider->getTypeIdentifiers() as $typeIdentifier) {
            $this->types[$typeIdentifier] = $typeProvider->requestType($typeIdentifier)->getInstanceOrThrow();
        }
    }
}
