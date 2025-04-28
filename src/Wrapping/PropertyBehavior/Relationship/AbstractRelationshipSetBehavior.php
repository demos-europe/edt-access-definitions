<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToMany\AbstractPropertySetBehavior;

/**
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractPropertySetBehavior<TEntity>
 * @template-implements RelationshipSetBehaviorInterface<TEntity, TRelationship>
 */
abstract class AbstractRelationshipSetBehavior extends AbstractPropertySetBehavior implements RelationshipSetBehaviorInterface
{
    /**
     * @param non-empty-string $propertyName
     * @param list<DrupalFilterInterface> $entityConditions
     * @param TransferableTypeInterface<TRelationship>|TransferableTypeProviderInterface<TRelationship> $relationshipType
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
