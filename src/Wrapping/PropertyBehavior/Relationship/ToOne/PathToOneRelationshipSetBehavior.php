<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToOne;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToOne\Factory\PathToOneRelationshipSetBehaviorFactory;
use Webmozart\Assert\Assert;

/**
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractToOneRelationshipSetBehavior<TEntity, TRelationship>
 */
class PathToOneRelationshipSetBehavior extends AbstractToOneRelationshipSetBehavior
{
    /**
     * @param non-empty-string $propertyName
     * @param class-string<TEntity> $entityClass
     * @param list<DrupalFilterInterface> $entityConditions
     * @param list<DrupalFilterInterface> $relationshipConditions
     * @param TransferableTypeInterface<TRelationship>|TransferableTypeProviderInterface<TRelationship> $relationshipType
     * @param non-empty-list<non-empty-string> $propertyPath
     */
    public function __construct(
        string                                                      $propertyName,
        protected readonly string                                   $entityClass,
        array                                                       $entityConditions,
        array                                                       $relationshipConditions,
        TransferableTypeInterface|TransferableTypeProviderInterface $relationshipType,
        protected readonly array                                    $propertyPath,
        protected readonly PropertyAccessorInterface                $propertyAccessor,
        OptionalField                                               $optional
    ) {
        parent::__construct($propertyName, $entityConditions, $relationshipConditions, $optional, $relationshipType);
    }

    /**
     * @param list<DrupalFilterInterface> $relationshipConditions
     * @param list<DrupalFilterInterface> $entityConditions
     *
     * @return RelationshipSetBehaviorFactoryInterface<object, object>
     */
    public static function createFactory(
        array $relationshipConditions,
        OptionalField $optional,
        PropertyAccessorInterface $propertyAccessor,
        array $entityConditions
    ): RelationshipSetBehaviorFactoryInterface {
        return new PathToOneRelationshipSetBehaviorFactory($relationshipConditions, $optional, $propertyAccessor, $entityConditions);
    }

    public function updateToOneRelationship(object $entity, ?object $relationship): array
    {
        $propertyPath = $this->propertyPath;
        $propertyName = array_pop($propertyPath);
        $target = [] === $propertyPath
            ? $entity
            : $this->propertyAccessor->getValueByPropertyPath($entity, ...$propertyPath);
        Assert::object($target);
        $this->propertyAccessor->setValue($target, $relationship, $propertyName);

        return [];
    }
    public function getDescription(): string
    {
        $propertyPathString = implode('.', $this->propertyPath);
        $relationshipType = $this->getRelationshipType()->getTypeName();

        return
            ($this->optional->equals(OptionalField::YES)
                ? "Allows a to-one relationship `$this->propertyName` of type `$relationshipType` to be present in the request body, but does not require it. "
                : "Requires a to-one relationship `$this->propertyName` of type `$relationshipType` to be present in the request body.")
            . "The relationship will be stored in $this->entityClass::$propertyPathString. "
            . ([] === $this->entityConditions
                ? 'The entity does not need to '
                : 'The entity must ')
            . 'match additional conditions beside the ones defined by its type. '
            . ([] === $this->relationshipConditions
                ? 'The relationship do not need to '
                : 'The relationship must ')
            . 'match additional conditions beside the ones defined by its type.';
    }
}
