<?php

declare(strict_types=1);

namespace Tests\Wrapping\TypeProviders;

use EDT\ConditionFactory\ConditionFactory;
use EDT\JsonApi\ApiDocumentation\AttributeTypeResolver;
use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Wrapping\TypeProviders\LazyTypeProvider;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use PHPUnit\Framework\TestCase;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BookType;

class PrefilledTypeProviderTest extends TestCase
{
    protected PrefilledTypeProvider $typeProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $conditionFactory = new ConditionFactory();
        $lazyTypeProvider = new LazyTypeProvider();
        $propertyAccessor = new ReflectionPropertyAccessor();
        $typeResolver = new AttributeTypeResolver();
        $authorType = new AuthorType($conditionFactory, $lazyTypeProvider, $propertyAccessor, $typeResolver);
        $bookType = new BookType($conditionFactory, $lazyTypeProvider, $propertyAccessor, $typeResolver);
        $this->typeProvider = new PrefilledTypeProvider([
            $authorType,
            $bookType,
        ]);
        $lazyTypeProvider->setAllTypes($this->typeProvider);
    }

    public function testUnknownTypeIdentifier(): void
    {
        $type = $this->typeProvider->getTypeByIdentifier('foobar');
        self::assertNull($type);
    }
}
