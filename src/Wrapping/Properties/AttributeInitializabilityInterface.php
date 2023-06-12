<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends AttributeSetabilityInterface<TCondition, TEntity>
 */
interface AttributeInitializabilityInterface extends AttributeSetabilityInterface, PropertyInitializabilityInterface
{
}
