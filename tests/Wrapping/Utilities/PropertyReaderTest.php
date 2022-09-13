<?php

declare(strict_types=1);

namespace Tests\Wrapping\Utilities;

use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use EDT\Wrapping\Utilities\PropertyReader;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
use Tests\data\Model\Person;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BookType;
use Tests\ModelBasedTest;

class PropertyReaderTest extends ModelBasedTest
{
    /**
     * @var WrapperFactoryInterface
     */
    private $nonWrappingWrapperFactory;
    /**
     * @var AuthorType
     */
    private $authorType;
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;
    /**
     * @var TypeProviderInterface
     */
    private $typeProvider;
    /**
     * @var SchemaPathProcessor
     */
    private $schemaPathProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->nonWrappingWrapperFactory = new class implements WrapperFactoryInterface {
            public function createWrapper(object $object, ReadableTypeInterface $type)
            {
                return $object;
            }
        };
        $conditionFactory = new PhpConditionFactory();
        $this->authorType = new AuthorType($conditionFactory);
        $bookType = new BookType($conditionFactory);
        $this->propertyAccessor = new ReflectionPropertyAccessor();
        $this->typeProvider = new PrefilledTypeProvider([
            $this->authorType,
            $bookType,
        ]);
        $this->schemaPathProcessor = new SchemaPathProcessor($this->typeProvider);
    }

    public function testInternalConditionAliasWithoutAccess(): void
    {
        $propertyReader = new PropertyReader(
            $this->propertyAccessor,
            $this->schemaPathProcessor
        );
        /** @var Person $author */
        $author = $this->authors['phen'];

        $value = $propertyReader->determineValue(
            $this->nonWrappingWrapperFactory,
            $this->authorType,
            $author
        );

        self::assertNull($value);
    }

    public function testInternalConditionAliasWithAccess(): void
    {
        $propertyReader = new PropertyReader(
            $this->propertyAccessor,
            $this->schemaPathProcessor
        );
        /** @var Person $author */
        $author = $this->authors['tolkien'];

        $value = $propertyReader->determineValue(
            $this->nonWrappingWrapperFactory,
            $this->authorType,
            $author
        );

        self::assertSame($author, $value);
    }
}

