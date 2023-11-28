<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\PropertyUpdaterTrait;
use EDT\Wrapping\PropertyBehavior\Relationship\AbstractRelationshipConstructorBehavior;
use InvalidArgumentException;
use function array_key_exists;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-extends AbstractRelationshipConstructorBehavior<TCondition, TSorting>
 */
class ToOneRelationshipConstructorBehavior extends AbstractRelationshipConstructorBehavior
{
    use PropertyUpdaterTrait;

    /**
     * @template TRelationship of object
     *
     * @param non-empty-string $argumentName
     * @param non-empty-string $propertyName
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship> $relationshipType
     * @param list<TCondition> $relationshipConditions
     * @param null|callable(CreationDataInterface): array{TRelationship|null, list<non-empty-string>} $fallback
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

    public function getArguments(CreationDataInterface $entityData): array
    {
        $toOneRelationships = $entityData->getToOneRelationships();
        if (array_key_exists($this->propertyName, $toOneRelationships)) {
            $relationshipValue = $this->determineToOneRelationshipValue(
                $this->getRelationshipType(),
                $this->relationshipConditions,
                $toOneRelationships[$this->propertyName]
            );
            $propertyDeviations = [];
        } elseif (null !== $this->fallback) {
            [$relationshipValue, $propertyDeviations] = ($this->fallback)($entityData);
        } else {
            throw new InvalidArgumentException("No to-one relationship '$this->propertyName' present and no fallback set.");
        }

        return [$this->argumentName => [$relationshipValue, $propertyDeviations]];
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
