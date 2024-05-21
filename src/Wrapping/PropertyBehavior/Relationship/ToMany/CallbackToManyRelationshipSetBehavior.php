<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToMany\Factory\CallbackToManyRelationshipSetBehaviorFactory;

/**
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractToManyRelationshipSetBehavior<TEntity, TRelationship>
 */
class CallbackToManyRelationshipSetBehavior extends AbstractToManyRelationshipSetBehavior
{
    /**
     * @param non-empty-string $propertyName the exposed property name accepted by this instance
     * @param list<DrupalFilterInterface> $entityConditions
     * @param list<DrupalFilterInterface> $relationshipConditions
     * @param TransferableTypeInterface<TRelationship>|TransferableTypeProviderInterface<TRelationship> $relationshipType
     * @param callable(TEntity, list<TRelationship>): list<non-empty-string> $setterCallback
     */
    public function __construct(
        string                                                      $propertyName,
        array                                                       $entityConditions,
        array                                                       $relationshipConditions,
        TransferableTypeInterface|TransferableTypeProviderInterface $relationshipType,
        protected readonly mixed                                    $setterCallback,
        OptionalField                                               $optional = OptionalField::NO
    ) {
        parent::__construct($propertyName, $entityConditions, $relationshipConditions, $optional, $relationshipType);
    }

    /**
     * @template TEnt of object
     * @template TRel of object
     *
     * @param callable(TEnt, list<TRel>): list<non-empty-string> $setBehaviorCallback
     * @param list<DrupalFilterInterface> $relationshipConditions
     * @param list<DrupalFilterInterface> $entityConditions
     *
     * @return RelationshipSetBehaviorFactoryInterface<TEnt, TRel>
     */
    public static function createFactory(
        callable $setBehaviorCallback,
        array $relationshipConditions,
        OptionalField $optional,
        array $entityConditions
    ): RelationshipSetBehaviorFactoryInterface {
        return new CallbackToManyRelationshipSetBehaviorFactory($setBehaviorCallback, $relationshipConditions, $optional, $entityConditions);
    }

    public function updateToManyRelationship(object $entity, array $relationships): array
    {
        return ($this->setterCallback)($entity, $relationships);
    }

    public function getDescription(): string
    {
        $relationshipType = $this->getRelationshipType()->getTypeName();

        return ($this->optional->equals(OptionalField::YES)
                ? "Allows a to-many relationship `$this->propertyName` with the type `$relationshipType` to be present in the request body, but does not require it. "
                : "Requires a to-many relationship `$this->propertyName` with the type `$relationshipType` to be present in the request body.")
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
