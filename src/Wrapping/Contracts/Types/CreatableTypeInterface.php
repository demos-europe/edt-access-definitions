<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\EntityBasedInterface;

/**
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 */
interface CreatableTypeInterface
{
    /**
     * The return defines what properties are allowed to be set when a new instance of
     * the {@link EntityBasedInterface::getEntityClass() backing class} is created.
     *
     * Each entry consists of the name of the property as key and conditions
     * as value. The conditions will be used to determine if a value is allowed to be set. (TODO: not implemented yet)
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
     * type. This enables to restrict createability based on the context (e.g.
     * authorizations), which would not be possible by
     * {@link CreatableTypeInterface::getInitializableProperties()} alone, as it may return an
     * empty array if a resource can be created without any properties required.
     */
    public function isCreatable(): bool;
}
