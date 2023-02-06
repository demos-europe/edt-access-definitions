<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-implements TypeProviderInterface<TCondition, TSorting>
 */
abstract class AbstractTypeProvider implements TypeProviderInterface
{
    public function requestType(string $typeIdentifier): TypeRequirement
    {
        $type = $this->getTypeByIdentifier($typeIdentifier);

        $problems = null === $type
            ? ["identifier '$typeIdentifier' not known"]
            : [];

        return new TypeRequirement($type, $type, $typeIdentifier, $problems);
    }

    /**
     * @return TypeInterface<TCondition, TSorting, object>|null
     *
     * @throws TypeRetrievalAccessException
     */
    abstract protected function getTypeByIdentifier(string $typeIdentifier): ?TypeInterface;
}
