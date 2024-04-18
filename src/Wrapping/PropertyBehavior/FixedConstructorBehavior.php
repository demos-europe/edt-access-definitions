<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\Wrapping\CreationDataInterface;

/**
 * This behavior will always trigger on creation requests, regardless of the properties present in the request.
 */
class FixedConstructorBehavior implements ConstructorBehaviorInterface
{
    use IdUnrelatedTrait;

    /**
     * @param non-empty-string $argumentName
     * @param callable(CreationDataInterface): array{mixed, list<non-empty-string>} $callback
     */
    public function __construct(
        protected readonly string $argumentName,
        protected readonly mixed $callback
    ) {}

    /**
     * @param callable(CreationDataInterface): array{mixed, list<non-empty-string>} $behavior
     */
    public static function createFactory(callable $behavior): ConstructorBehaviorFactoryInterface
    {
        return new class ($behavior) implements ConstructorBehaviorFactoryInterface {
            /**
             * @param callable(CreationDataInterface): array{mixed, list<non-empty-string>} $callback
             */
            public function __construct(protected readonly mixed $callback) {}

            public function __invoke(string $name, array $propertyPath, string $entityClass): ConstructorBehaviorInterface
            {
                return new FixedConstructorBehavior($name, $this->callback);
            }

            public function createConstructorBehavior(string $name, array $propertyPath, string $entityClass): ConstructorBehaviorInterface
            {
                return $this($name, $propertyPath, $entityClass);
            }
        };
    }

    public function getArguments(CreationDataInterface $entityData): array
    {
        return [$this->argumentName => ($this->callback)($entityData)];
    }

    public function getRequiredAttributes(): array
    {
        return [];
    }

    public function getOptionalAttributes(): array
    {
        return [];
    }

    public function getRequiredToOneRelationships(): array
    {
        return [];
    }

    public function getOptionalToOneRelationships(): array
    {
        return [];
    }

    public function getRequiredToManyRelationships(): array
    {
        return [];
    }

    public function getOptionalToManyRelationships(): array
    {
        return [];
    }
}
