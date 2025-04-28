<?php

declare(strict_types=1);

namespace Tests\Utilities;

use EDT\JsonApi\ApiDocumentation\AttributeTypeResolver;
use EDT\JsonApi\Utilities\PropertyBuilderFactory;
use EDT\Querying\PropertyAccessors\ReflectionPropertyAccessor;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Tests\data\DqlModel\Person;
use Tests\data\Model\Book;

class PropertyBuilderFactoryTest extends TestCase
{
    private ReflectionPropertyAccessor $accessor;
    private AttributeTypeResolver $typeResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accessor = new ReflectionPropertyAccessor();
        $this->typeResolver = new AttributeTypeResolver();
    }

    /**
     * @dataProvider getTestAttributeData
     */
    public function testProperties(bool $sortable, bool $filterable, bool $readable): void
    {
        $factory = new PropertyBuilderFactory(
            $this->accessor,
            $this->typeResolver,
            $sortable,
            $filterable,
            $sortable,
            $filterable,
            $readable,
            $sortable,
            $filterable,
            $readable,
            $sortable,
            $filterable,
            $readable
        );

        $identifier = $factory->createIdentifier(Book::class);
        $attribute = $factory->createAttribute(Book::class, 'title');
        $toOneRelationship = $factory->createToOneWithType(Book::class, Person::class, 'author');
        $toManyRelationship = $factory->createToManyWithType(Person::class, Book::class, 'books');

        $properties = [
            'sortable' => $sortable,
            'filterable' => $filterable,
        ];

        foreach ($properties as $propertyName => $enabled) {
            $reflectionProperty = new ReflectionProperty($identifier, $propertyName);
            $reflectionProperty->setAccessible(true);
            $value = $reflectionProperty->getValue($identifier);
            self::assertSame($enabled, $value);
        }

        // test access
        foreach ([$attribute, $toOneRelationship, $toManyRelationship] as $property) {
            foreach ($properties as $propertyName => $enabled) {
                $reflectionProperty = new ReflectionProperty($property, $propertyName);
                $reflectionProperty->setAccessible(true);
                $value = $reflectionProperty->getValue($property);
                self::assertSame($enabled, $value);
            }

            // test to-many relationship
            $reflectionProperty = new ReflectionProperty($property, 'readabilityFactory');
            $reflectionProperty->setAccessible(true);
            $value = $reflectionProperty->getValue($property);
            if ($readable) {
                self::assertNotNull($value);
            } else {
                self::assertNull($value);
            }
        }


    }

    public static function getTestAttributeData(): array
    {
        return [
            [true, true, true],
            [true, true, false],
            [true, false, true],
            [true, false, false],
            [false, true, true],
            [false, true, false],
            [false, false, true],
            [false, false, false],
        ];
    }
}
