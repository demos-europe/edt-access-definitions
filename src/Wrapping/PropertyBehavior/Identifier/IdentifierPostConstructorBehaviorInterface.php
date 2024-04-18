<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier;

use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\PropertyConstrainingInterface;
use EDT\Wrapping\PropertyBehavior\PropertySetBehaviorInterface;

/**
 * @template TEntity of object
 *
 * TODO: merge with {@link PropertySetBehaviorInterface}
 */
interface IdentifierPostConstructorBehaviorInterface extends PropertyConstrainingInterface
{
    /**
     * @param TEntity $entity
     *
     * @return list<non-empty-string>
     */
    public function setIdentifier(object $entity, CreationDataInterface $entityData): array;
}
