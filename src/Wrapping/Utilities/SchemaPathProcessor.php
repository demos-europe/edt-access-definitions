<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Querying\Contracts\PathException;
use EDT\Querying\SortMethodFactories\SortMethodInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\ContentField;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\Types\FilteringTypeInterface;
use EDT\Wrapping\Contracts\Types\PropertyReadableTypeInterface;
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
 *
 * TODO: remove this class by moving its logic into the classes where it is actually needed
 */
class SchemaPathProcessor
{
    public function __construct(
        protected readonly PropertyPathProcessorFactory $propertyPathProcessorFactory
    ) {}

    /**
     * Check the paths of the given conditions for availability and applies aliases using the given type.
     *
     * @param FilteringTypeInterface&EntityBasedInterface<object> $type
     * @param non-empty-list<DrupalFilterInterface> $conditions
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
     * @param non-empty-list<SortMethodInterface> $sortMethods
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
     * Compares the given path with the {@link TransferableTypeInterface::getReadability()} of the involved types.
     *
     * I.e. the given type specifies properties that are set as readable.
     * If such property is a relationship, it will point to another type, which itself specifies another set of
     * readable properties.
     *
     * The verification is done for the whole given path.
     * The first path segment must be present in the readable properties of the given type.
     * All path segments except for the last one must be represented as relationship property in their corresponding
     * type.
     * So if a path consisting of two segments is given, the first one must be a relationship in the given type.
     * The second path segment is unrelated to the given type.
     * Instead, it must be present in the type of the relationship property that corresponds to the first path segment.
     *
     * The last segment may be represented in the corresponding type by a relationship property or an attribute
     * property.
     * Except if `$allowAttribute` is set to false, in which case all segments in the given path must be represented
     * by a corresponding relationship.
     *
     * Note that {@link ContentField::ID} and {@link ContentField::TYPE} are not allowed in the given path, as they
     * are always readable.
     *
     * @param PropertyReadableTypeInterface<object> $type
     * @param non-empty-list<non-empty-string> $path
     *
     * @throws PropertyAccessException
     */
    public function verifyExternReadablePath(PropertyReadableTypeInterface $type, array $path, bool $allowAttribute): void
    {
        $originalPath = $path;
        try {
            $typeReadability = $type->getReadability();
            $readableProperties = $allowAttribute
                ? $typeReadability->getAllProperties()
                : $typeReadability->getRelationships();

            $currentPathSegment = array_shift($path);
            if (!array_key_exists($currentPathSegment, $readableProperties)) {
                $availablePropertyNames = array_keys($readableProperties);
                throw PropertyAccessException::propertyNotAvailableInType($currentPathSegment, $type, $availablePropertyNames);
            }

            // after processing the path segment above, we check if there are more segments to check, if not, we are done
            if ([] === $path) {
                return;
            }

            // if there are more path segments to check, the current path segment checked above must be a relationship
            if (!$typeReadability->hasRelationship($currentPathSegment)) {
                throw PropertyAccessException::nonRelationship($currentPathSegment, $type);
            }

            // the current path segment checked above is a relationship, retrieve the relationship type
            $property = $typeReadability->getRelationship($currentPathSegment);
            $relationshipType = $property->getRelationshipType();

            try {
                // go into the next recursion level, to check the remaining path segments
                $this->verifyExternReadablePath($relationshipType, $path, $allowAttribute);
            } catch (Exception $exception) {
                throw new ExternReadableRelationshipSchemaVerificationException($relationshipType, $path, $exception);
            }
        } catch (PropertyAccessException $exception) {
            throw AccessException::pathDenied($type, $exception, $originalPath);
        }
    }
}
