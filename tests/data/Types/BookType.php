<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\ExposableRelationshipTypeInterface;
use EDT\Wrapping\Contracts\Types\FilterableTypeInterface;
use EDT\Wrapping\Contracts\Types\IdentifiableTypeInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\SortableTypeInterface;
use Tests\data\Model\Book;

/**
 * @template-implements ReadableTypeInterface<Book>
 * @template-implements IdentifiableTypeInterface<Book>
 * @template-implements FilterableTypeInterface<Book>
 * @template-implements SortableTypeInterface<Book>
 */
class BookType implements ReadableTypeInterface, FilterableTypeInterface, SortableTypeInterface, IdentifiableTypeInterface, ExposableRelationshipTypeInterface
{
    private bool $exposedAsRelationship = true;

    private PathsBasedConditionFactoryInterface $conditionFactory;

    public function __construct(PathsBasedConditionFactoryInterface $conditionFactory)
    {
        $this->conditionFactory = $conditionFactory;
    }

    public function getReadableProperties(): array
    {
        return $this->getFilterableProperties();
    }

    public function getFilterableProperties(): array
    {
        return [
            'title' => null,
            'author' => AuthorType::class,
            'tags' => null,
        ];
    }

    public function getSortableProperties(): array
    {
        return [
            'title' => null,
            'author' => AuthorType::class,
        ];
    }

    public function getAccessCondition(): PathsBasedInterface
    {
        return $this->conditionFactory->propertyHasNotSize(0, 'author', 'books');
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
            'author' => AuthorType::class,
            'tags' => null,
        ];
    }
}
