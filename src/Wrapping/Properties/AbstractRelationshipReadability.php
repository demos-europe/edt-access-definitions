<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;

/**
 * @template TRelationshipType of TransferableTypeInterface
 */
abstract class AbstractRelationshipReadability extends AbstractReadability
{
    /**
     * @param TRelationshipType $relationshipType
     */
    public function __construct(
        bool $defaultField,
        bool $allowingInconsistencies,
        private readonly bool $defaultInclude,
        private readonly TransferableTypeInterface $relationshipType
    ) {
        parent::__construct($defaultField, $allowingInconsistencies);
    }

    public function isDefaultInclude(): bool
    {
        return $this->defaultInclude;
    }

    /**
     * @return TRelationshipType
     */
    public function getRelationshipType(): TransferableTypeInterface
    {
        return $this->relationshipType;
    }
}
