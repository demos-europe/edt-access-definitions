<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship;

use EDT\Wrapping\Contracts\RelationshipInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\PropertyReadabilityInterface;

/**
 * Provides readability information and behavior for a relationship (i.e. a non-attribute) property.
 *
 * @template TRelationshipType of TransferableTypeInterface
 *
 * @template-extends RelationshipInterface<TRelationshipType>
 */
interface RelationshipReadabilityInterface extends PropertyReadabilityInterface, RelationshipInterface
{
    /**
     * @see https://jsonapi.org/format/#document-compound-documents
     * @see https://jsonapi.org/format/#fetching-includes
     */
    public function isDefaultInclude(): bool;
}
