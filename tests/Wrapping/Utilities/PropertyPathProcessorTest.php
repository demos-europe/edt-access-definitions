<?php

declare(strict_types=1);

namespace Tests\Wrapping\Utilities;

use EDT\JsonApi\ApiDocumentation\AttributeTypeResolver;
use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\TypeProviders\LazyTypeProvider;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use EDT\Wrapping\Utilities\PropertyPathProcessor;
use EDT\Wrapping\Utilities\TypeAccessors\ExternSortableProcessorConfig;
use PHPUnit\Framework\TestCase;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BirthType;
use Tests\data\Types\BookType;

class PropertyPathProcessorTest extends TestCase
{
    private BookType $bookType;

    private AuthorType $authorType;

    private PrefilledTypeProvider $typeProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $conditionFactory = new PhpConditionFactory();
        $lazyTypeProvider = new LazyTypeProvider();
        $propertyAccessor = new ReflectionPropertyAccessor();
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

    public function testProcessPropertyPathWithRelationshipAfterAttribute(): void
    {
        $this->expectException(PropertyAccessException::class);
        $processorConfig = new ExternSortableProcessorConfig($this->typeProvider, $this->bookType);
        $propertyPathProcessor = new PropertyPathProcessor($processorConfig);
        $propertyPathProcessor->processPropertyPath(
            $this->bookType,
            [],
            'title',
            'foobar'
        );
    }
}
