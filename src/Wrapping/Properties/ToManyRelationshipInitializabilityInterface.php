<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends ToManyRelationshipSetabilityInterface<TCondition, TSorting, TEntity, TRelationship>
 */
interface ToManyRelationshipInitializabilityInterface extends ToManyRelationshipSetabilityInterface, PropertyInitializabilityInterface
{
}
