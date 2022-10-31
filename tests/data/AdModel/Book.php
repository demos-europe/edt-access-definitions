<?php

declare(strict_types=1);

namespace Tests\data\AdModel;

class Book
{
    protected string $title;

    protected Person $author;

    /**
     * @var string[]
     */
    protected array $tags;

    public function __construct(string $title, Person $author, string ...$tags)
    {
        $this->title = $title;
        $this->author = $author;
        $this->tags = $tags;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
