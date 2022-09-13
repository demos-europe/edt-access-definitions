<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

/**
 * Defines the property {@link TypeInterface::getEntityClass() corresponding entities}
 * can be distinguished by.
 *
 * @template C of \EDT\Querying\Contracts\PathsBasedInterface
 * @template S of \EDT\Querying\Contracts\PathsBasedInterface
 * @template T of object
 *
 * @template-extends TypeInterface<C, S, T>
 */
interface IdentifiableTypeInterface extends TypeInterface
{
    /**
     * The path to the property (in the schema of the Type) that uniquely identifies an instance of the
     * {@link TypeInterface::getEntityClass() backing entity}.
     *
     * @return non-empty-list<non-empty-string>
     */
    public function getIdentifierPropertyPath(): array;
}
