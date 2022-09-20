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
     * @template C of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param TypeInterface<C, PathsBasedInterface, object> $type
     * @param C ...$conditions
     *
     * @return list<C>
     *
     * @throws PathException
     * @throws AccessException
     */
    public function mapConditions(TypeInterface $type, PathsBasedInterface ...$conditions): array
    {
        $conditions = array_values($conditions);
        if ([] !== $conditions) {
            if ($type instanceof FilterableTypeInterface) {
                array_walk($conditions, [$this, 'processExternalCondition'], $type);
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
     * @template S of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param TypeInterface<PathsBasedInterface, S, object> $type
     * @param S                                             ...$sortMethods
     *
     * @return list<S>
     *
     * @throws AccessException
     * @throws PathException
     */
    public function mapSortMethods(TypeInterface $type, PathsBasedInterface ...$sortMethods): array
    {
        if ([] === $sortMethods) {
            return $this->processDefaultSortMethods($type);
        }

        if ($type instanceof SortableTypeInterface) {
            array_walk($sortMethods, [$this, 'processExternalSortMethod'], $type);
            return array_values($sortMethods);
        }

        throw AccessException::typeNotSortable($type);
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
        $processor = new PropertyPathProcessor($typeAccessor);
        try {
            return $processor->processPropertyPath($type, [], ...$path);
        } catch (PropertyAccessException $exception) {
            throw PropertyAccessException::pathDenied($type, $exception, $path);
        }
    }

    /**
     * @template S of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param TypeInterface<PathsBasedInterface, S, object> $type
     *
     * @return list<S>
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
    protected function processExternalSortMethod(PathsBasedInterface $sortMethod, int $key, TypeInterface $type): void
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
    protected function processInternalSortMethod(PathsBasedInterface $sortMethod, int $key, TypeInterface $type): void
    {
        $typeAccessor = new InternTypeAccessor($this->typeProvider);
        $processor = new PropertyPathProcessor($typeAccessor);
        $processor->processPropertyPaths($sortMethod, $type);
    }

    /**
     * Check if all properties used in the condition are available for filtering
     * and map the paths to be applied to the schema of the backing class.
     *
     * @throws AccessException
     * @throws PathException Thrown if {@link TypeInterface::getAliases()} returned an invalid path.
     */
    protected function processExternalCondition(PathsBasedInterface $condition, int $key, FilterableTypeInterface $type): void
    {
        $typeAccessor = new ExternFilterableTypeAccessor($this->typeProvider);
        $processor = new PropertyPathProcessor($typeAccessor);
        $processor->processPropertyPaths($condition, $type);
    }

    /**
     * @template C of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param TypeInterface<C, PathsBasedInterface, object> $type
     *
     * @return C
     *
     * @throws AccessException
     * @throws PathException
     */
    protected function processAccessCondition(TypeInterface $type): PathsBasedInterface
    {
        $condition = $type->getAccessCondition();
        $typeAccessor = new InternTypeAccessor($this->typeProvider);
        $processor = new PropertyPathProcessor($typeAccessor);
        $processor->processPropertyPaths($condition, $type);

        return $condition;
    }
}
