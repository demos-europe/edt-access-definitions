<?php

declare(strict_types=1);

namespace Tests\Wrapping\Utilities;

use EDT\JsonApi\ApiDocumentation\AttributeTypeResolver;
use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\Contracts\PropertyPathAccessInterface;
use EDT\Querying\Functions\Property;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Querying\PropertyPaths\PropertyPath;
use EDT\Querying\SortMethodFactories\PhpSortMethodFactory;
use EDT\Querying\SortMethods\Ascending;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\TypeProviders\LazyTypeProvider;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use EDT\Wrapping\Utilities\PropertyPathProcessorFactory;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BirthType;
use Tests\data\Types\BookType;
use Tests\ModelBasedTest;

class SchemaPathProcessorTest extends ModelBasedTest
{
    private SchemaPathProcessor $schemaPathProcessor;

    private AuthorType $authorType;

    private PhpSortMethodFactory $sortMethodFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sortMethodFactory = new PhpSortMethodFactory();
        $conditionFactory = new PhpConditionFactory();
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
        $this->expectException(AccessException::class);
        $this->expectExceptionMessage("Access with the path 'foo.bar' into the type class 'Tests\data\Types\AuthorType' was denied.");
        $invalidSortMethod = $this->sortMethodFactory->propertyAscending(['foo', 'bar']);
        $this->schemaPathProcessor->mapSorting($this->authorType, [$invalidSortMethod]);
    }

    public function testMapSorting(): void
    {
        $propertyPath = new PropertyPath(null, '', PropertyPathAccessInterface::UNPACK_RECURSIVE, ['name']);
        $sortMethod = new Ascending(new Property($propertyPath));
        $this->schemaPathProcessor->mapSorting($this->authorType, [$sortMethod]);
        $paths = $sortMethod->getPropertyPaths();
        self::assertCount(1, $paths);
        $path = array_pop($paths);
        self::assertSame(['name'], $path->getPath()->getAsNames());
    }


    public function testProcessPropertyPathWithAllowedAttribute(): void
    {
        $this->expectNotToPerformAssertions();
        $this->schemaPathProcessor->verifyExternReadablePath($this->authorType, ['books', 'title'], true);
    }

    public function testProcessPropertyPathWithNonAllowedAttribute(): void
    {
        $this->expectException(AccessException::class);
        $this->schemaPathProcessor->verifyExternReadablePath($this->authorType, ['books', 'title'], false);
    }
}
