<?php

declare(strict_types=1);

namespace Tests\Wrapping\WrapperFactories;

use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\Contracts\Types\IdentifiableTypeInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\UpdatableTypeInterface;
use EDT\Wrapping\Utilities\PropertyReader;
use EDT\Wrapping\Utilities\SchemaPathProcessor;
use EDT\Wrapping\Utilities\TypeAccessor;
use EDT\Wrapping\WrapperFactories\WrapperArrayFactory;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WrapperArrayFactoryTest extends TestCase
{
    public function testConstructorWithNegativeDepth(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $propertyPathReader = new class() implements PropertyAccessorInterface
        {
            public function getValueByPropertyPath(?object $target, string $property, string ...$properties)
            {
                throw new InvalidArgumentException('Should not be called');
            }
            public function getValuesByPropertyPath($target, int $depth, string $property, string ...$properties): array
            {
                throw new InvalidArgumentException('Should not be called');
            }
            public function setValue($target, $value, string $property): void
            {
                throw new InvalidArgumentException('Should not be called');
            }
        };
        $typeProvider = new class() implements TypeProviderInterface
        {
            public function getType(string $typeIdentifier, string ...$implementations): TypeInterface
            {
                throw new InvalidArgumentException('Should not be called');
            }
            public function getAvailableType(string $typeIdentifier, string ...$implementations): TypeInterface
            {
                throw new InvalidArgumentException('Should not be called');
            }
            public function isTypeAvailable(string $typeIdentifier, string ...$implementations): bool
            {
                throw new InvalidArgumentException('Should not be called');
            }
            public function getReadableAvailableType(string $typeIdentifier): ReadableTypeInterface
            {
                throw new InvalidArgumentException('Should not be called');
            }
            public function getUpdatableAvailableType(string $typeIdentifier): UpdatableTypeInterface
            {
                throw new InvalidArgumentException('Should not be called');
            }

            public function getReadableType(string $typeIdentifier): ReadableTypeInterface
            {
                throw new InvalidArgumentException('Should not be called');
            }

            public function getIdentifiableType(string $typeIdentifier): IdentifiableTypeInterface
            {
                throw new InvalidArgumentException('Should not be called');
            }
        };
        $schemaPathProcessor = new SchemaPathProcessor($typeProvider);
        $propertyReader = new PropertyReader($propertyPathReader, $schemaPathProcessor);
        $typeAccessor = new TypeAccessor($typeProvider);
        new WrapperArrayFactory($propertyPathReader, $propertyReader, $typeAccessor, -1);
    }
}
