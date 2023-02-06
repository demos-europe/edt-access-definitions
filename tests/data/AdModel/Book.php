<?php

declare(strict_types=1);

namespace Tests\data\AdModel;

class Book
{
    /**
     * @param list<non-empty-string> $tags
     */
    public function __construct(
        protected string $title,
        protected Person $author,
        protected array $tags
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }
}
