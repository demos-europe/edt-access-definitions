<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

/**
 * Defines the property {@link TypeInterface::getEntityClass() corresponding entities}
 * can be distinguished by.
 *
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends TypeInterface<TCondition, TSorting, TEntity>
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
