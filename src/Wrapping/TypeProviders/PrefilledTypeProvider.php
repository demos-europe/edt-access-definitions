<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Wrapping\Contracts\Types\TypeInterface;
use InvalidArgumentException;
use function get_class;
use function array_key_exists;
use const ARRAY_FILTER_USE_KEY;

/**
 * Takes something iterable containing {@link TypeInterface}s on initialization
 * and will assign each item an identifier using the {@link PrefilledTypeProvider::getIdentifier()}
 * method. By default, the fully qualified class name is chosen as identifier. To use something different
 * override {@link PrefilledTypeProvider::getIdentifier()}.
 *
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 *
 * @template-extends AbstractTypeProvider<TCondition, TSorting>
 */
class PrefilledTypeProvider extends AbstractTypeProvider
{
    /**
     * @var array<non-empty-string, TypeInterface<TCondition, TSorting, object>>
     */
    protected $typesByIdentifier = [];

    /**
     * @param iterable<TypeInterface<TCondition, TSorting, object>> $types The types this instance is able to provide.
     *
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
                throw new InvalidArgumentException("Duplicated type identifiers detected: '$typeClassA' and '$typeClassB' as '$typeIdentifier'.");
            }
            $this->typesByIdentifier[$typeIdentifier] = $type;
        }
    }

    /**
     * @return array<non-empty-string, TypeInterface<TCondition, TSorting, object>>
     */
    public function getAllAvailableTypes(): array
    {
        return array_filter(
            $this->typesByIdentifier,
            [$this, 'isTypeAvailable'],
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Returns the identifier to use for the given type. Defaults to its fully qualified class name if not overridden.
     * @return non-empty-string
     */
    protected function getIdentifier(TypeInterface $type): string
    {
        return get_class($type);
    }

    protected function getTypeByIdentifier(string $typeIdentifier): ?TypeInterface
    {
        return $this->typesByIdentifier[$typeIdentifier] ?? null;
    }

    public function getTypeIdentifiers(): array
    {
        return array_keys($this->typesByIdentifier);
    }
}
