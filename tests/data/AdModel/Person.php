<?php

declare(strict_types=1);

namespace Tests\data\AdModel;

class Person
{
    /**
     * @param list<Book> $books
     */
    public function __construct(
        protected string $id,
        protected string $name,
        protected ?string $pseudonym,
        protected Birth $birth,
        protected array $books = []
    ) {}

    public function addBook(Book $book): void
    {
        $this->books[] = $book;
    }

    /**
     * @return iterable<Book>
     */
    public function getBooks(): iterable
    {
        return $this->books;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
