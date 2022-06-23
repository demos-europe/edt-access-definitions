<?php

declare(strict_types=1);

namespace Tests\Wrapping\Utilities;

use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\SortMethodFactories\PhpSortMethodFactory;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
use Tests\data\Types\AuthorType;
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

    protected function setUp(): void
    {
        parent::setUp();
        $conditionFactory = new PhpConditionFactory();
        $this->sortMethodFactory = new PhpSortMethodFactory();
        $this->authorType = new AuthorType($conditionFactory);
        $typeProvider = new PrefilledTypeProvider([
            $this->authorType,
        ]);
        $this->schemaPathProcessor = new SchemaPathProcessor($typeProvider);
    }

    public function testSortAccessException(): void
    {
        $this->expectException(AccessException::class);
        $this->expectExceptionMessage("Access with the path 'foo.bar' into the type class 'Tests\data\Types\AuthorType' was denied because of the path segment 'foo'.");
        $this->schemaPathProcessor->mapSortMethods($this->authorType, $this->sortMethodFactory->propertyAscending('foo', 'bar'));
    }
}
