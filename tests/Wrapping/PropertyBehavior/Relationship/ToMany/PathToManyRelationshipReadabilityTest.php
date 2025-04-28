<?php

declare(strict_types=1);

namespace Tests\Wrapping\PropertyBehavior\Relationship\ToMany;

use EDT\ConditionFactory\ConditionFactory;
use EDT\JsonApi\ApiDocumentation\AttributeTypeResolver;
use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Wrapping\PropertyBehavior\Relationship\ToMany\PathToManyRelationshipReadability;
use EDT\Wrapping\TypeProviders\LazyTypeProvider;
use PHPUnit\Framework\TestCase;
use Tests\data\AdModel\Birth;
use Tests\data\AdModel\Book;
use Tests\data\AdModel\Person;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BookType;

class PathToManyRelationshipReadabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $conditionFactory = new ConditionFactory();
        $lazyTypeProvider = new LazyTypeProvider();
        $this->propertyAccessor = new ReflectionPropertyAccessor();
        $typeResolver = new AttributeTypeResolver();
        $this->authorType = new AuthorType($conditionFactory, $lazyTypeProvider, $this->propertyAccessor, $typeResolver);
        $this->bookType = new BookType($conditionFactory, $lazyTypeProvider, $this->propertyAccessor, $typeResolver);
    }

    public function testGetValue(): void
    {
        $titleReadability = new PathToManyRelationshipReadability(
            Person::class,
            ['books'],
            false,
            false,
            $this->bookType,
            $this->propertyAccessor
        );

        $author = $this->getAuthor();

        $value = $titleReadability->getValue($author, [], []);
        self::assertIsArray($value);
        self::assertCount(2, $value);
        self::assertContainsOnlyInstancesOf(Book::class, $value);
    }

    public function testGetValueNested(): void
    {
        $titleReadability = new PathToManyRelationshipReadability(
            Person::class,
            ['books', 'author'],
            false,
            false,
            $this->authorType,
            $this->propertyAccessor
        );

        $author = $this->getAuthor();

        $value = $titleReadability->getValue($author, [], []);
        self::assertIsArray($value);
        self::assertCount(2, $value);
        self::assertContainsOnlyInstancesOf(Person::class, $value);
    }

    protected function getAuthor(): Person
    {
        $author = new Person(
            '1',
            'Maximilian',
            'Max',
            new Birth('Germany', 'Berlin', 'Berlin', 1000, 1, 1)
        );

        $bookA = new Book('1', 'A', $author, []);
        $bookB = new Book('2', 'B', $author, []);
        $author->addBook($bookA);
        $author->addBook($bookB);

        return $author;
    }
}
