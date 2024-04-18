<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship;

use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToMany\AbstractPropertySetBehavior;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractPropertySetBehavior<TCondition, TEntity>
 * @template-implements RelationshipSetBehaviorInterface<TCondition, TSorting, TEntity, TRelationship>
 */
abstract class AbstractRelationshipSetBehavior extends AbstractPropertySetBehavior implements RelationshipSetBehaviorInterface
{
    /**
     * @param non-empty-string $propertyName
     * @param list<TCondition> $entityConditions
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship>|TransferableTypeProviderInterface<TCondition, TSorting, TRelationship> $relationshipType
     */
    public function __construct(
        string $propertyName,
        array $entityConditions,
        OptionalField $optional,
        protected readonly TransferableTypeInterface|TransferableTypeProviderInterface $relationshipType
    ) {
        parent::__construct($propertyName, $entityConditions, $optional);
    }

    public function getRelationshipType(): TransferableTypeInterface
    {
        return $this->relationshipType instanceof TransferableTypeInterface
            ? $this->relationshipType
            : $this->relationshipType->getType();
    }
}
