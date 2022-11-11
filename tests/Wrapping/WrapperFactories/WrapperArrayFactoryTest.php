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
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\TypeProviders\LazyTypeProvider;
use EDT\Wrapping\Utilities\PropertyPathProcessorFactory;
use EDT\Wrapping\Utilities\PropertyReader;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
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

    private PropertyReader $propertyReader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conditionFactory = new PhpConditionFactory();
        $lazyTypeProvider = new LazyTypeProvider();
        $this->authorType = new AuthorType($this->conditionFactory, $lazyTypeProvider);
        $bookType = new BookType($this->conditionFactory, $lazyTypeProvider);
        $this->typeProvider = new PrefilledTypeProvider([
            $this->authorType,
            $bookType,
            new BirthType($this->conditionFactory),
        ]);
        $lazyTypeProvider->setAllTypes($this->typeProvider);
        $this->propertyAccessor = new ReflectionPropertyAccessor();
        $this->authorProvider = new PrefilledObjectProvider(
            new ConditionEvaluator(new TableJoiner($this->propertyAccessor)),
            new Sorter(new TableJoiner($this->propertyAccessor)),
            $this->authors
        );
        $this->schemaPathProcessor = new SchemaPathProcessor(new PropertyPathProcessorFactory(), $this->typeProvider);
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

    private function createWrapperArrayFactory(int $depth): WrapperArrayFactory
    {
        return new WrapperArrayFactory($this->propertyAccessor, $this->propertyReader, $depth);
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
        $this->schemaPathProcessor->mapFilterConditions($type, $conditions);
        $conditions[] = $this->schemaPathProcessor->processAccessCondition($type);

        return $this->authorProvider->getEntities($conditions, [], null);
    }

    /**
     * @param IdentifiableTypeInterface&TransferableTypeInterface $type
     * @param non-empty-string                                    $identifier
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

