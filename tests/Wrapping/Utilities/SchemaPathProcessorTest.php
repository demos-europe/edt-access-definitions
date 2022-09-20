<?php

declare(strict_types=1);

namespace Tests\Wrapping\Utilities;

use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\Contracts\PropertyPathAccessInterface;
use EDT\Querying\Functions\Property;
use EDT\Querying\PropertyPaths\PropertyPath;
use EDT\Querying\SortMethodFactories\PhpSortMethodFactory;
use EDT\Querying\SortMethods\Ascending;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use EDT\Wrapping\Utilities\PropertyPathProcessorFactory;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
use InvalidArgumentException;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BirthType;
use Tests\ModelBasedTest;

class SchemaPathProcessorTest extends ModelBasedTest
{
    /**
     * @var SchemaPathProcessor
     */
    private $schemaPathProcessor;
    /**
     * @var AuthorType
     */
    private $authorType;
    /**
     * @var PhpSortMethodFactory
     */
    private $sortMethodFactory;

    /**
     * @var BirthType
     */
    private $birthType;

    protected function setUp(): void
    {
        parent::setUp();
        $conditionFactory = new PhpConditionFactory();
        $this->sortMethodFactory = new PhpSortMethodFactory();
        $this->authorType = new AuthorType($conditionFactory);
        $this->birthType = new BirthType($conditionFactory);
        $typeProvider = new PrefilledTypeProvider([
            $this->authorType,
        ]);
        $this->schemaPathProcessor = new SchemaPathProcessor(new PropertyPathProcessorFactory(), $typeProvider);
    }

    public function testSortAccessException(): void
    {
        $this->expectException(AccessException::class);
        $this->expectExceptionMessage("Access with the path 'foo.bar' into the type class 'Tests\data\Types\AuthorType' was denied because of the path segment 'foo'.");
        $this->schemaPathProcessor->mapSortMethods($this->authorType, $this->sortMethodFactory->propertyAscending('foo', 'bar'));
    }

    public function testNonSortableType(): void
    {
        $this->expectException(AccessException::class);
        $this->expectExceptionMessage("Methods for sorting were provided but the type class 'Tests\data\Types\BirthType' is not sortable.");
        $this->schemaPathProcessor->mapSortMethods($this->birthType, $this->sortMethodFactory->propertyAscending('foo', 'bar'));
    }

    public function testEmptyPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Property path must not be empty.');

        $propertyPath = new class(
            null,
            '',
            PropertyPathAccessInterface::UNPACK_RECURSIVE,
            'foo'
        ) extends PropertyPath {
            public function getAsNames(): array
            {
                return [];
            }
        };
        $sortMethod = new Ascending(new Property($propertyPath));

        $this->schemaPathProcessor->mapSortMethods($this->authorType, $sortMethod);
    }
}
