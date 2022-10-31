<?php

declare(strict_types=1);

namespace Tests\data\AdModel;

class Person
{
    protected string $name;

    protected ?string $pseudonym = null;

    protected Birth $birth;

    /**
     * @var list<Book>
     */
    protected array $books;

    public function __construct(string $name, ?string $pseudonym, Birth $birth)
    {
        $this->name = $name;
        $this->pseudonym = $pseudonym;
        $this->birth = $birth;
        $this->books = [];
    }

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
}
