<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * Creates a wrapper around an instance of a {@link TypeInterface::getEntityClass() backing object}.
 * Implementations can be tailored for a specific object class (specified via the template parameter `O`)
 * or be more generic and create wrappers suitable for different kinds of objects.
 *
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 *
 * TODO: check if usages can be replaced with {@link WrapperObjectFactory} and remove interface
 */
interface WrapperFactoryInterface
{
    /**
     * @template TEntity of object
     *
     * @param TEntity                              $entity
     * @param ReadableTypeInterface<TCondition, TSorting, TEntity> $type
     *
     * @return array<non-empty-string, mixed>|object|null
     *
     * @throws AccessException
     */
    public function createWrapper(object $entity, ReadableTypeInterface $type);
}
