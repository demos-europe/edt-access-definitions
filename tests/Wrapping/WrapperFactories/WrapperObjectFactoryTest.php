<?php

declare(strict_types=1);

namespace Tests\Wrapping\WrapperFactories;

use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Querying\Utilities\ConditionEvaluator;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use EDT\Wrapping\Utilities\PropertyReader;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
use EDT\Wrapping\Utilities\TypeAccessor;
use EDT\Wrapping\WrapperFactories\WrapperObject;
use EDT\Wrapping\WrapperFactories\WrapperObjectFactory;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BookType;
use Tests\ModelBasedTest;

class WrapperObjectFactoryTest extends ModelBasedTest
{
    /**
     * @var AuthorType
     */
    private $authorType;
    /**
     * @var WrapperObjectFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $conditionFactory = new PhpConditionFactory();
        $this->authorType = new AuthorType($conditionFactory);
        $typeProvider = new PrefilledTypeProvider([
            $this->authorType,
            new BookType($conditionFactory),
        ]);
        $propertyAccessor = new ReflectionPropertyAccessor();
        $this->factory = new WrapperObjectFactory(
            new TypeAccessor($typeProvider),
            new PropertyReader($propertyAccessor, new SchemaPathProcessor($typeProvider)),
            $propertyAccessor,
            new ConditionEvaluator($propertyAccessor)
        );
    }

    public function testAllowedPropertyAccess(): void
    {
        $author = $this->factory->createWrapper($this->authors['salinger'], $this->authorType);
        self::assertSame('USA', $author->birthCountry);
        self::assertSame('USA', $author->getBirthCountry());
        /** @link WrapperObject::__set} */
        $author->birthCountry = 'Sweden';
        self::assertSame('Sweden', $author->birthCountry);
        self::assertSame('Sweden', $author->getBirthCountry());
        $author->setBirthCountry('USA');
        self::assertSame('USA', $author->birthCountry);
        self::assertSame('USA', $author->getBirthCountry());

        $author = $this->factory->createWrapper($this->authors['salinger'], $this->authorType);
        self::assertSame('Jerome David Salinger', $author->name);
        self::assertSame('Jerome David Salinger', $author->getName());
        /** @link WrapperObject::__set} */
        $author->name = 'Paul';
        self::assertSame('Paul', $author->name);
        self::assertSame('Paul', $author->getName());
        $author->setName('Jerome David Salinger');
        self::assertSame('Jerome David Salinger', $author->name);
        self::assertSame('Jerome David Salinger', $author->getName());

        $author = $this->factory->createWrapper($this->authors['dickens'], $this->authorType);
        self::assertCount(1, $author->books);
        self::assertCount(1, $author->getBooks());
        self::assertSame($this->books['pickwickPapers']->getTitle(), $author->books[0]->title);
        self::assertSame($this->books['pickwickPapers']->getTitle(), $author->books[0]->getTitle());
        /** @link WrapperObject::__set} */
        $author->books = [
            $this->books['beowulf'],
            $this->books['doctorSleep'],
        ];
        self::assertCount(2, $author->books);
        self::assertCount(2, $author->getBooks());
        self::assertSame($this->books['beowulf']->getTitle(), $author->books[0]->title);
        self::assertSame($this->books['beowulf']->getTitle(), $author->books[0]->getTitle());
        self::assertSame($this->books['doctorSleep']->getTitle(), $author->books[1]->title);
        self::assertSame($this->books['doctorSleep']->getTitle(), $author->books[1]->getTitle());
    }

    public function testDisallowedPropertyUpdateAccess(): void
    {
        $this->expectException(AccessException::class);

        $author = $this->factory->createWrapper($this->authors['salinger'], $this->authorType);
        self::assertSame(null, $author->pseudonym);
        self::assertSame(null, $author->getPseudonym());
        /** @link WrapperObject::__set} */
        $author->pseudonym = 'Foobar';

        self::fail('Expected exception');
    }

    public function testDisallowedPropertyReadAccess(): void
    {
        $this->expectException(AccessException::class);

        $author = $this->factory->createWrapper($this->authors['salinger'], $this->authorType);
        self::assertIsObject($author);
        $author->birth;

        self::fail('Expected exception');
    }

    public function testDeniedPropertyAccess(): void
    {
        $this->expectException(AccessException::class);
        $author = $this->factory->createWrapper($this->authors['salinger'], $this->authorType);

        self::assertSame(null, $author->pseudonym);
        self::assertSame(null, $author->getPseudonym());
        $author->pseudonym = 'Paul';
        self::fail('Expected exception');
    }

    public function testDeniedMethodAccess(): void
    {
        $this->expectException(AccessException::class);
        $author = $this->factory->createWrapper($this->authors['salinger'], $this->authorType);

        self::assertSame(null, $author->pseudonym);
        self::assertSame(null, $author->getPseudonym());
        $author->setPseudonym('Paul');
        self::fail('Expected exception');
    }

    public function testInvalidCall(): void
    {
        $this->expectException(AccessException::class);
        $author = $this->factory->createWrapper($this->authors['salinger'], $this->authorType);
        $author->isName();
        self::fail('Expected exception');
    }

    public function testInvalidParameterCount(): void
    {
        $this->expectException(AccessException::class);
        $author = $this->factory->createWrapper($this->authors['salinger'], $this->authorType);
        $author->setName('Paul', 'David');
        self::fail('Expected exception');
    }
}
