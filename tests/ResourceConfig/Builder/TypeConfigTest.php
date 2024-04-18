<?php

declare(strict_types=1);

namespace Tests\ResourceConfig\Builder;

use EDT\JsonApi\ApiDocumentation\AttributeTypeResolver;
use EDT\JsonApi\Utilities\PropertyBuilderFactory;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Wrapping\Utilities\PropertyPathProcessorFactory;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
use EDT\Wrapping\WrapperFactories\WrapperObject;
use PHPUnit\Framework\TestCase;
use Tests\data\Model\Birth;
use Tests\data\Model\Book;
use Tests\data\Model\Person;

class TypeConfigTest extends TestCase
{
    public function testResourceConfigBuilder(): void
    {
        // prepare entities
        $author = new Person('1', 'tolkien', 't', new Birth('foo', null, 'bar', 1950, 1, 1));
        $book = new Book('1', 'lord of the rings', $author);
        $author->addBook($book);

        // prepare repositories
        $authorRepository = new PersonRepository($author);
        $bookRepository = new BookRepository($book);

        // create builders
        $schemaPathProcessor = new SchemaPathProcessor(new PropertyPathProcessorFactory());
        $propertyBuilderFactory = $this->getPropertyBuilderFactory();
        $bookConfig = new BookBasedTypeConfig(Book::class, $propertyBuilderFactory, $bookRepository, $schemaPathProcessor);
        $authorConfig = new PersonBasedConfigBuilder(Person::class, $propertyBuilderFactory, $bookRepository, $schemaPathProcessor);

        // configure resources
        $bookConfig->id->setReadableByPath();
        $bookConfig->author
            ->setRelationshipType($authorConfig)
            ->setFilterable()
            ->setSortable()
            ->setReadableByPath();
        $bookConfig->setAccessConditions([]);

        $authorConfig->id->setReadableByPath();
        $authorConfig->books
            ->setRelationshipType($bookConfig)
            ->setFilterable()
            ->setReadableByPath();

        // if we can access authors via a book and the books from the accessed author
        // the intention is to test if the resource config builder can be build if it is configured in a circle and
        // does not for example get stuck in a recursion loop
        $bookWrapper = new WrapperObject($book, $bookConfig->getType());
        self::assertSame('Book', $bookWrapper->getTypeName());
        self::assertEquals($book, $bookWrapper->getEntity());
        self::assertSame($bookConfig->getType(), $bookWrapper->getResourceType());
        /** @var WrapperObject $authorWrapper */
        $authorWrapper = $bookWrapper->getPropertyValue('author');
        self::assertEquals($author, $authorWrapper->getEntity());
        self::assertSame($book, $authorWrapper->getPropertyValue('books')[0]->getEntity());
    }

    protected function getPropertyBuilderFactory(): PropertyBuilderFactory
    {
        $propertyAccessor = new ReflectionPropertyAccessor();
        $typeResolver = new AttributeTypeResolver();

        $propertyBuilderFactory = new PropertyBuilderFactory($propertyAccessor, $typeResolver);

        return $propertyBuilderFactory;
    }
}
