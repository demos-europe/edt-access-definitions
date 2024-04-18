<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Attribute;

use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\AbstractConstructorBehavior;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;
use function array_key_exists;

class AttributeConstructorBehavior extends AbstractConstructorBehavior
{
    /**
     * @param non-empty-string|null $argumentName
     * @param null|callable(CreationDataInterface): array{mixed, list<non-empty-string>} $customBehavior
     */
    public static function createFactory(?string $argumentName, OptionalField $optional, ?callable $customBehavior): ConstructorBehaviorFactoryInterface
    {
        return new class($argumentName, $optional, $customBehavior) implements ConstructorBehaviorFactoryInterface {
            /**
             * @param non-empty-string|null $argumentName
             * @param null|callable(CreationDataInterface): array{mixed, list<non-empty-string>} $customBehavior
             */
            public function __construct(
                protected readonly ?string $argumentName,
                protected readonly OptionalField $optional,
                protected readonly mixed $customBehavior
            ) {}

            public function __invoke(string $name, array $propertyPath, string $entityClass): ConstructorBehaviorInterface
            {
                return new AttributeConstructorBehavior(
                    $name,
                    $this->argumentName ?? $name,
                    $this->optional,
                    $this->customBehavior
                );
            }

            public function createConstructorBehavior(string $name, array $propertyPath, string $entityClass): ConstructorBehaviorInterface
            {
                return $this($name, $propertyPath, $entityClass);
            }
        };
    }

    protected function isValueInRequest(CreationDataInterface $entityData): bool
    {
        return array_key_exists($this->resourcePropertyName, $entityData->getAttributes());
    }

    protected function getArgumentValueFromRequest(CreationDataInterface $entityData): mixed
    {
        return $entityData->getAttributes()[$this->resourcePropertyName];
    }

    public function getRequiredAttributes(): array
    {
        return $this->getRequiredPropertyList();
    }

    public function getOptionalAttributes(): array
    {
        return $this->getOptionalPropertyList();
    }
}
