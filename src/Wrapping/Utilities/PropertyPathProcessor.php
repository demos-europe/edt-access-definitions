<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\Contracts\PropertyPathAccessInterface;
use EDT\Querying\PropertyPaths\PathInfo;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\RelationshipAccessException;
use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use EDT\Wrapping\Utilities\TypeAccessors\AbstractProcessorConfig;
use InvalidArgumentException;
use function array_key_exists;

/**
 * Instances of this class can be used to convert the property paths in a {@link PathsBasedInterface}
 * to their de-aliased version. During the conversion the path will  be automatically checked
 * for property access violations, depending on the context (readability/sortability/...).
 * The context is set on instantiation by using providers that limit the access accordingly.
 *
 * @template TType of TypeInterface<\EDT\Querying\Contracts\PathsBasedInterface, \EDT\Querying\Contracts\PathsBasedInterface, object>
 */
class PropertyPathProcessor
{
    /**
     * @var AbstractProcessorConfig<TType>
     */
    private AbstractProcessorConfig $processorConfig;

    /**
     * @param AbstractProcessorConfig<TType> $processorConfig
     */
    public function __construct(AbstractProcessorConfig $processorConfig)
    {
        $this->processorConfig = $processorConfig;
    }

    /**
     * Check if all properties used by the given property paths are accessible
     * and map the paths to be applied to the schema of the backing class.
     *
     * Executes {@link PropertyPathProcessor::processPropertyPath} on all given paths. Will throw an
     * exception if the processing of any of the given paths fails.
     *
     * @throws AccessException Thrown if any of the given paths can not be used because the path itself is not available (not made present via $getProperties) or the Type it leads to is not accessible (e.g. {@link TypeInterface::isAvailable()} returned `false`).
     * @throws PathException Thrown if {@link TypeInterface::getAliases()} returned an invalid path.
     */
    public function processPropertyPaths(PathsBasedInterface $pathsBased): void
    {
        // If the path is `book.author.name` and `author` needs mapping but `book` does not
        // then we get the author relationship here and map it to something like
        // `book.authoredBy.fullName` or `book.author.meta.name` depending on the
        // schema of the object class backing the type.
        array_map(function (PropertyPathAccessInterface $propertyPath): void {
            $path = $propertyPath->getAsNames();
            if ([] === $path) {
                throw new InvalidArgumentException('Property path must not be empty.');
            }
            $rootType = $this->processorConfig->getRootType();
            try {
                $path = $this->processPropertyPath($rootType, [], ...$path);
            } catch (PropertyAccessException $exception) {
                throw PropertyAccessException::pathDenied($rootType, $exception, $path);
            }
            $propertyPath->setPath($path);
        }, PathInfo::getPropertyPaths($pathsBased));
    }

    /**
     * Follows the given property path recursively and rewrites it if necessary by appending the rewritten path to the given array.
     *
     * @param TType                  $currentType
     * @param list<non-empty-string> $newPath is filled with the rewritten path during the recursive execution of this method
     * @param non-empty-string       $currentPathPart
     * @param non-empty-string       ...$remainingParts
     *
     * @return non-empty-list<non-empty-string> A list of property names denoting the processed path to a specific property.
     *
     * @throws PropertyAccessException if the property of the $currentPathPart or any of the $remainingParts is not available for some reason
     */
    public function processPropertyPath(TypeInterface $currentType, array $newPath, string $currentPathPart, string ...$remainingParts): array
    {
        $propertyTypeIdentifier = $this->getPropertyTypeIdentifier($currentType, $currentPathPart);

        // Check if the current type needs mapping to the backing object schema, if so, apply it.
        $pathToAdd = $this->processorConfig->getDeAliasedPath($currentType, $currentPathPart);
        // append the de-aliased path to the processed path
        $newPath = $this->appendDeAliasedPath($newPath, $pathToAdd);

        if (null !== $propertyTypeIdentifier) {
            try {
                // even if we don't need the $nextTarget here because there may be no
                // remaining segments, we still check with this call if the current
                // relationship is valid in this path
                $nextTarget = $this->processorConfig->getRelationshipType($propertyTypeIdentifier);

                if ([] === $remainingParts) {
                    // if no parts remain after the current relationship are done and don't need to follow the $nextTarget
                    return $newPath;
                }

                // otherwise, we continue the mapping recursively
                return $this->processPropertyPath($nextTarget, $newPath, ...$remainingParts);
            } catch (TypeRetrievalAccessException $exception) {
                throw RelationshipAccessException::relationshipTypeAccess($currentType, $currentPathPart, $exception);
            }
        }

        if ([] === $remainingParts) {
            // no parts remain after the attribute, we are done
            return $newPath;
        }

        // the current segment is an attribute followed by more segments,
        // thus we throw an exception
        throw PropertyAccessException::nonRelationship($currentPathPart, $currentType);
    }

    /**
     * @param TType $type
     * @param non-empty-string $pathSegment
     *
     * @return non-empty-string|null
     */
    protected function getPropertyTypeIdentifier(TypeInterface $type, string $pathSegment): ?string
    {
        $availableProperties = $this->processorConfig->getProperties($type);
        // abort if the (originally accessed/non-de-aliased) property is not available
        if (!array_key_exists($pathSegment, $availableProperties)) {
            $availablePropertyNames = array_keys($availableProperties);
            throw PropertyAccessException::propertyNotAvailableInType($pathSegment, $type, ...$availablePropertyNames);
        }

        return $availableProperties[$pathSegment];
    }

    /**
     * @param list<non-empty-string>           $newPath
     * @param non-empty-list<non-empty-string> $pathToAdd
     *
     * @return non-empty-list<non-empty-string>
     */
    private function appendDeAliasedPath(array $newPath, array $pathToAdd): array
    {
        array_push($newPath, ...$pathToAdd);

        return $newPath;
    }
}
