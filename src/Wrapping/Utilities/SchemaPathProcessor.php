<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\FilterableTypeInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\SortableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Utilities\TypeAccessors\ExternFilterableTypeAccessor;
use EDT\Wrapping\Utilities\TypeAccessors\ExternReadableTypeAccessor;
use EDT\Wrapping\Utilities\TypeAccessors\ExternSortableTypeAccessor;
use EDT\Wrapping\Utilities\TypeAccessors\InternTypeAccessor;

/**
 * Follows {@link PropertyPathAccessInterface} instances to check if access is
 * allowed in the context of a given root {@link TypeInterface} and maps
 * the paths according to the corresponding {@link TypeInterface::getAliases()} return.
 */
class SchemaPathProcessor
{
    /**
     * @var TypeProviderInterface<PathsBasedInterface, PathsBasedInterface>
     */
    private $typeProvider;

    /**
     * @var PropertyPathProcessorFactory
     */
    private $propertyPathProcessorFactory;

    public function __construct(PropertyPathProcessorFactory $propertyPathProcessorFactory, TypeProviderInterface $typeProvider)
    {
        $this->typeProvider = $typeProvider;
        $this->propertyPathProcessorFactory = $propertyPathProcessorFactory;
    }

    /**
     * Check the paths of the given conditions for availability and applies aliases using the given type.
     *
     * Also adds the {@link ReadableTypeInterface::getAccessCondition() access condition} of the given type.
     *
     * @template C of \EDT\Querying\Contracts\PathsBasedInterface
     * @template S of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param TypeInterface<C, S, object> $type
     * @param C                           ...$conditions
     *
     * @return list<C>
     *
     * @throws PathException Thrown if {@link TypeInterface::getAliases()} returned an invalid path.
     * @throws AccessException
     *
     * @deprecated use {@link SchemaPathProcessor::mapFilterConditions()} and {@link SchemaPathProcessor::processAccessCondition()} instead
     */
    public function mapConditions(TypeInterface $type, PathsBasedInterface ...$conditions): array
    {
        $conditions = array_values($conditions);
        if ([] !== $conditions) {
            if ($type instanceof FilterableTypeInterface) {
                $typeAccessor = new ExternFilterableTypeAccessor($this->typeProvider);
                $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($typeAccessor);
                foreach ($conditions as $condition) {
                    $processor->processPropertyPaths($condition, $type);
                }
            } else {
                throw AccessException::typeNotFilterable($type);
            }
        }

        // process the access condition too, however based on a different property set than the external conditions
        $conditions[] = $this->processAccessCondition($type);

        return $conditions;
    }

    /**
     * Check the paths of the given conditions for availability and applies aliases using the given type.
     *
     * @template C of \EDT\Querying\Contracts\PathsBasedInterface
     * @template S of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param FilterableTypeInterface<C, S, object> $type
     * @param non-empty-list<C>                     $conditions
     *
     * @throws PathException Thrown if {@link TypeInterface::getAliases()} returned an invalid path.
     * @throws AccessException
     */
    public function mapFilterConditions(FilterableTypeInterface $type, array $conditions): void
    {
        $typeAccessor = new ExternFilterableTypeAccessor($this->typeProvider);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($typeAccessor);
        foreach ($conditions as $condition) {
           $processor->processPropertyPaths($condition, $type);
        }
    }

    /**
     * Check the paths of the given sort methods for availability and aliases using the given type.
     *
     * If no sort methods were given then apply the {@link TypeInterface::getDefaultSortMethods() default sort methods}
     * of the given type.
     *
     * @template C of \EDT\Querying\Contracts\PathsBasedInterface
     * @template S of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param TypeInterface<C, S, object> $type
     * @param S                           ...$sortMethods
     *
     * @return list<S>
     *
     * @throws AccessException
     * @throws PathException Thrown if {@link TypeInterface::getAliases()} returned an invalid path.
     *
     * @deprecated use {@link SchemaPathProcessor::processDefaultSortMethods()} and {@link SchemaPathProcessor::mapSorting()} instead
     */
    public function mapSortMethods(TypeInterface $type, PathsBasedInterface ...$sortMethods): array
    {
        if ([] === $sortMethods) {
            return $this->processDefaultSortMethods($type);
        }

        if ($type instanceof SortableTypeInterface) {
            $typeAccessor = new ExternSortableTypeAccessor($this->typeProvider);
            $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($typeAccessor);
            foreach ($sortMethods as $sortMethod) {
                $processor->processPropertyPaths($sortMethod, $type);
            }
            return array_values($sortMethods);
        }

        throw AccessException::typeNotSortable($type);
    }

    /**
     * Check the paths of the given sort methods for availability and aliases using the given type.
     *
     * @template C of \EDT\Querying\Contracts\PathsBasedInterface
     * @template S of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param SortableTypeInterface<C, S, object> $type
     * @param non-empty-list<S>                   $sortMethods
     *
     * @throws AccessException
     * @throws PathException Thrown if {@link TypeInterface::getAliases()} returned an invalid path.
     */
    public function mapSorting(SortableTypeInterface $type, array $sortMethods): void
    {
        $typeAccessor = new ExternSortableTypeAccessor($this->typeProvider);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($typeAccessor);
        foreach ($sortMethods as $sortMethod) {
            $processor->processPropertyPaths($sortMethod, $type);
        }
    }

    /**
     * @param non-empty-list<non-empty-string> $path
     *
     * @return non-empty-list<non-empty-string>
     *
     * @throws PropertyAccessException
     */
    public function mapExternReadablePath(ReadableTypeInterface $type, array $path, bool $allowAttribute): array
    {
        $typeAccessor = new ExternReadableTypeAccessor($this->typeProvider, $allowAttribute);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($typeAccessor);
        try {
            return $processor->processPropertyPath($type, [], ...$path);
        } catch (PropertyAccessException $exception) {
            throw PropertyAccessException::pathDenied($type, $exception, $path);
        }
    }

    /**
     * Check if all properties used in the sort methods are available
     * and map the paths to be applied to the schema of the backing class.
     *
     * @template C of \EDT\Querying\Contracts\PathsBasedInterface
     * @template S of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param TypeInterface<C, S, object> $type
     *
     * @return list<S>
     *
     * @throws PathException Thrown if {@link TypeInterface::getAliases()} returned an invalid path.
     *
     * @internal
     */
    public function processDefaultSortMethods(TypeInterface $type): array
    {
        $sortMethods = $type->getDefaultSortMethods();
        $typeAccessor = new InternTypeAccessor($this->typeProvider);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($typeAccessor);
        foreach ($sortMethods as $sortMethod) {
            $processor->processPropertyPaths($sortMethod, $type);
        }

        return $sortMethods;
    }

    /**
     * Get the processed {@link ReadableTypeInterface::getAccessCondition() access condition}
     * of the given type.
     *
     * @template C of \EDT\Querying\Contracts\PathsBasedInterface
     * @template S of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param TypeInterface<C, S, object> $type
     *
     * @return C
     *
     * @throws AccessException
     * @throws PathException
     *
     * @internal
     */
    public function processAccessCondition(TypeInterface $type): PathsBasedInterface
    {
        $condition = $type->getAccessCondition();
        $typeAccessor = new InternTypeAccessor($this->typeProvider);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($typeAccessor);
        $processor->processPropertyPaths($condition, $type);

        return $condition;
    }
}
