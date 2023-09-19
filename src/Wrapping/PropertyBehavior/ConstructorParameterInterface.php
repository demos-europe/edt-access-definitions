<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\Wrapping\CreationDataInterface;

interface ConstructorParameterInterface extends PropertyConstrainingInterface
{
    /**
     * Returns the parameter value for the corresponding constructor argument.
     *
     * Note that implementations must not have any side effects, but simply calculate
     * and return the argument value.
     */
    public function getArgument(CreationDataInterface $entityData): mixed;

    /**
     * @return non-empty-string The name of the constructor parameter, matching the actual argument name in the
     *                          constructors source code. It may be the same as an exposed property name, but should
     *                          not be confused with it.
     */
    public function getArgumentName(): string;
}
