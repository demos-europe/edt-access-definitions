<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany;

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
class ToManyRelationshipConstructorBehavior extends AbstractRelationshipConstructorBehavior
{
    use PropertyUpdaterTrait;

    /**
     * @template TRelationship of object
     *
     * @param non-empty-string $argumentName
     * @param non-empty-string $propertyName
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship> $relationshipType
     * @param list<TCondition> $relationshipConditions
     * @param null|callable(CreationDataInterface): array{list<TRelationship>, list<non-empty-string>} $fallback
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
        $toManyRelationships = $entityData->getToManyRelationships();
        if (array_key_exists($this->propertyName, $toManyRelationships)) {
            $relationshipValues = $this->determineToManyRelationshipValues(
                $this->getRelationshipType(),
                $this->relationshipConditions,
                $toManyRelationships[$this->propertyName]
            );
            $propertyDeviations = [];
        } elseif (null !== $this->fallback) {
            [$relationshipValues, $propertyDeviations] = ($this->fallback)($entityData);
        } else {
            throw new InvalidArgumentException("No to-many relationship '$this->propertyName' present and no fallback set.");
        }

        return [$this->argumentName => [$relationshipValues, $propertyDeviations]];
    }

    public function getRequiredToManyRelationships(): array
    {
        if (null === $this->fallback) {
            return [$this->propertyName => $this->relationshipType->getTypeName()];
        }

        return [];
    }

    public function getOptionalToManyRelationships(): array
    {
        if (null === $this->fallback) {
            return [];
        }

        return [$this->propertyName => $this->relationshipType->getTypeName()];
    }
}
