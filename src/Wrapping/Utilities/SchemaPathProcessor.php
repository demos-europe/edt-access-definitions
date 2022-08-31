<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\SortMethodInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\FilterableTypeInterface;
use EDT\Wrapping\Contracts\Types\SortableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Utilities\TypeAccessors\ExternFilterableTypeAccessor;
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
     * @var TypeProviderInterface
     */
    private $typeProvider;

    public function __construct(TypeProviderInterface $typeProvider)
    {
        $this->typeProvider = $typeProvider;
    }

    /**
     * Check the paths of the given conditions for availability and applies aliases using the given type.
     *
     * Also adds the {@link ReadableTypeInterface::getAccessCondition() access condition} of the given type.
     *
     * @param FunctionInterface<bool> ...$conditions
     *
     * @return array<int, FunctionInterface<bool>>
     *
     * @throws PathException
     * @throws AccessException
     */
    public function mapConditions(TypeInterface $type, FunctionInterface ...$conditions): array
    {
        if ([] !== $conditions) {
            if ($type instanceof FilterableTypeInterface) {
                $this->processExternalConditions($type, ...$conditions);
            } else {
                throw AccessException::typeNotFilterable($type);
            }
        }

        // process the access condition too, however based on a different property set than the external conditions
        $conditions[] = $this->processAccessCondition($type);

        return $conditions;
    }

    /**
     * Check the paths of the given sort methods for availability and aliases using the given type.
     *
     * If no sort methods were given then apply the {@link TypeInterface::getDefaultSortMethods() default sort methods}
     * of the given type.
     *
     * @return array<int, SortMethodInterface>
     *
     * @throws AccessException
     * @throws PathException
     */
    public function mapSortMethods(TypeInterface $type, SortMethodInterface ...$sortMethods): array
    {
        if ([] === $sortMethods) {
            return $this->processDefaultSortMethods($type);
        }

        if ($type instanceof SortableTypeInterface) {
            return $this->processExternalSortMethods($type, ...$sortMethods);
        }

        throw AccessException::typeNotSortable($type);
    }

    /**
     * Checks the paths of the given conditions for availability and applies aliases using the given type.
     *
     * @param FunctionInterface<bool> ...$conditions
     *
     * @throws AccessException
     * @throws PathException
     */
    protected function processExternalConditions(FilterableTypeInterface $type, FunctionInterface ...$conditions): void
    {
        // check authorizations of the property paths of the conditions and map them to the backing schema
        array_walk($conditions, [$this, 'processExternalCondition'], $type);
    }

    /**
     * Check the paths of the given sort methods for availability and aliases using the given type.
     *
     * @param SortMethodInterface ...$sortMethods
     * @return array<int, SortMethodInterface>
     *
     * @throws PathException
     * @throws AccessException
     */
    protected function processExternalSortMethods(SortableTypeInterface $type, SortMethodInterface ...$sortMethods): array
    {
        // check authorizations of the property paths of the sort methods and map them to the backing schema
        array_walk($sortMethods, [$this, 'processExternalSortMethod'], $type);

        return $sortMethods;
    }

    /**
     * @return array<int, SortMethodInterface>
     *
     * @throws PathException
     */
    protected function processDefaultSortMethods(TypeInterface $type): array
    {
        $sortMethods = $type->getDefaultSortMethods();
        array_walk($sortMethods, [$this, 'processInternalSortMethod'], $type);

        return $sortMethods;
    }

    /**
     * Check if all properties used in the sort methods are available
     * and map the paths to be applied to the schema of the backing class.
     *
     * @throws PathException Thrown if {@link TypeInterface::getAliases()} returned an invalid path.
     * @throws AccessException
     */
    protected function processExternalSortMethod(SortMethodInterface $sortMethod, int $key, SortableTypeInterface $type): void
    {
        $typeAccessor = new ExternSortableTypeAccessor($this->typeProvider);
        $processor = new PropertyPathProcessor($typeAccessor);
        $processor->processPropertyPaths($sortMethod, $type);
    }

    /**
     * Check if all properties used in the sort methods are available
     * and map the paths to be applied to the schema of the backing class.
     *
     * @throws PathException Thrown if {@link TypeInterface::getAliases()} returned an invalid path.
     */
    protected function processInternalSortMethod(SortMethodInterface $sortMethod, int $key, TypeInterface $type): void
    {
        $typeAccessor = new InternTypeAccessor($this->typeProvider);
        $processor = new PropertyPathProcessor($typeAccessor);
        $processor->processPropertyPaths($sortMethod, $type);
    }

    /**
     * Check if all properties used in the condition are available for filtering
     * and map the paths to be applied to the schema of the backing class.
     *
     * @param FunctionInterface<bool> $condition
     *
     * @throws AccessException
     * @throws PathException Thrown if {@link TypeInterface::getAliases()} returned an invalid path.
     */
    protected function processExternalCondition(FunctionInterface $condition, int $key, FilterableTypeInterface $type): void
    {
        $typeAccessor = new ExternFilterableTypeAccessor($this->typeProvider);
        $processor = new PropertyPathProcessor($typeAccessor);
        $processor->processPropertyPaths($condition, $type);
    }

    /**
     * @return FunctionInterface<bool>
     *
     * @throws AccessException
     * @throws PathException
     */
    protected function processAccessCondition(TypeInterface $type): FunctionInterface
    {
        $condition = $type->getAccessCondition();
        $typeAccessor = new InternTypeAccessor($this->typeProvider);
        $processor = new PropertyPathProcessor($typeAccessor);
        $processor->processPropertyPaths($condition, $type);

        return $condition;
    }
}
