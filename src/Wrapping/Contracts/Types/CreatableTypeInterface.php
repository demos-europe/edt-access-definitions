<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Properties\Initializability;

/**
 * @template TCondition of PathsBasedInterface
 */
interface CreatableTypeInterface
{
    /**
     * The return defines what properties are allowed to be set when a new instance of
     * the {@link EntityBasedInterface::getEntityClass() backing class} is created.
     *
     * Each entry consists of the name of the property as key and config data as value.
     *
     * @return array<non-empty-string, Initializability<TCondition>>
     */
    public function getInitializableProperties(): array;

    /**
     * Controls if the implementing instance can be used to create resources of the corresponding
     * type. This enables to restrict createability based on the context (e.g.
     * authorizations), which would not be possible by
     * {@link CreatableTypeInterface::getInitializableProperties()} alone, as it may return an
     * empty array if a resource can be created without any properties required.
     */
    public function isCreatable(): bool;
}
