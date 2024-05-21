<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany\Factory;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToMany\CallbackToManyRelationshipSetBehavior;

/**
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-implements RelationshipSetBehaviorFactoryInterface<TEntity, TRelationship>
 */
class CallbackToManyRelationshipSetBehaviorFactory implements RelationshipSetBehaviorFactoryInterface
{
    /**
     * @var callable(TEntity, list<TRelationship>): list<non-empty-string>
     */
    private $setBehaviorCallback;

    /**
     * @param callable(TEntity, list<TRelationship>): list<non-empty-string> $setBehaviorCallback
     * @param list<DrupalFilterInterface> $relationshipConditions
     * @param list<DrupalFilterInterface> $entityConditions
     */
    public function __construct(
        callable $setBehaviorCallback,
        protected readonly array $relationshipConditions,
        protected readonly OptionalField $optional,
        protected readonly array $entityConditions
    ) {
        $this->setBehaviorCallback = $setBehaviorCallback;
    }

    public function __invoke(string $name, array $propertyPath, string $entityClass, TransferableTypeInterface|TransferableTypeProviderInterface $relationshipType): RelationshipSetBehaviorInterface
    {
        return new CallbackToManyRelationshipSetBehavior(
            $name,
            $this->entityConditions,
            $this->relationshipConditions,
            $relationshipType,
            $this->setBehaviorCallback,
            $this->optional
        );
    }

    public function createRelationshipSetBehavior(string $name, array $propertyPath, string $entityClass, ResourceTypeInterface $relationshipType): RelationshipSetBehaviorInterface
    {
        return $this($name, $propertyPath, $entityClass, $relationshipType);
    }
}
