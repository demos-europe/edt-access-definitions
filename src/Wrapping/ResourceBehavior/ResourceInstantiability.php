<?php

declare(strict_types=1);

namespace EDT\Wrapping\ResourceBehavior;

use EDT\Wrapping\Contracts\ContentField;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorParameterInterface;
use EDT\Wrapping\PropertyBehavior\Identifier\IdentifierPostInstantiabilityInterface;
use EDT\Wrapping\PropertyBehavior\PropertyConstrainingInterface;
use EDT\Wrapping\PropertyBehavior\PropertySetabilityInterface;
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
     * @var list<ConstructorParameterInterface|null>
     */
    protected readonly array $orderedConstructorParameters;

    /**
     * @var list<ReflectionParameter>
     */
    protected readonly array $reflectionConstructorParameters;

    /**
     * @param class-string<TEntity> $entityClass
     * @param array<non-empty-string, ConstructorParameterInterface> $constructorParameters mapping from resource property name to constructor parameter
     * @param array<non-empty-string, PropertySetabilityInterface<TEntity>> $postInstantiabilities mapping from resource property name to post instantiability instance
     * @param IdentifierPostInstantiabilityInterface<TEntity>|null $identifierPostInstantiability
     */
    public function __construct(
        protected readonly string $entityClass,
        array $constructorParameters,
        protected readonly array $postInstantiabilities,
        protected readonly ?IdentifierPostInstantiabilityInterface $identifierPostInstantiability
    ) {
        $reflectionClass = new ReflectionClass($this->entityClass);
        $constructor = $this->getConstructor($reflectionClass);
        $this->reflectionConstructorParameters = $constructor?->getParameters() ?? [];

        $lookupTable = [];
        foreach ($constructorParameters as $constructorParameter) {
            $argumentName = $constructorParameter->getArgumentName();
            Assert::keyNotExists($lookupTable, $argumentName);
            $lookupTable[$argumentName] = $constructorParameter;
        }

        $orderedConstructorParameters = [];
        foreach ($this->reflectionConstructorParameters as $propertyName => $reflectionParameter) {
            $orderedConstructorParameters[$propertyName] = $lookupTable[$reflectionParameter->getName()] ?? null;
        }
        $this->orderedConstructorParameters = $orderedConstructorParameters;
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
        if (null !== $entityData->getEntityIdentifier()) {
            // a specific ID was provided, check if it can be set either via constructor
            // parameter or post instantiation setter, if not throw an exception
            if (null === $this->identifierPostInstantiability && array_key_exists(ContentField::ID, $this->orderedConstructorParameters)) {
                // TODO: MUST return 403 Forbidden (https://jsonapi.org/format/#crud-creating-client-ids)
                throw new InvalidArgumentException('Value for `id` field was provided, but no setup to handle it exists.');
            }
        }

        $idSideEffect = $this->identifierPostInstantiability?->setIdentifier($entity, $entityData) ?? false;
        $propertySideEffects = $this
            ->getSetabilitiesSideEffect(array_values($this->postInstantiabilities), $entity, $entityData);

        return $idSideEffect && $propertySideEffects;
    }

    protected function getParameterConstrains(): array
    {
        return array_merge(
            array_filter(
                $this->orderedConstructorParameters,
                static fn (?PropertyConstrainingInterface $param): bool => null !== $param
            ),
            array_values($this->postInstantiabilities)
        );
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
        return array_map(
            static function (
                ReflectionParameter $reflectionParameter,
                ?ConstructorParameterInterface $constructorParameter
            ) use ($entityData): mixed {
                // if no constructor parameter was given, fall back to default parameters
                if (null === $constructorParameter) {
                    return $reflectionParameter->isDefaultValueAvailable()
                        ? $reflectionParameter->getDefaultValue()
                        : throw new InvalidArgumentException('Missing constructor parameter');
                }

                return $constructorParameter->getArgument($entityData);
            },
            $this->reflectionConstructorParameters,
            $this->orderedConstructorParameters
        );
    }
}
