<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\TypeInterface;

abstract class AbstractTypeProvider implements TypeProviderInterface
{
    public function getAvailableType(string $typeName, string ...$implementations): TypeInterface
    {
        $type = $this->getType($typeName, ...$implementations);
        if (!$type->isAvailable()) {
            throw TypeRetrievalAccessException::typeExistsButNotAvailable($typeName);
        }
        return $type;
    }

    public function getType(string $typeIdentifier, string ...$implementations): TypeInterface
    {
        $type = $this->getTypeByIdentifier($typeIdentifier);
        if (null === $type) {
            throw TypeRetrievalAccessException::unknownTypeIdentifier($typeIdentifier, $this->getTypeIdentifiers());
        }

        foreach ($implementations as $implementation) {
            if (!is_a($type, $implementation)) {
                throw TypeRetrievalAccessException::noNameWithImplementation($typeIdentifier, $implementation);
            }
        }

        return $type;
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
     * @throws TypeRetrievalAccessException
     */
    abstract protected function getTypeByIdentifier(string $typeIdentifier): ?TypeInterface;

    /**
     * @return array<int, string>
     */
    abstract protected function getTypeIdentifiers(): array;
}
