<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 *
 * @template-implements TypeProviderInterface<TCondition, TSorting>
 */
abstract class AbstractTypeProvider implements TypeProviderInterface
{
    public function requestType(string $typeIdentifier): TypeRequirement
    {
        $type = $this->getTypeByIdentifier($typeIdentifier);
        if (null === $type) {
            throw TypeRetrievalAccessException::unknownTypeIdentifier($typeIdentifier, $this->getTypeIdentifiers());
        }

        return new TypeRequirement($type, $typeIdentifier);
    }

    /**
     * @throws TypeRetrievalAccessException
     */
    public function isTypeAvailable(string $typeIdentifier, string ...$implementations): bool
    {
        $type = $this->getTypeByIdentifier($typeIdentifier);
        if (null === $type) {
            return false;
        }

        foreach ($implementations as $implementation) {
            if (!is_a($type, $implementation)) {
                return false;
            }
        }

        return $type->isAvailable();
    }

    /**
     * @return TypeInterface<TCondition, TSorting, object>|null
     *
     * @throws TypeRetrievalAccessException
     */
    abstract protected function getTypeByIdentifier(string $typeIdentifier): ?TypeInterface;
}
