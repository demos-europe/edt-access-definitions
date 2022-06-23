<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\Querying\Contracts\ConditionFactoryInterface;
use EDT\Querying\Contracts\FunctionInterface;
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
class BookType implements ReadableTypeInterface, FilterableTypeInterface, SortableTypeInterface, IdentifiableTypeInterface
{
    private $available = true;

    /**
     * @var ConditionFactoryInterface
     */
    private $conditionFactory;

    public function __construct(ConditionFactoryInterface $conditionFactory)
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

    public function getAccessCondition(): FunctionInterface
    {
        return $this->conditionFactory->propertyHasNotSize(0, 'author', 'books');
    }

    public function getEntityClass(): string
    {
        return Book::class;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function getIdentifierPropertyPath(): array
    {
        return ['title'];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function isReferencable(): bool
    {
        return true;
    }

    public function isDirectlyAccessible(): bool
    {
        return true;
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
