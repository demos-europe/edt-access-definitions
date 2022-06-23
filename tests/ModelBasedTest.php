<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\data\Model\Birth;
use Tests\data\Model\Book;
use Tests\data\Model\Person;

abstract class ModelBasedTest extends TestCase
{
    /**
     * @var Person[]
     */
    protected $authors;
    /**
     * @var Book[]
     */
    protected $books;

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
        $king = new Person('Stephen King', 'Richard Bachman', new Birth(
            'USA', 'Maine', 'Portland', 1947, 9, 21
        ));
        $tolkien = new Person('John Ronald Reuel Tolkien', null, new Birth(
            'Oranje-Freistaat', null, 'Bloemfontein', 1892, 1, 3
        ));
        $dickens = new Person('Charles John Huffam Dickens', 'Boz', new Birth(
            'England', 'Portsmouth', 'Landport', 1812, 2, 7
        ));
        $rowling = new Person('Joanne K. Rowling', 'Robert Galbraith', new Birth(
            'England', 'South Gloucestershire', 'Yate', 1965, 7, 31
        ));
        $lee = new Person('Nelle Harper Lee', null, new Birth(
            'USA', 'Alabama', 'Monroeville', 1926, 4, 28
        ));
        $salinger = new Person('Jerome David Salinger', null, new Birth(
            'USA', 'New York', 'Manhattan', 1919, 1, 1
        ));
        $phen = new Person('Manfred', 'Mannie', new Birth(
           'Germany', 'Berlin', 'Spandau', 2012, 7, 8
        ));

        $doctorSleep = new Book('Doctor Sleep', $king);
        $beowulf = new Book('Beowulf: The Monsters and the Critics', $tolkien);
        $pickwickPapers = new Book('The Pickwick Papers', $dickens, 'Novel');
        $philosopherStone = new Book('Harry Potter and the Philosopher\'s Stone', $rowling);
        $deathlyHallows = new Book('Harry Potter and the Deathly Hallows', $rowling);

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
