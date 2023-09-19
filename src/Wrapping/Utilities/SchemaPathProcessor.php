<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\Types\FilteringTypeInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Contracts\Types\SortingTypeInterface;
use EDT\Wrapping\Utilities\TypeAccessors\ExternFilterableProcessorConfig;
use EDT\Wrapping\Utilities\TypeAccessors\ExternSortableProcessorConfig;
use Exception;
use function array_key_exists;

/**
 * Follows {@link PropertyPathAccessInterface} instances to check if access is
 * allowed in the context of a given root {@link EntityBasedInterface} and maps
 * the paths according to the corresponding configured aliases.
 */
class SchemaPathProcessor
{
    public function __construct(
        protected readonly PropertyPathProcessorFactory $propertyPathProcessorFactory
    ) {}

    /**
     * Check the paths of the given conditions for availability and applies aliases using the given type.
     *
     * @template TCondition of PathsBasedInterface
     * @template TSorting of PathsBasedInterface
     *
     * @param FilteringTypeInterface<TCondition, TSorting>&EntityBasedInterface<object> $type
     * @param non-empty-list<TCondition> $conditions
     *
     * @throws PathException
     * @throws AccessException
     */
    public function mapFilterConditions(FilteringTypeInterface&EntityBasedInterface $type, array $conditions): void
    {
        $processorConfig = new ExternFilterableProcessorConfig($type);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($processorConfig);
        array_map([$processor, 'processPropertyPaths'], $conditions);
    }

    /**
     * Check the paths of the given sort methods for availability and aliases using the given type.
     *
     * @template TCondition of PathsBasedInterface
     * @template TSorting of PathsBasedInterface
     *
     * @param SortingTypeInterface<TCondition, TSorting> $type
     * @param non-empty-list<TSorting> $sortMethods
     *
     * @throws AccessException
     * @throws PathException
     */
    public function mapSorting(SortingTypeInterface $type, array $sortMethods): void
    {
        $processorConfig = new ExternSortableProcessorConfig($type);
        $processor = $this->propertyPathProcessorFactory->createPropertyPathProcessor($processorConfig);
        array_map([$processor, 'processPropertyPaths'], $sortMethods);
    }

    /**
     * Compares the given path with the {@link TransferableTypeInterface::getReadability()}
     * of the involved types.
     *
     * @param TransferableTypeInterface<PathsBasedInterface, PathsBasedInterface, object> $type
     * @param non-empty-list<non-empty-string> $path
     *
     * @throws PropertyAccessException
     */
    public function verifyExternReadablePath(TransferableTypeInterface $type, array $path, bool $allowAttribute): void
    {
        $originalPath = $path;
        try {
            $readableProperties = $type->getReadability();
            $allReadableProperties = $allowAttribute
                ? $readableProperties->getAllProperties()
                : $readableProperties->getRelationships();

            $pathSegment = array_shift($path);
            if (!array_key_exists($pathSegment, $allReadableProperties)) {
                $availablePropertyNames = array_keys($allReadableProperties);
                throw PropertyAccessException::propertyNotAvailableInType($pathSegment, $type, $availablePropertyNames);
            }

            if ([] === $path) {
                return;
            }

            if (!$readableProperties->hasRelationship($pathSegment)) {
                throw PropertyAccessException::nonRelationship($pathSegment, $type);
            }

            $property = $readableProperties->getRelationship($pathSegment);
            $relationshipType = $property->getRelationshipType();

            try {
                $this->verifyExternReadablePath($relationshipType, $path, $allowAttribute);
            } catch (Exception $exception) {
                throw new ExternReadableRelationshipSchemaVerificationException($relationshipType, $path, $exception);
            }
        } catch (PropertyAccessException $exception) {
            throw AccessException::pathDenied($type, $exception, $originalPath);
        }
    }
}
