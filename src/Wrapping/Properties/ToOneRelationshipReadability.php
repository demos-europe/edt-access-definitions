<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractRelationshipReadability<TransferableTypeInterface<TCondition, TSorting, TRelationship>>
 */
class ToOneRelationshipReadability extends AbstractRelationshipReadability
{
    /**
     * @param null|callable(TEntity): (TRelationship|null) $customValueFunction
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship> $relationshipType
     */
    public function __construct(
        bool $defaultField,
        bool $allowingInconsistencies,
        bool $defaultInclude,
        private $customValueFunction,
        TransferableTypeInterface $relationshipType
    ) {
        parent::__construct($defaultField, $allowingInconsistencies, $defaultInclude, $relationshipType);
    }

    /**
     * @return null|callable(TEntity): (TRelationship|null)
     */
    public function getCustomValueFunction(): ?callable
    {
        return $this->customValueFunction;
    }
}
