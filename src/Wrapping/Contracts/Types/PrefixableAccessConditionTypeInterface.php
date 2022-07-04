<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\PropertyPathInterface;

/**
 * Intended for usage with a {@link TypeInterface} to allow to retrieve the condition
 * returned by {@link TypeInterface::getAccessCondition()} with a given path  Allows to
 */
interface PrefixableAccessConditionTypeInterface
{
    /**
     * @return FunctionInterface<bool>
     */
    public function getPrefixedAccessCondition(PropertyPathInterface $prefix): FunctionInterface;
}
