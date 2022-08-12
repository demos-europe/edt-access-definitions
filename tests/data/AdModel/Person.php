<?php

declare(strict_types=1);

namespace Tests\data\AdModel;

class Person
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $pseudonym;
    /**
     * @var Birth
     */
    protected $birth;
    /**
     * @var array<int,Book>
     */
    protected $books;

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
