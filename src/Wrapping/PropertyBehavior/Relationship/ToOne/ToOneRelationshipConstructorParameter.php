<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\PropertyUpdaterTrait;
use EDT\Wrapping\PropertyBehavior\Relationship\AbstractRelationshipConstructorParameter;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-extends AbstractRelationshipConstructorParameter<TCondition, TSorting>
 */
class ToOneRelationshipConstructorParameter extends AbstractRelationshipConstructorParameter
{
    /**
     * @param non-empty-string $argumentName
     * @param non-empty-string $propertyName
     * @param TransferableTypeInterface<TCondition, TSorting, object> $relationshipType
     * @param list<TCondition> $relationshipConditions
     */
    public function __construct(
        string $argumentName,
        string $propertyName,
        TransferableTypeInterface $relationshipType,
        protected readonly array $relationshipConditions
    ) {
        parent::__construct($argumentName, $propertyName, $relationshipType);
    }

    use PropertyUpdaterTrait;

    public function getArgument(CreationDataInterface $entityData): ?object
    {
        $toOneRelationships = $entityData->getToOneRelationships();
        $relationshipRef = $toOneRelationships[$this->propertyName]
            ?? throw new \InvalidArgumentException("No to-one relationship '$this->propertyName' present.");

        return $this->determineToOneRelationshipValue(
            $this->getRelationshipType(),
            $this->relationshipConditions,
            $relationshipRef
        );
    }
    
    public function getRequiredToOneRelationships(): array
    {
        return [$this->propertyName => $this->relationshipType->getTypeName()];
    }
}
