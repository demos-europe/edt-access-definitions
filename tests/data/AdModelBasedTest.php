<?php

declare(strict_types=1);

namespace Tests\data;

use PHPUnit\Framework\TestCase;
use Tests\data\AdModel\Birth;
use Tests\data\AdModel\Book;
use Tests\data\AdModel\Person;

abstract class AdModelBasedTest extends TestCase
{
    /**
     * @var array<non-empty-string, Person>
     */
    protected array $authors;

    /**
     * @var array<non-empty-string, Book>
     */
    protected array $books;

    protected function setUp(): void
    {
        parent::setUp();
        $testData = $this->getTestData();
        $this->authors = $testData['authors'];
        $this->books = $testData['books'];
    }

    /**
     * @return array[]
     */
    protected function getTestData(): array
    {
        $king = new Person('1', 'Stephen King', 'Richard Bachman', new Birth(
            'USA', 'Maine', 'Portland', 1947, 9, 21
        ));
        $tolkien = new Person('2', 'John Ronald Reuel Tolkien', null, new Birth(
            'Oranje-Freistaat', null, 'Bloemfontein', 1892, 1, 3
        ));
        $dickens = new Person('3', 'Charles John Huffam Dickens', 'Boz', new Birth(
            'England', 'Portsmouth', 'Landport', 1812, 2, 7
        ));
        $rowling = new Person('4', 'Joanne K. Rowling', 'Robert Galbraith', new Birth(
            'England', 'South Gloucestershire', 'Yate', 1965, 7, 31
        ));
        $lee = new Person('5', 'Nelle Harper Lee', null, new Birth(
            'USA', 'Alabama', 'Monroeville', 1926, 4, 28
        ));
        $salinger = new Person('6', 'Jerome David Salinger', null, new Birth(
            'USA', 'New York', 'Manhattan', 1919, 1, 1
        ));
        $phen = new Person('7', 'Manfred', 'Mannie', new Birth(
           'Germany', 'Berlin', 'Spandau', 2012, 7, 8
        ));

        $doctorSleep = new Book('1', 'Doctor Sleep', $king, []);
        $beowulf = new Book('2', 'Beowulf: The Monsters and the Critics', $tolkien, []);
        $pickwickPapers = new Book('3', 'The Pickwick Papers', $dickens, ['Novel']);
        $philosopherStone = new Book('4', 'Harry Potter and the Philosopher\'s Stone', $rowling, []);
        $deathlyHallows = new Book('5', 'Harry Potter and the Deathly Hallows', $rowling, []);

        $king->addBook($doctorSleep);
        $tolkien->addBook($beowulf);
        $dickens->addBook($pickwickPapers);
        $rowling->addBook($philosopherStone);
        $rowling->addBook($deathlyHallows);

        return [
            'authors' => compact('king', 'tolkien', 'dickens', 'rowling', 'lee', 'salinger', 'phen'),
            'books' => compact('doctorSleep', 'beowulf', 'pickwickPapers', 'philosopherStone', 'deathlyHallows'),
        ];
    }
}
