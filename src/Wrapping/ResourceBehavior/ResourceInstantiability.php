<?php

declare(strict_types=1);

namespace EDT\Wrapping\ResourceBehavior;

use EDT\Wrapping\Contracts\ContentField;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Identifier\IdentifierPostConstructorBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\PropertySetBehaviorInterface;
use EDT\Wrapping\Utilities\ConstructorArgumentLookupList;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
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
     * @param array<non-empty-string, list<ConstructorBehaviorInterface>> $propertyConstructorBehaviors mapping from resource property name to constructor parameters
     * @param list<ConstructorBehaviorInterface> $generalConstructorBehaviors
     * @param array<non-empty-string, list<PropertySetBehaviorInterface<TEntity>>> $propertyPostConstructorBehaviors mapping from resource property name to post constructor instances
     * @param list<PropertySetBehaviorInterface<TEntity>> $generalPostConstructorBehaviors mapping from resource property name to post constructor instances
     * @param list<IdentifierPostConstructorBehaviorInterface<TEntity>> $identifierPostConstructorBehaviors
     */
    public function __construct(
        protected readonly string $entityClass,
        protected readonly array $propertyConstructorBehaviors,
        protected readonly array $generalConstructorBehaviors,
        protected readonly array $propertyPostConstructorBehaviors,
        protected readonly array $generalPostConstructorBehaviors,
        protected readonly array $identifierPostConstructorBehaviors
    ) {
        $reflectionClass = new ReflectionClass($this->entityClass);
        $constructor = $this->getConstructor($reflectionClass);
        $this->reflectionConstructorParameters = $constructor?->getParameters() ?? [];
    }

    /**
     * @return array{TEntity, list<non-empty-string>}
     */
    public function initializeEntity(CreationDataInterface $entityData): array
    {
        $lookupTable = $this->createLookupTable($entityData);
        [$arguments, $deviations] = $this->getConstructorArguments($lookupTable);
        $entity = new $this->entityClass(...$arguments);

        return [$entity, $deviations];
    }

    /**
     * @param TEntity $entity
     *
     * @return list<non-empty-string>
     */
    public function fillProperties(object $entity, CreationDataInterface $entityData): array
    {
        $flattenedPostConstructorBehaviors = $this->getFlattenedValues($this->propertyPostConstructorBehaviors);
        $flattenedPostConstructorBehaviors = array_merge($this->generalPostConstructorBehaviors, $flattenedPostConstructorBehaviors);
        $requestDeviations = $this
            ->getSetabilitiesRequestDeviations($flattenedPostConstructorBehaviors, $entity, $entityData);

        return array_unique($requestDeviations);
    }

    /**
     * @param TEntity $entity
     *
     * @return list<non-empty-string>|null  a list of properties that were set differently than requested, due to the presence of the ID in the request; `null` if the ID was not present in the request
     */
    public function setIdentifier(object $entity, CreationDataInterface $entityData): ?array
    {
        if (null === $entityData->getEntityIdentifier()) {
            return null;
        }

        // if a specific ID was provided, check if it can be set either via constructor
        // parameter or post instantiation setter, if not throw an exception

        $nestedIdRequestDeviations = [];
        if ([] !== $this->identifierPostConstructorBehaviors) {
            foreach ($this->identifierPostConstructorBehaviors as $identifierPostConstructorBehavior) {
                $nestedIdRequestDeviations[] = $identifierPostConstructorBehavior->setIdentifier($entity, $entityData);
            }
        } elseif (!array_key_exists(ContentField::ID, $this->propertyConstructorBehaviors)) {
            // TODO: MUST return 403 Forbidden (https://jsonapi.org/format/#crud-creating-client-ids)
            throw new InvalidArgumentException('Value for `id` field was provided in request, but no setup to handle it exists.');
        }

        return array_unique(array_merge(...$nestedIdRequestDeviations));
    }

    protected function getParameterConstrains(): array
    {
        return array_merge(
            $this->generalConstructorBehaviors,
            $this->generalPostConstructorBehaviors,
            $this->getFlattenedValues($this->propertyPostConstructorBehaviors),
            $this->getFlattenedValues($this->propertyConstructorBehaviors)
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
     * @return array{list<mixed>, list<non-empty-string>}
     * @throws ReflectionException
     */
    protected function getConstructorArguments(ConstructorArgumentLookupList $lookupTable): array
    {
        // loop through all constructor arguments of the corresponding entity and
        // determine a value to be used if possible
        $constructorArgumentsWithDeviatingProperties = array_map(
            static function (
                ReflectionParameter $reflectionParameter
            ) use ($lookupTable): array {
                $argumentName = $reflectionParameter->getName();
                if ($lookupTable->hasArgument($argumentName)) {
                    return $lookupTable->getArgument($argumentName);
                }

                // if no constructor parameter was given, try to fall back to default values
                $defaultValue = $reflectionParameter->isDefaultValueAvailable()
                    ? $reflectionParameter->getDefaultValue()
                    : throw new InvalidArgumentException("Missing constructor parameter value: `{$reflectionParameter->getName()}`");

                return [$defaultValue, []];
            },
            $this->reflectionConstructorParameters
        );
        $constructorArguments = array_column($constructorArgumentsWithDeviatingProperties, 0);
        $requestDeviations = array_column($constructorArgumentsWithDeviatingProperties, 1);
        $requestDeviations = array_unique(array_merge(...$requestDeviations));

        return [$constructorArguments, $requestDeviations];
    }

    protected function createLookupTable(CreationDataInterface $entityData): ConstructorArgumentLookupList
    {
        $relevantPropertyConstructorBehaviors = $this->getRelevantPropertyConstructorBehaviors($entityData);

        $propertyLookupTable = $this->calculateConstructorArguments($relevantPropertyConstructorBehaviors, $entityData);
        $generalLookupTable = $this->calculateConstructorArguments($this->generalConstructorBehaviors, $entityData);

        $propertyLookupTable->addFallbacks($generalLookupTable);

        return $propertyLookupTable;
    }

    /**
     * Returns all {@link self::$propertyConstructorBehaviors} which are connected to property names that are present
     * in the given entity data.
     *
     * @return list<ConstructorBehaviorInterface>
     */
    protected function getRelevantPropertyConstructorBehaviors(CreationDataInterface $entityData): array
    {
        $relevantProperties = array_flip($entityData->getPropertyNames());

        return array_merge(
            ...array_values(array_intersect_key($this->propertyConstructorBehaviors, $relevantProperties))
        );
    }

    /**
     * Will provide each constructor parameter behavior instance with the given entity data.
     *
     * The result will be a mapping from a resource property name to an associative list of constructor arguments.
     * The result may contain invalid argument value types or not cover all required constructor arguments, if the
     * configuration contains mistakes.
     *
     * @param list<ConstructorBehaviorInterface> $constructorBehaviors
     */
    public function calculateConstructorArguments(array $constructorBehaviors, CreationDataInterface $entityData): ConstructorArgumentLookupList
    {
        $nestedArguments = array_map(
            static fn(ConstructorBehaviorInterface $behavior): array => $behavior->getArguments($entityData),
            $constructorBehaviors
        );

        $list = new ConstructorArgumentLookupList();
        foreach ($nestedArguments as $arguments) {
            foreach ($arguments as $name => [$value, $deviations]) {
                $list->add($name, $value, $deviations);
            }
        }

        return $list;
    }
}
