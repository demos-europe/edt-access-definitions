<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToMany\PathToManyRelationshipSetBehavior;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractToOneRelationshipSetBehavior<TCondition, TSorting, TEntity, TRelationship>
 */
class CallbackToOneRelationshipSetBehavior extends AbstractToOneRelationshipSetBehavior
{
    /**
     * @param non-empty-string $propertyName
     * @param list<TCondition> $entityConditions
     * @param list<TCondition> $relationshipConditions
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship> $relationshipType
     * @param callable(TEntity, TRelationship|null): list<non-empty-string> $setterCallback
     */
    public function __construct(
        string $propertyName,
        array $entityConditions,
        array $relationshipConditions,
        protected readonly TransferableTypeInterface $relationshipType,
        protected readonly mixed $setterCallback,
        bool $optional
    ) {
        parent::__construct($propertyName, $entityConditions, $relationshipConditions, $optional);
    }

    public function getRelationshipType(): TransferableTypeInterface
    {
        return $this->relationshipType;
    }

    public function updateToOneRelationship(object $entity, ?object $relationship): array
    {
        return ($this->setterCallback)($entity, $relationship);
    }

    public function getDescription(): string
    {
        $relationshipType = $this->getRelationshipType()->getTypeName();

        return ($this->optional
                ? "Allows a to-one relationship `$this->propertyName` with the type `$relationshipType` to be present in the request body, but does not require it. "
                : "Requires a to-one relationship `$this->propertyName` with the type `$relationshipType` to be present in the request body.")
            . 'If the property is present in the request body it will be passed to a callback, which is able to adjust the target entity or execute side effects.'
            . ([] === $this->entityConditions
                ? 'The target entity does not need to '
                : 'The target entity must ')
            . 'match additional conditions beside the ones defined by its type.'
            . ([] === $this->relationshipConditions
                ? 'The relationships do not need to '
                : 'The relationships must ')
            . 'match additional conditions beside the ones defined by their type.';
    }
}
