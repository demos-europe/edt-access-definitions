<?php

declare(strict_types=1);

namespace Tests\Wrapping\EntityFetchers;

use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Contracts\WrapperFactoryInterface;
use EDT\Wrapping\Utilities\PropertyReader;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
use EDT\Wrapping\Utilities\TypeAccessor;
use EDT\Wrapping\WrapperFactories\WrapperArrayFactory;
use EDT\Querying\ObjectProviders\PrefilledObjectProvider;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use EDT\Wrapping\Utilities\GenericEntityFetcher;
use ReflectionClass;
use Tests\data\Model\Person;
use Tests\data\Types\BirthType;
use Tests\ModelBasedTest;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BookType;

class GenericEntityFetcherTest extends ModelBasedTest
{
    /**
     * @var AuthorType
     */
    private $authorType;
    /**
     * @var PhpConditionFactory
     */
    private $conditionFactory;
    /**
     * @var PrefilledObjectProvider<Person>
     */
    private $authorProvider;
    /**
     * @var PrefilledTypeProvider
     */
    private $typeProvider;
    /**
     * @var ReflectionPropertyAccessor
     */
    private $propertyAccessor;
    /**
     * @var WrapperFactoryInterface
     */
    private $nonWrappingWrapperFactory;
    /**
     * @var SchemaPathProcessor
     */
    private $schemaPathProcessor;
    /**
     * @var TypeAccessor
     */
    private $typeAccessor;
    /**
     * @var PropertyReader
     */
    private $propertyReader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conditionFactory = new PhpConditionFactory();
        $this->authorType = new AuthorType($this->conditionFactory);
        $bookType = new BookType($this->conditionFactory);
        $this->typeProvider = new PrefilledTypeProvider([
            $this->authorType,
            $bookType,
            new BirthType($this->conditionFactory),
        ]);
        $this->propertyAccessor = new ReflectionPropertyAccessor();
        $this->authorProvider = new PrefilledObjectProvider($this->propertyAccessor, $this->authors);
        $this->nonWrappingWrapperFactory = new class implements WrapperFactoryInterface {
            public function createWrapper(object $object, ReadableTypeInterface $type): object
            {
                return $object;
            }
        };
        $this->schemaPathProcessor = new SchemaPathProcessor($this->typeProvider);
        $this->typeAccessor = new TypeAccessor($this->typeProvider);
        $this->propertyReader = new PropertyReader($this->propertyAccessor, $this->schemaPathProcessor);
    }

    public function testTrue(): void
    {
        self::assertTrue(true);
    }

    public function testListBackingObjectsUnrestricted(): void
    {
        $entityFetcher = $this->createGenericEntityFetcher($this->nonWrappingWrapperFactory);
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('man', 'pseudonym');
        $filteredAuthors = $entityFetcher->listEntities($this->authorType, [$hasString]);
        self::assertCount(1, $filteredAuthors);
        $author = array_pop($filteredAuthors);
        self::assertSame($this->authors['king'], $author);
    }

    public function testListWrappersDepthZero(): void
    {
        $wrapperFactory = $this->createWrapperArrayFactory(0);
        $entityFetcher = $this->createGenericEntityFetcher($wrapperFactory);
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('man', 'pseudonym');
        $filteredAuthors = $entityFetcher->listEntities($this->authorType, [$hasString]);

        $expected = [
            0 => [
                'name'         => 'Stephen King',
                'pseudonym'    => 'Richard Bachman',
                'birthCountry' => 'USA',
                'books'        => [
                    0 => null,
                ],
            ],
        ];

        self::assertEquals($expected, $filteredAuthors);
    }

    public function testListWrappersDepthOne(): void
    {
        $wrapperFactory = $this->createWrapperArrayFactory(1);
        $entityFetcher = $this->createGenericEntityFetcher($wrapperFactory);
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('man', 'pseudonym');
        $filteredAuthors = $entityFetcher->listEntities($this->authorType, [$hasString]);

        $expected = [
            0 => [
                'name'         => 'Stephen King',
                'pseudonym'    => 'Richard Bachman',
                'birthCountry' => 'USA',
                'books'        => [
                    0 => [
                        'author' => null,
                        'tags'   => [],
                        'title'  => 'Doctor Sleep',
                    ],
                ],
            ],
        ];

        self::assertEquals($expected, $filteredAuthors);
    }

    public function testListWrappersDepthNegative(): void
    {
        $wrapperFactory = $this->createWrapperArrayFactory(-1);
        $entityFetcher = $this->createGenericEntityFetcher($wrapperFactory);
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('man', 'pseudonym');
        $filteredAuthors = $entityFetcher->listEntities($this->authorType, [$hasString]);

        $expected = [
            0 => [
                'name'         => 'Stephen King',
                'pseudonym'    => 'Richard Bachman',
                'birthCountry' => 'USA',
                'books'        => [
                    0 => null,
                ],
            ],
        ];

        self::assertEquals($expected, $filteredAuthors);
    }

    public function testListWrappersDepthTwo(): void
    {
        $wrapperFactory = $this->createWrapperArrayFactory(2);
        $entityFetcher = $this->createGenericEntityFetcher($wrapperFactory);
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('man', 'pseudonym');
        $filteredAuthors = $entityFetcher->listEntities($this->authorType, [$hasString]);

        $expected = [
            0 => [
                'name'         => 'Stephen King',
                'pseudonym'    => 'Richard Bachman',
                'birthCountry' => 'USA',
                'books'        => [
                    0 => [
                        'author' => [
                            'name'         => 'Stephen King',
                            'pseudonym'    => 'Richard Bachman',
                            'birthCountry' => 'USA',
                            'books'        => [
                                0 => null,
                            ],
                        ],
                        'tags'   => [],
                        'title'  => 'Doctor Sleep',
                    ],
                ],
            ],
        ];

        self::assertEquals($expected, $filteredAuthors);
    }

    public function testListWrappersWithMapping(): void
    {
        $wrapperFactory = $this->createWrapperArrayFactory(0);
        $entityFetcher = $this->createGenericEntityFetcher($wrapperFactory);
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('Oranje', 'birthCountry');
        $filteredAuthors = $entityFetcher->listEntities($this->authorType, [$hasString]);

        $expected = [
            0 => [
                'name'         => 'John Ronald Reuel Tolkien',
                'pseudonym'    => null,
                'birthCountry' => 'Oranje-Freistaat',
                'books'        => [
                    0 => null,
                ],
            ],
        ];

        self::assertEquals($expected, $filteredAuthors);
    }

    public function testGetAuthorWrapper(): void
    {
        $wrapperFactory = $this->createWrapperArrayFactory(0);
        $entityFetcher = $this->createGenericEntityFetcher($wrapperFactory);
        $fetchedAuthor = $entityFetcher->getEntityByIdentifier($this->authorType,'John Ronald Reuel Tolkien');

        $expected = [
            'name'         => 'John Ronald Reuel Tolkien',
            'pseudonym'    => null,
            'birthCountry' => 'Oranje-Freistaat',
            'books'        => [
                0 => null,
            ],
        ];

        self::assertEquals($expected, $fetchedAuthor);
    }

    public function testGetAuthorObject(): void
    {
        $entityFetcher = $this->createGenericEntityFetcher($this->nonWrappingWrapperFactory);
        $fetchedAuthor = $entityFetcher->getEntityByIdentifier($this->authorType,'John Ronald Reuel Tolkien');

        self::assertSame($this->authors['tolkien'], $fetchedAuthor);
    }

    /**
     * When {@link TypeInterface::getAccessCondition()} is processed the paths must not be
     * checked against {@link TypeInterface::isAvailable()}. Otherwise a user may request
     * data without provoking any violation and still get an exception because an internal
     * check in {@link TypeInterface::getAccessCondition()} accessed a {@link TypeInterface type}
     * he doesn't have access to.
     *
     * Like with the skipped check for
     * {@link ReadableTypeInterface::getReadableProperties()} we expect the developer to know
     * what he does when implementing {@link TypeInterface::getAccessCondition()}.
     */
    public function testInternalIsAvailable(): void
    {
        // Set the return of isAvailable to false to simulate being able to access
        // AuthorType but not BookType.
        $bookType = $this->typeProvider->getType(BookType::class);
        $bookTypeReflection = new ReflectionClass($bookType);
        $property = $bookTypeReflection->getProperty('available');
        $property->setAccessible(true);
        $property->setValue($bookType, false);
        self::assertFalse($this->typeProvider->getType(BookType::class)->isAvailable());

        // When fetching the AuthorType::getAccessCondition method is automatically executed, in which
        // a path uses the BookType. This automatic check must not fail due to missing availability
        // of the BookType for the requesting user.
        $wrapperFactory = $this->createWrapperArrayFactory(0);
        $entityFetcher = $this->createGenericEntityFetcher($wrapperFactory);
        $fetchedAuthor = $entityFetcher->getEntityByIdentifier($this->authorType,'John Ronald Reuel Tolkien');

        $expected = [
            'name'         => 'John Ronald Reuel Tolkien',
            'pseudonym'    => null,
            'birthCountry' => 'Oranje-Freistaat',
        ];

        self::assertEquals($expected, $fetchedAuthor);
    }

    private function createWrapperArrayFactory(int $depth): WrapperArrayFactory
    {
        return new WrapperArrayFactory($this->propertyAccessor, $this->propertyReader, $this->typeAccessor, $depth);
    }

    private function createGenericEntityFetcher(WrapperFactoryInterface $wrapperFactory): GenericEntityFetcher
    {
        return new GenericEntityFetcher(
            $this->authorProvider,
            $this->conditionFactory,
            $this->schemaPathProcessor,
            $wrapperFactory
        );
    }
}

