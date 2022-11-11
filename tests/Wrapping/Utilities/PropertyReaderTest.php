<?php

declare(strict_types=1);

namespace Tests\Wrapping\Utilities;

use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Querying\Utilities\ConditionEvaluator;
use EDT\Querying\Utilities\Sorter;
use EDT\Querying\Utilities\TableJoiner;
use EDT\Wrapping\TypeProviders\LazyTypeProvider;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use EDT\Wrapping\Utilities\PropertyPathProcessorFactory;
use EDT\Wrapping\Utilities\PropertyReader;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
use Tests\data\Model\Person;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BookType;
use Tests\ModelBasedTest;

class PropertyReaderTest extends ModelBasedTest
{
    private AuthorType $authorType;

    private PropertyAccessorInterface $propertyAccessor;

    private SchemaPathProcessor $schemaPathProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $conditionFactory = new PhpConditionFactory();
        $lazyTypeProvider = new LazyTypeProvider();
        $this->authorType = new AuthorType($conditionFactory, $lazyTypeProvider);
        $bookType = new BookType($conditionFactory, $lazyTypeProvider);
        $this->propertyAccessor = new ReflectionPropertyAccessor();
        $typeProvider = new PrefilledTypeProvider([$this->authorType, $bookType]);
        $lazyTypeProvider->setAllTypes($typeProvider);
        $this->schemaPathProcessor = new SchemaPathProcessor(new PropertyPathProcessorFactory(), $typeProvider);
    }

    public function testInternalConditionAliasWithoutAccess(): void
    {
        $tableJoiner = new TableJoiner($this->propertyAccessor);
        $conditionEvaluator = new ConditionEvaluator($tableJoiner);
        $sorter = new Sorter($tableJoiner);
        $propertyReader = new PropertyReader(
            $this->schemaPathProcessor,
            $conditionEvaluator,
            $sorter
        );
        $author = $this->authors['phen'];

        $value = $propertyReader->determineToOneRelationshipValue($this->authorType, $author);

        self::assertNull($value);
    }

    public function testInternalConditionAliasWithAccess(): void
    {
        $tableJoiner = new TableJoiner($this->propertyAccessor);
        $conditionEvaluator = new ConditionEvaluator($tableJoiner);
        $sorter = new Sorter($tableJoiner);
        $propertyReader = new PropertyReader(
            $this->schemaPathProcessor,
            $conditionEvaluator,
            $sorter
        );
        $author = $this->authors['tolkien'];

        $value = $propertyReader->determineToOneRelationshipValue($this->authorType, $author);

        self::assertSame($author, $value);
    }
}

