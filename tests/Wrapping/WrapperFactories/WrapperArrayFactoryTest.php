<?php

declare(strict_types=1);

namespace Tests\Wrapping\WrapperFactories;

use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use EDT\Querying\Utilities\ConditionEvaluator;
use EDT\Querying\Utilities\Sorter;
use EDT\Querying\Utilities\TableJoiner;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\Types\FilterableTypeInterface;
use EDT\Wrapping\Contracts\Types\IdentifiableTypeInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Utilities\PropertyPathProcessorFactory;
use EDT\Wrapping\Utilities\PropertyReader;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
use EDT\Wrapping\Utilities\TypeAccessor;
use EDT\Wrapping\WrapperFactories\WrapperArrayFactory;
use EDT\Querying\ObjectProviders\PrefilledObjectProvider;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use ReflectionClass;
use Tests\data\Model\Person;
use Tests\data\Types\BirthType;
use Tests\ModelBasedTest;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BookType;

class WrapperArrayFactoryTest extends ModelBasedTest
{
    private AuthorType $authorType;

    private PhpConditionFactory $conditionFactory;

    /**
     * @var PrefilledObjectProvider<Person>
     */
    private PrefilledObjectProvider $authorProvider;

    private PrefilledTypeProvider $typeProvider;

    private ReflectionPropertyAccessor $propertyAccessor;

    private SchemaPathProcessor $schemaPathProcessor;

    private TypeAccessor $typeAccessor;

    private PropertyReader $propertyReader;

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
        $this->authorProvider = new PrefilledObjectProvider(
            new ConditionEvaluator(new TableJoiner($this->propertyAccessor)),
            new Sorter(new TableJoiner($this->propertyAccessor)),
            $this->authors
        );
        $this->schemaPathProcessor = new SchemaPathProcessor(new PropertyPathProcessorFactory(), $this->typeProvider);
        $this->typeAccessor = new TypeAccessor($this->typeProvider);
        $tableJoiner = new TableJoiner($this->propertyAccessor);
        $conditionEvaluator = new ConditionEvaluator($tableJoiner);
        $sorter = new Sorter($tableJoiner);
        $this->propertyReader = new PropertyReader($this->schemaPathProcessor, $conditionEvaluator, $sorter);
    }

    public function testTrue(): void
    {
        self::assertTrue(true);
    }

    public function testListBackingObjectsUnrestricted(): void
    {
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('man', 'pseudonym');
        $filteredAuthors = $this->listEntities($this->authorType, [$hasString]);
        self::assertCount(1, $filteredAuthors);
        $author = array_pop($filteredAuthors);
        self::assertSame($this->authors['king'], $author);
    }

    public function testListWrappersDepthZero(): void
    {
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('man', 'pseudonym');
        $filteredAuthors = $this->listEntities($this->authorType, [$hasString]);
        $filteredAuthors = $this->createArrayWrappers($filteredAuthors, $this->authorType, 0);

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
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('man', 'pseudonym');
        $filteredAuthors = $this->listEntities($this->authorType, [$hasString]);
        $filteredAuthors = $this->createArrayWrappers($filteredAuthors, $this->authorType, 1);

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
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('man', 'pseudonym');
        $filteredAuthors = $this->listEntities($this->authorType, [$hasString]);
        $filteredAuthors = $this->createArrayWrappers($filteredAuthors, $this->authorType, -1);

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
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('man', 'pseudonym');
        $filteredAuthors = $this->listEntities($this->authorType, [$hasString]);
        $filteredAuthors = $this->createArrayWrappers($filteredAuthors, $this->authorType, 2);

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
        $hasString = $this->conditionFactory->propertyHasStringContainingCaseInsensitiveValue('Oranje', 'birthCountry');
        $filteredAuthors = $this->listEntities($this->authorType, [$hasString]);
        $filteredAuthors = $this->createArrayWrappers($filteredAuthors, $this->authorType, 0);

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
        $fetchedAuthor = $this->getEntityByIdentifier($this->authorType,'John Ronald Reuel Tolkien');

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
        $fetchedAuthor = $this->getEntityByIdentifier($this->authorType,'John Ronald Reuel Tolkien', false);

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
        // Set the return of isAvailable to `false` to simulate being able to access
        // AuthorType but not BookType.
        $bookType = $this->typeProvider->requestType(BookType::class)->getTypeInstance();
        $bookTypeReflection = new ReflectionClass($bookType);
        $property = $bookTypeReflection->getProperty('available');
        $property->setAccessible(true);
        $property->setValue($bookType, false);
        $type = $this->typeProvider->requestType(BookType::class)->getTypeInstance();
        self::assertFalse($type->isAvailable());

        // When fetching the AuthorType::getAccessCondition method is automatically executed, in which
        // a path uses the BookType. This automatic check must not fail due to missing availability
        // of the BookType for the requesting user.
        $fetchedAuthor = $this->getEntityByIdentifier($this->authorType,'John Ronald Reuel Tolkien');

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

    private function createArrayWrappers(array $entities, $type, int $depth): array
    {
        $wrapper = $this->createWrapperArrayFactory($depth);
        return array_values(array_map(
            static fn (object $entity) => $wrapper->createWrapper($entity, $type),
            $entities
        ));
    }

    private function listEntities(FilterableTypeInterface $type, array $conditions): array
    {
        if (!$type->isAvailable()) {
            throw AccessException::typeNotAvailable($type);
        }

        $this->schemaPathProcessor->mapFilterConditions($type, $conditions);
        $conditions[] = $this->schemaPathProcessor->processAccessCondition($type);

        return $this->authorProvider->getEntities($conditions, [], null);
    }

    /**
     * @param IdentifiableTypeInterface&ReadableTypeInterface $type
     * @param non-empty-string $identifier
     */
    public function getEntityByIdentifier(IdentifiableTypeInterface $type, string $identifier, bool $wrap = true)
    {
        $identifierPath = $type->getIdentifierPropertyPath();
        $identifierCondition = $this->conditionFactory->propertyHasValue($identifier, ...$identifierPath);
        $entities = $this->listEntities($type, [$identifierCondition]);
        if ($wrap) {
            $entities = $this->createArrayWrappers($entities, $type, 0);
        }

        switch (count($entities)) {
            case 0:
                throw AccessException::noEntityByIdentifier($type);
            case 1:
                return array_pop($entities);
            default:
                throw AccessException::multipleEntitiesByIdentifier($type);
        }
    }
}

