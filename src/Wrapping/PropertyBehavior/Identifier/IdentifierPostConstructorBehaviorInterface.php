<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier;

use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\PropertyConstrainingInterface;

/**
 * @template TEntity of object
 */
interface IdentifierPostConstructorBehaviorInterface extends PropertyConstrainingInterface
{
    /**
     * @param TEntity $entity
     */
    public function setIdentifier(object $entity, CreationDataInterface $entityData): bool;
}
