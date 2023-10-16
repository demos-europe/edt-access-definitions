<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\Wrapping\CreationDataInterface;

interface ConstructorBehaviorInterface extends PropertyConstrainingInterface
{
    /**
     * Returns the parameter value for the corresponding constructor argument.
     *
     * Keys in the returned array must match a name of the constructor arguments.
     * It may be the same as an exposed resource property name, but should not be
     * confused with it.
     *
     * The values in the returned array will be directly passed as constructor argument
     * that correspond to the matching array key (i.e. the argument name).
     *
     * @return array<non-empty-string, mixed>
     */
    public function getArguments(CreationDataInterface $entityData): array;
}
