<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\ExposableRelationshipTypeInterface;
use EDT\Wrapping\Contracts\Types\FilterableTypeInterface;
use EDT\Wrapping\Contracts\Types\IdentifiableTypeInterface;
use EDT\Wrapping\Contracts\Types\SortableTypeInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Properties\ToOneRelationshipReadability;
use Tests\data\Model\Book;

/**
 * @template-implements TransferableTypeInterface<Book>
 * @template-implements IdentifiableTypeInterface<Book>
 * @template-implements FilterableTypeInterface<Book>
 * @template-implements SortableTypeInterface<Book>
 */
class BookType implements
    TransferableTypeInterface,
    FilterableTypeInterface,
    SortableTypeInterface,
    IdentifiableTypeInterface,
    ExposableRelationshipTypeInterface
{
    private bool $exposedAsRelationship = true;

    public function __construct(
        private readonly PathsBasedConditionFactoryInterface $conditionFactory,
        protected readonly TypeProviderInterface $typeProvider
    ) {}

    public function getReadableProperties(): array
    {
        return [
            [
                'title' => new TestAttributeReadability(false, false, null),
                'tags' => new TestAttributeReadability(false, false, null),
            ], [
                'author' => new ToOneRelationshipReadability(false, false, false, null,
                    $this->typeProvider->requestType(AuthorType::class)->getInstanceOrThrow(),
                ),
            ],
            [],
        ];
    }

    public function getFilterableProperties(): array
    {
        return [
            'title' => null,
            'author' => $this->typeProvider->requestType(AuthorType::class)->getInstanceOrThrow(),
            'tags' => null,
        ];
    }

    public function getSortableProperties(): array
    {
        return [
            'title' => null,
            'author' => $this->typeProvider->requestType(AuthorType::class)->getInstanceOrThrow(),
        ];
    }

    public function getAccessCondition(): PathsBasedInterface
    {
        return $this->conditionFactory->propertyHasNotSize(0, ['author', 'books']);
    }

    public function getEntityClass(): string
    {
        return Book::class;
    }

    public function getIdentifierPropertyPath(): array
    {
        return ['title'];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function isExposedAsRelationship(): bool
    {
        return $this->exposedAsRelationship;
    }

    public function getDefaultSortMethods(): array
    {
        return [];
    }

    public function getInternalProperties(): array
    {
        return [
            'title' => null,
            'author' => $this->typeProvider->requestType(AuthorType::class)->getInstanceOrThrow(),
            'tags' => null,
        ];
    }

    public function getUpdatableProperties(): array
    {
        return [];
    }

    public function getIdentifier(): string
    {
        return self::class;
    }
}
