<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\TypeProviderInterface;
use EDT\Wrapping\Contracts\Types\FilterableTypeInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Contracts\Types\SortableTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Utilities\TypeAccessors\ExternFilterableProcessorConfig;
use EDT\Wrapping\Utilities\TypeAccessors\ExternReadableProcessorConfig;
use EDT\Wrapping\Utilities\TypeAccessors\ExternSortableProcessorConfig;
use EDT\Wrapping\Utilities\TypeAccessors\InternProcessorConfig;

/**
 * Follows {@link PropertyPathAccessInterface} instances to check if access is
 * allowed in the context of a given root {@link TypeInterface} and maps
 * the paths according to the corresponding {@link AliasableTypeInterface::getAliases()} return.
 */
class SchemaPathProcessor
{
    /**
     * @var TypeProviderInterface<PathsBasedInterface, PathsBasedInterface>
     */
    private TypeProviderInterface $typeProvider;

    private PropertyPathProcessorFactory $propertyPathProcessorFactory;

    public function __construct(PropertyPathProcessorFactory $propertyPathProcessorFactory, TypeProviderInterface $typeProvider)
    {
        $this->typeProvider = $typeProvider;
        $this->propertyPathProcessorFactory = $propertyPathProcessorFactory;
    }

    /**
     * Check the paths of the given conditions for availability and applies aliases using the given type.
     *
     * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
     * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param FilterableTypeInterface<TCondition, TSorting, object> $type
     * @param non-empty-list<TCondition>                     $conditions
     *
     * @throws PathException Thrown if {@link AliasableTypeInterface::getAliases()} returned an invalid path.
     * @throws AccessException
     */
    public function mapFilterConditions(FilterableTypeInterface $type, array $conditions): void
    {
        $processorConfig = new ExternFilterableProcessorConfig($this->typeProvider, $type);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($processorConfig);
        array_map([$processor, 'processPropertyPaths'], $conditions);
    }

    /**
     * Check the paths of the given sort methods for availability and aliases using the given type.
     *
     * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
     * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param SortableTypeInterface<TCondition, TSorting, object> $type
     * @param non-empty-list<TSorting>                   $sortMethods
     *
     * @throws AccessException
     * @throws PathException Thrown if {@link AliasableTypeInterface::getAliases()} returned an invalid path.
     */
    public function mapSorting(SortableTypeInterface $type, array $sortMethods): void
    {
        $processorConfig = new ExternSortableProcessorConfig($this->typeProvider, $type);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($processorConfig);
        array_map([$processor, 'processPropertyPaths'], $sortMethods);
    }

    /**
     * @param non-empty-list<non-empty-string> $path
     *
     * @return non-empty-list<non-empty-string>
     *
     * @throws PropertyAccessException
     */
    public function mapExternReadablePath(TransferableTypeInterface $type, array $path, bool $allowAttribute): array
    {
        $processorConfig = new ExternReadableProcessorConfig($this->typeProvider, $type, $allowAttribute);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($processorConfig);
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
     * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
     * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param TypeInterface<TCondition, TSorting, object> $type
     *
     * @return list<TSorting>
     *
     * @throws PathException Thrown if {@link AliasableTypeInterface::getAliases()} returned an invalid path.
     *
     * @internal
     */
    public function processDefaultSortMethods(TypeInterface $type): array
    {
        $sortMethods = $type->getDefaultSortMethods();
        if ([] === $sortMethods) {
            return [];
        }

        $processorConfig = new InternProcessorConfig($this->typeProvider, $type);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($processorConfig);
        array_map([$processor, 'processPropertyPaths'], $sortMethods);

        return $sortMethods;
    }

    /**
     * Get the processed {@link TransferableTypeInterface::getAccessCondition() access condition}
     * of the given type.
     *
     * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
     * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
     *
     * @param TypeInterface<TCondition, TSorting, object> $type
     *
     * @return TCondition
     *
     * @throws AccessException
     * @throws PathException
     *
     * @internal
     */
    public function processAccessCondition(TypeInterface $type): PathsBasedInterface
    {
        $condition = $type->getAccessCondition();
        $processorConfig = new InternProcessorConfig($this->typeProvider, $type);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($processorConfig);
        $processor->processPropertyPaths($condition);

        return $condition;
    }
}
