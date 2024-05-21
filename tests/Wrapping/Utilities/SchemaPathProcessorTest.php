<?php

declare(strict_types=1);

namespace Tests\Wrapping\Utilities;

use EDT\ConditionFactory\ConditionFactory;
use EDT\JsonApi\ApiDocumentation\AttributeTypeResolver;
use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\Contracts\PropertyPathAccessInterface;
use EDT\Querying\Functions\Property;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Querying\PropertyPaths\PropertyPath;
use EDT\Querying\SortMethodFactories\PhpSortMethodFactory;
use EDT\Querying\SortMethodFactories\SortMethod;
use EDT\Querying\SortMethodFactories\SortMethodInterface;
use EDT\Querying\SortMethodFactories\SortMethodFactory;
use EDT\Querying\SortMethods\Ascending;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\TypeProviders\LazyTypeProvider;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use EDT\Wrapping\Utilities\ExternReadableRelationshipSchemaVerificationException;
use EDT\Wrapping\Utilities\PropertyPathProcessorFactory;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
use InvalidArgumentException;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BirthType;
use Tests\data\Types\BookType;
use Tests\ModelBasedTest;

class SchemaPathProcessorTest extends ModelBasedTest
{
    private SchemaPathProcessor $schemaPathProcessor;

    private AuthorType $authorType;

    private SortMethodFactory $sortMethodFactory;
    private BookType $bookType;
    private PrefilledTypeProvider $typeProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sortMethodFactory = new SortMethodFactory();
        $conditionFactory = new ConditionFactory();
        $lazyTypeProvider = new LazyTypeProvider();
        $propertyAccessor = new ReflectionPropertyAccessor();
        $propertyPathProcessorFactory = new PropertyPathProcessorFactory();
        $this->schemaPathProcessor = new SchemaPathProcessor($propertyPathProcessorFactory);
        $typeResolver = new AttributeTypeResolver();
        $this->bookType = new BookType($conditionFactory, $lazyTypeProvider, $propertyAccessor, $typeResolver);
        $this->authorType = new AuthorType($conditionFactory, $lazyTypeProvider, $propertyAccessor, $typeResolver);
        $this->typeProvider = new PrefilledTypeProvider([
            $this->bookType,
            $this->authorType,
            new BirthType($conditionFactory),
        ]);
        $lazyTypeProvider->setAllTypes($this->typeProvider);
    }

    public function testSortAccessException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("No property 'foo' is available. Available properties are: name, pseudonym, birthCountry");
        $invalidSortMethod = $this->sortMethodFactory->propertyAscending(['foo', 'bar']);
        $this->schemaPathProcessor->mapSorting($this->authorType, [$invalidSortMethod]);
    }

    public function testMapSorting(): void
    {
        $propertyPath = new PropertyPath(null, '', PropertyPathAccessInterface::UNPACK_RECURSIVE, ['name']);
        $sortMethod = new SortMethod($propertyPath->getAsNames(), false);
        $this->schemaPathProcessor->mapSorting($this->authorType, [$sortMethod]);
        self::assertSame('name', $sortMethod->getAsString());
    }


    public function testProcessPropertyPathWithAllowedAttribute(): void
    {
        $this->expectNotToPerformAssertions();
        $this->schemaPathProcessor->verifyExternReadablePath($this->authorType, ['books', 'title'], true);
    }

    public function testProcessPropertyPathWithNonAllowedAttribute(): void
    {
        $this->expectException(ExternReadableRelationshipSchemaVerificationException::class);
        $this->schemaPathProcessor->verifyExternReadablePath($this->authorType, ['books', 'title'], false);
    }
}
