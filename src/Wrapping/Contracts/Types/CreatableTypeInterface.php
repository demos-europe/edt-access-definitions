<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\FunctionInterface;

/**
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends TypeInterface<TCondition, TSorting, TEntity>
 */
interface CreatableTypeInterface extends TypeInterface
{
    /**
     * The return defines what properties are allowed to be set when a new instance of
     * the {@link TypeInterface::getEntityClass() backing class} is created.
     *
     * Each entry consists of the name of the property as key and {@link FunctionInterface conditions}
     * as value. The conditions will be used to determine if a value is allowed to be set.
     *
     * @return array<non-empty-string, list<TCondition>>
     */
    public function getInitializableProperties(): array;

    /**
     * @return list<non-empty-string>
     */
    public function getPropertiesRequiredForCreation(): array;

    /**
     * Controls if the implementing instance can be used to create resources of the corresponding
     * type. This not only enables to restrict createability based on the context (e.g.
     * authorizations) but also allows special cases in which a resource can be created without any
     * properties returned by {@link CreatableTypeInterface::getInitializableProperties()}.
     */
    public function isCreatable(): bool;
}
