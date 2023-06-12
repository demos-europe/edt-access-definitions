<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\NamedTypeInterface;
use EDT\Wrapping\Contracts\Types\RelationshipFetchableTypeInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-extends RelationshipAccessibilityInterface<TCondition>
 * @template-extends RelationshipInterface<NamedTypeInterface&RelationshipFetchableTypeInterface<TCondition, TSorting, object>>
 */
interface ConstructorParameterInterface extends RelationshipAccessibilityInterface, RelationshipInterface
{
    public function isAttribute(): bool;
    public function isToManyRelationship(): bool;
    public function isToOneRelationship(): bool;
}
