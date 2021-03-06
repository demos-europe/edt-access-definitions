<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Wrapping\Contracts\Types\TypeInterface;
use InvalidArgumentException;
use function get_class;
use function array_key_exists;
use function is_a;

/**
 * Takes something iterable containing {@link TypeInterface}s on initialization
 * and will assign each item an identifier using the {@link PrefilledTypeProvider::getIdentifier()}
 * method. By default the fully qualified class name is chosen as identifier. To use something different
 * override {@link PrefilledTypeProvider::getIdentifier()}.
 */
class PrefilledTypeProvider extends AbstractTypeProvider
{
    /**
     * @var array<string,TypeInterface>
     */
    protected $typesByIdentifier = [];

    /**
     * @param iterable<TypeInterface<object>> $types The types this instance is able to provide.
     * @throws InvalidArgumentException Thrown if the given array contains duplicates. Types are considered duplicates
     * if {@link PrefilledTypeProvider::getIdentifier their} return the same result for two given types.
     */
    public function __construct(iterable $types)
    {
        foreach ($types as $type) {
            $typeIdentifier = $this->getIdentifier($type);
            if (array_key_exists($typeIdentifier, $this->typesByIdentifier)) {
                $typeClassA = get_class($this->typesByIdentifier[$typeIdentifier]);
                $typeClassB = get_class($type);
                throw new InvalidArgumentException("Duplicated type identifiers detected: '$typeClassA' and '$typeClassB' as $typeIdentifier");
            }
            $this->typesByIdentifier[$typeIdentifier] = $type;
        }
    }

    /**
     * Returns the identifier to use for the given type. Defaults to its fully qualified class name if not overridden.
     * @return class-string<TypeInterface<object>>
     */
    protected function getIdentifier(TypeInterface $type): string
    {
        return get_class($type);
    }

    protected function getTypeByIdentifier(string $typeIdentifier): ?TypeInterface
    {
        return $this->typesByIdentifier[$typeIdentifier] ?? null;
    }

    protected function getTypeIdentifiers(): array
    {
        return array_keys($this->typesByIdentifier);
    }
}
