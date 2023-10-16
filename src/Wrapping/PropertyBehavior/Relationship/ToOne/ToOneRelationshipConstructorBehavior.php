<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\PropertyUpdaterTrait;
use EDT\Wrapping\PropertyBehavior\Relationship\AbstractRelationshipConstructorBehavior;
use function array_key_exists;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-extends AbstractRelationshipConstructorBehavior<TCondition, TSorting>
 */
class ToOneRelationshipConstructorBehavior extends AbstractRelationshipConstructorBehavior
{
    /**
     * @param non-empty-string $argumentName
     * @param non-empty-string $propertyName
     * @param TransferableTypeInterface<TCondition, TSorting, object> $relationshipType
     * @param list<TCondition> $relationshipConditions
     * @param null|callable(CreationDataInterface): (TransferableTypeInterface<TCondition, TSorting, object>|null) $fallback
     */
    public function __construct(
        string $argumentName,
        string $propertyName,
        TransferableTypeInterface $relationshipType,
        protected readonly array $relationshipConditions,
        protected readonly mixed $fallback
    ) {
        parent::__construct($argumentName, $propertyName, $relationshipType);
    }

    use PropertyUpdaterTrait;

    /**
     * @param CreationDataInterface $entityData
     * @return array<non-empty-string, object|null>
     */
    public function getArguments(CreationDataInterface $entityData): array
    {
        $toOneRelationships = $entityData->getToOneRelationships();
        if (array_key_exists($this->propertyName, $toOneRelationships)) {
            $relationshipValue = $this->determineToOneRelationshipValue(
                $this->getRelationshipType(),
                $this->relationshipConditions,
                $toOneRelationships[$this->propertyName]
            );
        } elseif (null !== $this->fallback) {
            $relationshipValue = ($this->fallback)($entityData);
        } else {
            throw new \InvalidArgumentException("No to-one relationship '$this->propertyName' present and no fallback set.");
        }

        return [
            $this->argumentName => $relationshipValue,
        ];
    }
    
    public function getRequiredToOneRelationships(): array
    {
        if (null === $this->fallback) {
            return [$this->propertyName => $this->relationshipType->getTypeName()];
        }

        return [];
    }

    public function getOptionalToOneRelationships(): array
    {
        if (null === $this->fallback) {
            return [];
        }

        return [$this->propertyName => $this->relationshipType->getTypeName()];
    }
}
