<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToOne\Factory\CallbackToOneRelationshipSetBehaviorFactory;

/**
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractToOneRelationshipSetBehavior<TEntity, TRelationship>
 */
class CallbackToOneRelationshipSetBehavior extends AbstractToOneRelationshipSetBehavior
{
    /**
     * @param non-empty-string $propertyName
     * @param list<DrupalFilterInterface> $entityConditions
     * @param list<DrupalFilterInterface> $relationshipConditions
     * @param TransferableTypeInterface<TRelationship>|TransferableTypeProviderInterface<TRelationship> $relationshipType
     * @param callable(TEntity, TRelationship|null): list<non-empty-string> $setterCallback
     */
    public function __construct(
        string                                                      $propertyName,
        array                                                       $entityConditions,
        array                                                       $relationshipConditions,
        TransferableTypeInterface|TransferableTypeProviderInterface $relationshipType,
        protected readonly mixed                                    $setterCallback,
        OptionalField                                               $optional
    ) {
        parent::__construct($propertyName, $entityConditions, $relationshipConditions, $optional, $relationshipType);
    }

    /**
     * @template TEnt of object
     * @template TRel of object
     *
     * @param callable(TEnt, TRel|null): list<non-empty-string> $setBehaviorCallback
     * @param list<DrupalFilterInterface> $relationshipConditions
     * @param list<DrupalFilterInterface> $entityConditions
     *
     * @return RelationshipSetBehaviorFactoryInterface<TEnt, TRel>
     */
    public static function createFactory(
        mixed $setBehaviorCallback,
        array $relationshipConditions,
        OptionalField $optional,
        array $entityConditions
    ): RelationshipSetBehaviorFactoryInterface {
        return new CallbackToOneRelationshipSetBehaviorFactory($setBehaviorCallback, $relationshipConditions, $optional, $entityConditions);
    }

    public function updateToOneRelationship(object $entity, ?object $relationship): array
    {
        return ($this->setterCallback)($entity, $relationship);
    }

    public function getDescription(): string
    {
        $relationshipType = $this->getRelationshipType()->getTypeName();

        return ($this->optional->equals(OptionalField::YES)
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
