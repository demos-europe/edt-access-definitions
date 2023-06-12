<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\JsonApi\ApiDocumentation\AttributeTypeResolver;
use EDT\JsonApi\Properties\Id\PathIdReadability;
use EDT\JsonApi\Properties\Relationships\PathToManyRelationshipReadability;
use EDT\JsonApi\Properties\Relationships\PathToManyRelationshipSetability;
use EDT\JsonApi\RequestHandling\Body\UpdateRequestBody;
use EDT\JsonApi\RequestHandling\ExpectedPropertyCollection;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\PropertyPaths\PropertyLink;
use EDT\Querying\Utilities\ConditionEvaluator;
use EDT\Querying\Utilities\TableJoiner;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\ExposableRelationshipTypeInterface;
use EDT\Wrapping\Contracts\Types\FilteringTypeInterface;
use EDT\Wrapping\Contracts\Types\SortingTypeInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Properties\ReadabilityCollection;
use EDT\Wrapping\Properties\UpdatablePropertyCollection;
use Tests\data\Model\Person;
use Webmozart\Assert\Assert;

class AuthorType implements
    TransferableTypeInterface,
    FilteringTypeInterface,
    SortingTypeInterface,
    ExposableRelationshipTypeInterface
{
    public function __construct(
        protected readonly PathsBasedConditionFactoryInterface $conditionFactory,
        protected readonly TypeProviderInterface $typeProvider,
        protected readonly PropertyAccessorInterface $propertyAccessor,
        protected readonly AttributeTypeResolver $typeResolver
    ) {}

    public function getReadableProperties(): ReadabilityCollection
    {
        return new ReadabilityCollection(
            [
                'name' => new TestAttributeReadability(['name'], $this->propertyAccessor),
                'pseudonym' => new TestAttributeReadability(['pseudonym'], $this->propertyAccessor),
                'birthCountry' => new TestAttributeReadability(['birth', 'country'], $this->propertyAccessor),
            ],
            [],
            [
                'books' => new PathToManyRelationshipReadability(
                    $this->getEntityClass(),
                    ['books'],
                    false,
                    false,
                    $this->typeProvider->getTypeByIdentifier(BookType::class),
                    $this->propertyAccessor,
                    $this->entityVerifier
                ),
            ],
            new PathIdReadability(
                $this->getEntityClass(),
                ['id'],
                $this->propertyAccessor,
                $this->typeResolver
            )
        );
    }

    public function getFilteringProperties(): array
    {
        return [
            'id' => new PropertyLink(['id'], null),
            'name' => new PropertyLink(['name'], null),
            'pseudonym' => new PropertyLink(['pseudonym'], null),
            'books' => new PropertyLink(
                ['books'],
                $this->typeProvider->getTypeByIdentifier(BookType::class)
            ),
            'birthCountry' => new PropertyLink(['birth', 'country'], null),
        ];
    }

    public function getSortingProperties(): array
    {
        return [
            'name' => new PropertyLink(['name'], null),
            'pseudonym' => new PropertyLink(['pseudonym'], null),
            'birthCountry' => new PropertyLink(['birth', 'country'], null),
        ];
    }

    public function getAccessConditions(): array
    {
        return [$this->conditionFactory->propertyHasNotSize(0, ['books'])];
    }

    public function getEntityClass(): string
    {
        return Person::class;
    }

    public function isExposedAsRelationship(): bool
    {
        return true;
    }

    public function getUpdatableProperties(): UpdatablePropertyCollection
    {
        $bookType = $this->typeProvider->getTypeByIdentifier(BookType::class);

        return new UpdatablePropertyCollection(
            [
                'name' => new TestAttributeSetability(['name'], $this->propertyAccessor),
                'birthCountry' => new TestAttributeSetability(['birth', 'country'], $this->propertyAccessor),
            ],
            [],
            [
                'books' => new PathToManyRelationshipSetability(
                    self::class,
                    [],
                    $bookType->getAccessConditions(),
                    $bookType,
                    ['books'],
                    $this->propertyAccessor
                ),
            ],
        );
    }

    public function getTypeName(): string
    {
        return self::class;
    }

    public function getEntityByIdentifier(string $identifier, array $conditions): object
    {
        throw new \RuntimeException();
    }

    public function getEntitiesForRelationship(array $identifiers, array $conditions, array $sortMethods): array
    {
        throw new \RuntimeException();
    }

    public function getExpectedUpdateProperties(): ExpectedPropertyCollection
    {
        throw new \RuntimeException();
    }

    public function updateEntity(UpdateRequestBody $requestBody): ?object
    {
        throw new \RuntimeException();
    }

    public function assertMatchingEntities(array $entities, array $conditions): void
    {
        throw new \RuntimeException();
    }

    public function assertMatchingEntity(object $entity, array $conditions): void
    {
        $tableJoiner = new TableJoiner($this->propertyAccessor);
        $conditionEvaluator = new ConditionEvaluator($tableJoiner);
        $conditions = array_merge($conditions, $this->getAccessConditions());
        Assert::true($conditionEvaluator->evaluateConditions($entity, $conditions));
    }

    public function isMatchingEntity(object $entity, array $conditions): bool
    {
        $conditions = array_merge($conditions, $this->getAccessConditions());
        $tableJoiner = new TableJoiner($this->propertyAccessor);

        return (new ConditionEvaluator($tableJoiner))->evaluateConditions($entity, $conditions);
    }

    public function reindexEntities(array $entities, array $conditions, array $sortMethods): array
    {
        throw new \RuntimeException();
    }

    public function getEntityForRelationship(string $identifier, array $conditions): object
    {
        throw new \RuntimeException();
    }
}
