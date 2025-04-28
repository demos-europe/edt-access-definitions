<?php

declare(strict_types=1);

namespace Tests\Wrapping\WrapperFactories;

use EDT\ConditionFactory\ConditionFactory;
use EDT\JsonApi\ApiDocumentation\AttributeTypeResolver;
use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\TypeProviders\LazyTypeProvider;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use EDT\Wrapping\WrapperFactories\WrapperObject;
use Tests\data\AdModelBasedTest;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BirthType;
use Tests\data\Types\BookType;

class WrapperObjectTest extends AdModelBasedTest
{
    private AuthorType $authorType;
    private BookType $bookType;

    protected function setUp(): void
    {
        parent::setUp();
        $conditionFactory = new ConditionFactory();
        $propertyAccessor = new ReflectionPropertyAccessor();
        $lazyTypeProvider = new LazyTypeProvider();
        $attributeTypeResolver = new AttributeTypeResolver();
        $this->authorType = new AuthorType($conditionFactory, $lazyTypeProvider, $propertyAccessor, $attributeTypeResolver);
        $typeResolver = new AttributeTypeResolver();
        $this->bookType = new BookType($conditionFactory, $lazyTypeProvider, $propertyAccessor, $typeResolver);
        $typeProvider = new PrefilledTypeProvider([
            $this->authorType,
            $this->bookType,
            new BirthType($conditionFactory),
        ]);
        $lazyTypeProvider->setAllTypes($typeProvider);
    }

    public function testCreateWrapperInvalidInstance(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new WrapperObject($this->authors['salinger'], $this->authorType);
    }

    public function testAllowedPropertyAccess(): void
    {
        $author = new WrapperObject($this->authors['dickens'], $this->authorType);
        self::assertSame('England', $author->birthCountry);
        self::assertSame('England', $author->getBirthCountry());
        self::assertSame('Charles John Huffam Dickens', $author->name);
        self::assertSame('Charles John Huffam Dickens', $author->getName());
        self::assertCount(1, $author->books);
        self::assertCount(1, $author->getBooks());
        self::assertSame($this->books['pickwickPapers']->getTitle(), $author->books[0]->title);
        self::assertSame($this->books['pickwickPapers']->getTitle(), $author->books[0]->getTitle());
        $this->bookType->setAvailableInstances($this->getTestData()['books']);
        $author->__set('books', [
            $this->books['beowulf'],
            $this->books['doctorSleep'],
        ]);
        self::assertCount(2, $author->books);
        self::assertCount(2, $author->getBooks());
        self::assertSame($this->books['beowulf']->getTitle(), $author->books[0]->title);
        self::assertSame($this->books['beowulf']->getTitle(), $author->books[0]->getTitle());
        self::assertSame($this->books['doctorSleep']->getTitle(), $author->books[1]->title);
        self::assertSame($this->books['doctorSleep']->getTitle(), $author->books[1]->getTitle());

        $author->birthCountry = 'Sweden';
        self::assertSame('Sweden', $author->birthCountry);
        self::assertSame('Sweden', $author->getBirthCountry());
        $author->setBirthCountry('USA');
        self::assertSame('USA', $author->birthCountry);
        self::assertSame('USA', $author->getBirthCountry());

        $author->name = 'Paul';
        self::assertSame('Paul', $author->name);
        self::assertSame('Paul', $author->getName());
        $author->setName('Jerome David Salinger');
        self::assertSame('Jerome David Salinger', $author->name);
        self::assertSame('Jerome David Salinger', $author->getName());
    }

    public function testDisallowedPropertyUpdateAccess(): void
    {
        $this->expectException(AccessException::class);

        $author = new WrapperObject($this->authors['dickens'], $this->authorType);
        self::assertSame('Boz', $author->pseudonym);
        self::assertSame('Boz', $author->getPseudonym());
        /** @link WrapperObject::__set} */
        $author->pseudonym = 'Foobar';
    }

    public function testDisallowedPropertyReadAccess(): void
    {
        $this->expectException(AccessException::class);

        $author = new WrapperObject($this->authors['dickens'], $this->authorType);
        self::assertIsObject($author);
        $author->birth;
    }

    public function testDeniedPropertyAccess(): void
    {
        $this->expectException(AccessException::class);
        $author = new WrapperObject($this->authors['dickens'], $this->authorType);

        self::assertSame('Boz', $author->pseudonym);
        self::assertSame('Boz', $author->getPseudonym());
        $author->pseudonym = 'Paul';
    }

    public function testDeniedMethodAccess(): void
    {
        $this->expectException(AccessException::class);
        $author = new WrapperObject($this->authors['dickens'], $this->authorType);

        self::assertSame('Boz', $author->pseudonym);
        self::assertSame('Boz', $author->getPseudonym());
        $author->setPseudonym('Paul');
    }

    public function testInvalidCall(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $author = new WrapperObject($this->authors['salinger'], $this->authorType);
        $author->isName();
    }

    public function testInvalidParameterCount(): void
    {
        $this->expectException(AccessException::class);
        $author = new WrapperObject($this->authors['dickens'], $this->authorType);
        $author->setName('Paul', 'David');
    }
}
