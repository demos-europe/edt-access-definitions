<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\ExposableRelationshipTypeInterface;
use EDT\Wrapping\Contracts\Types\FilterableTypeInterface;
use EDT\Wrapping\Contracts\Types\IdentifiableTypeInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Contracts\Types\SortableTypeInterface;
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

    private PathsBasedConditionFactoryInterface $conditionFactory;

    protected TypeProviderInterface $typeProvider;

    public function __construct(
        PathsBasedConditionFactoryInterface $conditionFactory,
        TypeProviderInterface $typeProvider
    ) {
        $this->conditionFactory = $conditionFactory;
        $this->typeProvider = $typeProvider;
    }

    public function getReadableProperties(): array
    {
        return [
            'title' => null,
            'author' => $this->typeProvider->requestType(AuthorType::class)->getInstanceOrThrow(),
            'tags' => null,
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

    public function getUpdatableProperties(object $updateTarget): array
    {
        return [];
    }
}
