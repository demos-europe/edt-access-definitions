<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use InvalidArgumentException;
use function array_key_exists;

/**
 * Takes something iterable containing {@link EntityBasedInterface}s on initialization
 * and will assign each item an identifier using the {@link PrefilledTypeProvider::getIdentifier()}
 * method. By default, the fully qualified class name is chosen as identifier. To use something different
 * override {@link PrefilledTypeProvider::getIdentifier()}.
 */
class PrefilledTypeProvider implements TypeProviderInterface
{
    /**
     * @var array<non-empty-string, EntityBasedInterface<object>>
     */
    protected array $typesByIdentifier = [];

    /**
     * @param iterable<EntityBasedInterface<object>> $types The types this instance is able to provide.
     *
     * @throws InvalidArgumentException Thrown if the given array contains duplicates. Types are considered duplicates
     * if {@link PrefilledTypeProvider::getIdentifier their} return the same result for two given types.
     */
    public function __construct(iterable $types)
    {
        foreach ($types as $type) {
            $typeIdentifier = $this->getIdentifier($type);
            if (array_key_exists($typeIdentifier, $this->typesByIdentifier)) {
                $typeClassA = $this->typesByIdentifier[$typeIdentifier]::class;
                $typeClassB = $type::class;
                throw new InvalidArgumentException("Duplicated type identifiers detected: '$typeClassA' and '$typeClassB' as '$typeIdentifier'.");
            }
            $this->typesByIdentifier[$typeIdentifier] = $type;
        }
    }

    /**
     * Returns the identifier to use for the given type. Defaults to its fully qualified class name if not overridden.
     *
     * @param EntityBasedInterface<object> $type
     *
     * @return non-empty-string
     */
    protected function getIdentifier(EntityBasedInterface $type): string
    {
        return $type::class;
    }

    public function getTypeByIdentifier(string $typeIdentifier): ?EntityBasedInterface
    {
        return $this->typesByIdentifier[$typeIdentifier] ?? null;
    }

    public function getTypeIdentifiers(): array
    {
        return array_keys($this->typesByIdentifier);
    }
}
