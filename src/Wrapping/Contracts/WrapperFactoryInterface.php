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
 * @template O of object
 * @template R
 */
interface WrapperFactoryInterface
{
    /**
     * @param O $object
     * @param ReadableTypeInterface<O> $type
     * @return R
     * @throws AccessException
     */
    public function createWrapper(object $object, ReadableTypeInterface $type);
}
