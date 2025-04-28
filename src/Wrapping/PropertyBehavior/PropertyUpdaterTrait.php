<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\Wrapping\Contracts\ContentField;
use EDT\Wrapping\Contracts\Types\NamedTypeInterface;
use EDT\Wrapping\Contracts\Types\RelationshipFetchableTypeInterface;
use Exception;
use Webmozart\Assert\Assert;

trait PropertyUpdaterTrait
{
    /**
     * @template TRel of object
     *
     * @param NamedTypeInterface&RelationshipFetchableTypeInterface<TRel> $relationshipType
     * @param list<DrupalFilterInterface> $relationshipConditions
     * @param JsonApiRelationship|null $relationshipRef
     *
     * @return TRel|null
     *
     * @throws Exception
     */
    protected function determineToOneRelationshipValue(
        NamedTypeInterface&RelationshipFetchableTypeInterface $relationshipType,
        array $relationshipConditions,
        ?array $relationshipRef
    ): ?object {
        if (null === $relationshipRef) {
            // TODO (#148): how to disallow a `null` relationship? can it be done with a condition?
            return null;
        }

        $expectedType = $relationshipType->getTypeName();
        Assert::same($relationshipRef[ContentField::TYPE], $expectedType);

        return $relationshipType->getEntityForRelationship($relationshipRef[ContentField::ID], $relationshipConditions);
    }

    /**
     * @template TRel of object
     *
     * @param NamedTypeInterface&RelationshipFetchableTypeInterface<TRel> $relationshipType
     * @param list<DrupalFilterInterface> $relationshipConditions
     * @param list<JsonApiRelationship> $relationshipRefs
     *
     * @return list<TRel>
     */
    protected function determineToManyRelationshipValues(
        NamedTypeInterface&RelationshipFetchableTypeInterface $relationshipType,
        array $relationshipConditions,
        array $relationshipRefs
    ): array {
        if ([] === $relationshipRefs) {
            // TODO (#148): how to disallow an empty relationship?
            return [];
        }

        Assert::allSame(array_column($relationshipRefs, ContentField::TYPE), $relationshipType->getTypeName());
        $relationshipIds = array_column($relationshipRefs, ContentField::ID);
        $relationshipValues = $relationshipType->getEntitiesForRelationship($relationshipIds, $relationshipConditions, []);
        Assert::count($relationshipValues, count($relationshipRefs), 'Tried to fetch %d entities, only %d were available for update according to the given identifiers and applied conditions.');

        return $relationshipValues;
    }
}
