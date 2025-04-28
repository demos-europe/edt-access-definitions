<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Relationship;

use EDT\JsonApi\ApiDocumentation\Cardinality;
use EDT\JsonApi\ResourceTypes\ResourceTypeInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\ResourceTypeProviderInterface;
use EDT\Wrapping\Contracts\Types\NamedTypeInterface;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\IdUnrelatedTrait;

/**
 * This behavior will always trigger on creation requests, regardless of the properties present in the request.
 *
 * @template TCondition of PathsBasedInterface
 */
class RequiredRelationshipConstructorBehavior implements ConstructorBehaviorInterface
{
    use IdUnrelatedTrait;

    /**
     * @param non-empty-string $argumentName
     * @param callable(CreationDataInterface): array{mixed, list<non-empty-string>} $callback
     * @param NamedTypeInterface|ResourceTypeProviderInterface<TCondition, PathsBasedInterface, object> $relationshipType
     */
    public function __construct(
        protected readonly string $argumentName,
        protected readonly mixed $callback,
        protected readonly NamedTypeInterface|ResourceTypeProviderInterface $relationshipType,
        protected readonly Cardinality $cardinality
    ) {}

    /**
     * @param callable(CreationDataInterface): array{mixed, list<non-empty-string>} $behavior
     *
     * @return RelationshipConstructorBehaviorFactoryInterface<PathsBasedInterface>
     */
    public static function createFactory(callable $behavior): RelationshipConstructorBehaviorFactoryInterface
    {
        return new class ($behavior) implements RelationshipConstructorBehaviorFactoryInterface {
            /**
             * @param callable(CreationDataInterface): array{mixed, list<non-empty-string>} $callback
             */
            public function __construct(protected readonly mixed $callback){}

            public function __invoke(
                string                                           $name,
                array                                            $propertyPath,
                string                                           $entityClass,
                NamedTypeInterface|ResourceTypeProviderInterface $relationshipType,
            ): ConstructorBehaviorInterface {
                return new RequiredRelationshipConstructorBehavior($name, $this->callback, $relationshipType, Cardinality::TO_ONE);
            }

            public function createRelationshipConstructorBehavior(string $name, array $propertyPath, string $entityClass, ResourceTypeInterface $relationshipType): ConstructorBehaviorInterface
            {
                return $this($name, $propertyPath, $entityClass, $relationshipType);
            }
        };
    }

    public function getRequiredToOneRelationships(): array
    {
        return $this->cardinality->equals(Cardinality::TO_ONE) ? [$this->argumentName => $this->getRelationshipType()->getTypeName()] : [];
    }

    public function getRequiredToManyRelationships(): array
    {
        return !$this->cardinality->equals(Cardinality::TO_ONE) ? [$this->argumentName => $this->getRelationshipType()->getTypeName()] : [];
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

    public function getOptionalToOneRelationships(): array
    {
        return [];
    }

    public function getOptionalToManyRelationships(): array
    {
        return [];
    }

    protected function getRelationshipType(): NamedTypeInterface
    {
        return $this->relationshipType instanceof NamedTypeInterface
            ? $this->relationshipType
            : $this->relationshipType->getType();
    }
}
