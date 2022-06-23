<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

/**
 * Defines the property {@link TypeInterface::getEntityClass() corresponding entities}
 * can be distinguished by.
 *
 * @template T of object
 *
 * @template-extends TypeInterface<T>
 */
interface IdentifiableTypeInterface extends TypeInterface
{
    /**
     * The path to the property (in the schema of the Type) that uniquely identifies an instance of the
     * {@link TypeInterface::getEntityClass() backing entity}.
     *
     * @return array<int,string>
     */
    public function getIdentifierPropertyPath(): array;
}
