<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship\ToMany;

use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\Contracts\TransferableTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\RelationshipSetBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToMany\Factory\PathToManyRelationshipSetBehaviorFactory;
use Webmozart\Assert\Assert;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 * @template TRelationship of object
 *
 * @template-extends AbstractToManyRelationshipSetBehavior<TCondition, TSorting, TEntity, TRelationship>
 */
class PathToManyRelationshipSetBehavior extends AbstractToManyRelationshipSetBehavior
{
    /**
     * @param non-empty-string $propertyName
     * @param class-string<TEntity> $entityClass
     * @param list<TCondition> $entityConditions
     * @param list<TCondition> $relationshipConditions
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship>|TransferableTypeProviderInterface<TCondition, TSorting, TRelationship> $relationshipType
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
     * @template TCond of PathsBasedInterface
     *
     * @param list<TCond> $relationshipConditions
     * @param list<TCond> $entityConditions
     *
     * @return RelationshipSetBehaviorFactoryInterface<TCond, PathsBasedInterface, object, object>
     */
    public static function createFactory(
        array $relationshipConditions,
        OptionalField $optional,
        PropertyAccessorInterface $propertyAccessor,
        array $entityConditions
    ): RelationshipSetBehaviorFactoryInterface {
        return new PathToManyRelationshipSetBehaviorFactory($relationshipConditions, $optional, $propertyAccessor, $entityConditions);
    }

    public function updateToManyRelationship(object $entity, array $relationships): array
    {
        $propertyPath = $this->propertyPath;
        $propertyName = array_pop($propertyPath);
        $target = [] === $propertyPath
            ? $entity
            : $this->propertyAccessor->getValueByPropertyPath($entity, ...$propertyPath);
        Assert::object($target);
        $this->propertyAccessor->setValue($target, $relationships, $propertyName);

        return [];
    }

    public function getDescription(): string
    {
        $propertyPathString = implode('.', $this->propertyPath);
        $relationshipType = $this->getRelationshipType()->getTypeName();

        return
            ($this->optional->equals(OptionalField::YES)
                ? "Allows a to-many relationship `$this->propertyName` of type `$relationshipType` to be present in the request body, but does not require it. "
                : "Requires a to-many relationship `$this->propertyName` of type `$relationshipType` to be present in the request body.")
            . "The relationship will be stored in $this->entityClass::$propertyPathString. "
            . ([] === $this->entityConditions
                ? 'The entity does not need to '
                : 'The entity must ')
            . 'match additional conditions beside the ones defined by its type. '
            . ([] === $this->relationshipConditions
                ? 'The relationships do not need to '
                : 'The relationships must ')
            . 'match additional conditions beside the ones defined by their type.';
    }
}
