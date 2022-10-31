<?php

declare(strict_types=1);

namespace Tests\Wrapping\Utilities;

use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\RelationshipAccessException;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use EDT\Wrapping\Utilities\PropertyPathProcessor;
use EDT\Wrapping\Utilities\TypeAccessors\ExternReadableProcessorConfig;
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
        $this->bookType = new BookType($conditionFactory);
        $this->authorType = new AuthorType($conditionFactory);
        $this->typeProvider = new PrefilledTypeProvider([
            $this->bookType,
            $this->authorType,
            new BirthType($conditionFactory),
        ]);
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

    public function testProcessPropertyPathWithNonAvailableType(): void
    {
        $this->expectException(RelationshipAccessException::class);
        $processorConfig = new ExternReadableProcessorConfig($this->typeProvider, $this->authorType, true);
        $propertyPathProcessor = new PropertyPathProcessor($processorConfig);
        $propertyPathProcessor->processPropertyPath(
            $this->authorType,
            [],
            'birth',
        );
    }

    public function testProcessPropertyPathWithAllowedAttribute(): void
    {
        $processorConfig = new ExternReadableProcessorConfig($this->typeProvider, $this->authorType, true);
        $propertyPathProcessor = new PropertyPathProcessor($processorConfig);
        $processedPath = $propertyPathProcessor->processPropertyPath(
            $this->authorType,
            [],
            'books',
            'title'
        );
        self::assertSame(['books', 'title'], $processedPath);
    }

    public function testProcessPropertyPathWithNonAllowedAttribute(): void
    {
        $this->expectException(PropertyAccessException::class);
        $processorConfig = new ExternReadableProcessorConfig($this->typeProvider, $this->authorType, false);
        $propertyPathProcessor = new PropertyPathProcessor($processorConfig);
        $propertyPathProcessor->processPropertyPath(
            $this->authorType,
            [],
            'books',
            'title'
        );
    }
}
