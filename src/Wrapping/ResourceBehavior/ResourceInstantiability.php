<?php

declare(strict_types=1);

namespace EDT\Wrapping\ResourceBehavior;

use EDT\Wrapping\Contracts\ContentField;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Identifier\IdentifierPostConstructorBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\PropertySetBehaviorInterface;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Webmozart\Assert\Assert;
use function array_key_exists;

/**
 * @template TEntity of object
 */
class ResourceInstantiability extends AbstractResourceModifier
{
    /**
     * @var list<ReflectionParameter>
     */
    protected readonly array $reflectionConstructorParameters;

    /**
     * @param class-string<TEntity> $entityClass
     * @param array<non-empty-string, list<ConstructorBehaviorInterface>> $constructorBehaviors mapping from resource property name to constructor parameters
     * @param array<non-empty-string, list<PropertySetBehaviorInterface<TEntity>>> $postConstructorBehaviors mapping from resource property name to post constructor instances
     * @param list<IdentifierPostConstructorBehaviorInterface<TEntity>> $identifierPostConstructorBehaviors
     */
    public function __construct(
        protected readonly string $entityClass,
        protected readonly array $constructorBehaviors,
        protected readonly array $postConstructorBehaviors,
        protected readonly array $identifierPostConstructorBehaviors
    ) {
        $reflectionClass = new ReflectionClass($this->entityClass);
        $constructor = $this->getConstructor($reflectionClass);
        $this->reflectionConstructorParameters = $constructor?->getParameters() ?? [];
    }

    /**
     * @param list<mixed> $constructorArguments
     *
     * @return TEntity
     */
    public function initializeEntity(array $constructorArguments): object
    {
        return new $this->entityClass(...$constructorArguments);
    }

    /**
     * @param TEntity $entity
     */
    public function fillProperties(object $entity, CreationDataInterface $entityData): bool
    {
        $idSideEffect = false;

        // if a specific ID was provided, check if it can be set either via constructor
        // parameter or post instantiation setter, if not throw an exception
        if (null !== $entityData->getEntityIdentifier()) {
            if ([] !== $this->identifierPostConstructorBehaviors) {
                foreach ($this->identifierPostConstructorBehaviors as $identifierPostConstructorBehavior) {
                    $idSideEffect = $identifierPostConstructorBehavior->setIdentifier($entity, $entityData) || $idSideEffect;
                }
            } elseif (!array_key_exists(ContentField::ID, $this->constructorBehaviors)) {
                // TODO: MUST return 403 Forbidden (https://jsonapi.org/format/#crud-creating-client-ids)
                throw new InvalidArgumentException('Value for `id` field was provided in request, but no setup to handle it exists.');
            }
        }

        $flattenedPostConstructorBehaviors = $this->getFlattenedValues($this->postConstructorBehaviors);
        $propertySideEffects = $this
            ->getSetabilitiesSideEffect($flattenedPostConstructorBehaviors, $entity, $entityData);

        return $idSideEffect && $propertySideEffects;
    }

    protected function getParameterConstrains(): array
    {
        return array_merge(
            $this->getFlattenedValues($this->postConstructorBehaviors),
            $this->getFlattenedValues($this->constructorBehaviors)
        );
    }

    /**
     * @template TValue of object
     *
     * @param array<non-empty-string, list<TValue>> $nestedArray
     *
     * @return list<TValue>
     */
    protected function getFlattenedValues(array $nestedArray): array
    {
        return array_merge(...array_values($nestedArray));
    }

    /**
     * @param ReflectionClass<object> $class
     */
    protected function getConstructor(ReflectionClass $class): ?ReflectionMethod
    {
        $constructor = $class->getConstructor();
        if (null === $constructor) {
            $parent = $class->getParentClass();
            if (false === $parent) {
                return null;
            }
            return $this->getConstructor($parent);
        }

        return $constructor;
    }

    /**
     * @return list<mixed>
     */
    public function getConstructorArguments(CreationDataInterface $entityData): array
    {
        $lookupTable = $this->createLookupTable($entityData);

        return array_map(
            static fn (
                ReflectionParameter $reflectionParameter
            ): mixed => $lookupTable[$reflectionParameter->getName()] ?? (
                // if no constructor parameter was given, try to fall back to default values
                $reflectionParameter->isDefaultValueAvailable()
                    ? $reflectionParameter->getDefaultValue()
                    : throw new InvalidArgumentException('Missing constructor parameter')
            ),
            $this->reflectionConstructorParameters
        );
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    protected function createLookupTable(CreationDataInterface $entityData): array
    {
        $nestedConstructorArguments = array_map(
            fn (array $constructorBehaviors): array => $this->calculateConstructorArguments($constructorBehaviors, $entityData),
            $this->constructorBehaviors
        );

        $lookupTable = [];
        foreach ($nestedConstructorArguments as $constructorArgumentLists) {
            foreach ($constructorArgumentLists as $constructorArguments) {
                foreach ($constructorArguments as $argumentName => $constructorArgument) {
                    Assert::keyNotExists($lookupTable, $argumentName);
                    $lookupTable[$argumentName] = $constructorArgument;
                }
            }
        }

        return $lookupTable;
    }

    /**
     * Will provide each constructor parameter behavior instance with the given entity data.
     *
     * The result will be a mapping from a resource property name to an associative list of constructor arguments.
     * I.e. the result may contain conflicting values or may not cover all required constructor arguments, if the
     * configuration contains mistakes.
     *
     * @param list<ConstructorBehaviorInterface> $constructorBehaviors
     *
     * @return list<array<non-empty-string, mixed>>
     */
    protected function calculateConstructorArguments(array $constructorBehaviors, CreationDataInterface $entityData): array
    {
        return array_map(
            static fn(ConstructorBehaviorInterface $constructorBehavior): array => $constructorBehavior->getArguments($entityData),
            $constructorBehaviors
        );
    }
}
