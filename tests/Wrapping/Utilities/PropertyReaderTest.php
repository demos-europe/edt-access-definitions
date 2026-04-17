<?php

declare(strict_types=1);

namespace Tests\Wrapping\Utilities;

use EDT\JsonApi\ApiDocumentation\AttributeTypeResolver;
use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Wrapping\TypeProviders\LazyTypeProvider;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use InvalidArgumentException;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BookType;
use Tests\ModelBasedTest;

class PropertyReaderTest extends ModelBasedTest
{
    private AuthorType $authorType;

    private PropertyAccessorInterface $propertyAccessor;

    protected function setUp(): void
    {
        parent::setUp();
        $conditionFactory = new PhpConditionFactory();
        $lazyTypeProvider = new LazyTypeProvider();
        $this->propertyAccessor = new ReflectionPropertyAccessor();
        $typeResolver = new AttributeTypeResolver();
        $this->authorType = new AuthorType(
            $conditionFactory,
            $lazyTypeProvider,
            $this->propertyAccessor,
            $typeResolver
        );
        $bookType = new BookType($conditionFactory, $lazyTypeProvider, $this->propertyAccessor, $typeResolver);
        $typeProvider = new PrefilledTypeProvider([$this->authorType, $bookType]);
        $lazyTypeProvider->setAllTypes($typeProvider);
    }

    public function testInternalConditionAliasWithoutAccess(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $author = $this->authors['phen'];
        $this->authorType->assertMatchingEntity($author, []);
    }

    public function testInternalConditionAliasWithAccess(): void
    {
        $author = $this->authors['tolkien'];
        $this->authorType->assertMatchingEntity($author, []);

        self::assertTrue(true);
    }
}

