<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TRelationshipType of TransferableTypeInterface
 *
 * @template-extends AbstractUpdatability<TCondition>
 */
class AbstractRelationshipUpdatability extends AbstractUpdatability
{
    /**
     * @param list<TCondition> $entityConditions
     * @param list<TCondition> $valueConditions
     * @param TRelationshipType $relationshipType
     */
    public function __construct(
        array $entityConditions,
        array $valueConditions,
        private readonly TransferableTypeInterface $relationshipType
    ) {
        parent::__construct($entityConditions, $valueConditions);
    }

    /**
     * @return TRelationshipType
     */
    public function getRelationshipType(): TransferableTypeInterface
    {
        return $this->relationshipType;
    }
}
