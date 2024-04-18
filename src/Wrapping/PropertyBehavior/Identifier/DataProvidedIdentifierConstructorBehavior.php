<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier;

use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Wrapping\Contracts\ContentField;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\AbstractConstructorBehavior;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Identifier\Factory\IdentifierConstructorBehaviorFactoryInterface;

class DataProvidedIdentifierConstructorBehavior extends AbstractConstructorBehavior
{
    /**
     * @param non-empty-string $argumentName
     * @param null|callable(CreationDataInterface): array{mixed, list<non-empty-string>} $customBehavior
     */
    public function __construct(string $argumentName, OptionalField $optional, ?callable $customBehavior)
    {
        parent::__construct(ContentField::ID, $argumentName, $optional, $customBehavior);
    }

    /**
     * @param non-empty-string|null $argumentName
     * @param null|callable(CreationDataInterface): array{mixed, list<non-empty-string>} $customBehavior
     */
    public static function createFactory(?string $argumentName, OptionalField $optional, ?callable $customBehavior): IdentifierConstructorBehaviorFactoryInterface
    {
        return new class($argumentName, $optional, $customBehavior) implements IdentifierConstructorBehaviorFactoryInterface {
            /**
             * @param non-empty-string|null $argumentName
             * @param null|callable(CreationDataInterface): array{mixed, list<non-empty-string>} $customBehavior
             */
            public function __construct(
                protected readonly ?string $argumentName,
                protected readonly OptionalField $optional,
                protected readonly mixed $customBehavior
            ) {}

            public function __invoke(array $propertyPath, string $entityClass): ConstructorBehaviorInterface
            {
                return new DataProvidedIdentifierConstructorBehavior($this->argumentName ?? ContentField::ID, $this->optional, $this->customBehavior);
            }

            public function createIdentifierConstructorBehavior(array $propertyPath, string $entityClass): ConstructorBehaviorInterface
            {
                return $this($propertyPath, $entityClass);
            }
        };
    }

    protected function isValueInRequest(CreationDataInterface $entityData): bool
    {
        return null !== $entityData->getEntityIdentifier();
    }

    protected function getArgumentValueFromRequest(CreationDataInterface $entityData): mixed
    {
        return $entityData->getEntityIdentifier();
    }

    public function isIdRequired(): bool
    {
        return $this->optional->equals(OptionalField::NO);
    }

    public function isIdOptional(): bool
    {
        return $this->optional->equals(OptionalField::YES);
    }
}
