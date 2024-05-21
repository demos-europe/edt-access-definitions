<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use EDT\Querying\Contracts\PathAdjustableInterface;
use EDT\Querying\Contracts\PathException;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\PropertyPaths\PropertyLinkInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Utilities\TypeAccessors\AbstractProcessorConfig;
use InvalidArgumentException;
use function array_key_exists;

/**
 * Instances of this class can be used to convert the property paths in a {@link PathsBasedInterface}
 * to their de-aliased version. During the conversion the path will be automatically checked
 * for property access violations, depending on the context (readability/sortability/...).
 * The context is set on instantiation by using providers that limit the access accordingly.
 *
 * @template TType of object
 */
class PropertyPathProcessor
{
    /**
     * @param AbstractProcessorConfig<TType> $processorConfig
     */
    public function __construct(
        protected readonly AbstractProcessorConfig $processorConfig
    ) {}

    /**
     * Check if all properties used by the given property paths are accessible
     * and map the paths to be applied to the schema of the backing class.
     *
     * Executes {@link PropertyPathProcessor::processPropertyPath} on all given paths. Will throw an
     * exception if the processing of any of the given paths fails.
     *
     * @throws AccessException Thrown if any of the given paths can not be used because a path segment
     *                         is not available (not present via
     *                         {@link AbstractProcessorConfig::getProperties()}).
     * @throws PathException
     */
    public function processPropertyPaths(PathAdjustableInterface $pathAdjustable): void
    {
        // If the path is `book.author.name` and `author` needs mapping but `book` does not
        // then we get the author relationship here and map it to something like
        // `book.authoredBy.fullName` or `book.author.meta.name` depending on the
        // schema of the object class backing the type.
        $pathAdjustable->adjustPath(function (array $path): array {
            $rootType = $this->processorConfig->getRootType();
            try {
                $availableProperties = $this->processorConfig->getProperties($rootType);
                return $this->processPropertyPath($availableProperties, [], ...$path);
            } catch (PropertyAccessException $exception) {
                throw AccessException::pathDenied($rootType, $exception, $path);
            }
        });
    }

    /**
     * Follows the given property path recursively and rewrites it if necessary by appending the rewritten path to the given array.
     *
     * @param array<non-empty-string, PropertyLinkInterface> $availableProperties
     * @param list<non-empty-string> $newPath is filled with the rewritten path during the recursive execution of this method
     * @param non-empty-string $currentExternalPathSegment
     * @param non-empty-string ...$remainingExternalPathSegments
     *
     * @return non-empty-list<non-empty-string> A list of property names denoting the processed path to a specific property.
     *
     * @throws PropertyAccessException if the property of the $currentPathPart or any of the $remainingParts is not available for some reason
     */
    public function processPropertyPath(array $availableProperties, array $newPath, string $currentExternalPathSegment, string ...$remainingExternalPathSegments): array
    {
        // abort if the (external) property is not available
        if (!array_key_exists($currentExternalPathSegment, $availableProperties)) {
            $propertyList = implode(', ', array_keys($availableProperties));

            throw new InvalidArgumentException("No property '$currentExternalPathSegment' is available. Available properties are: $propertyList");
        }

        $pathLink = $availableProperties[$currentExternalPathSegment];

        // Convert the current (external) path segment to its internal representation, leading to an
        // actual entity property and append it to the result path.
        array_push($newPath, ...$pathLink->getPath());

        // continue with the next (external) path segment if there is one...
        $nextAvailableProperties = $pathLink->getAvailableTargetProperties();
        if (null !== $nextAvailableProperties) {
            if ([] === $remainingExternalPathSegments) {
                // if no parts remain after the current relationship are done and don't need to follow the $nextTarget
                return $newPath;
            }

            // otherwise, we continue the mapping recursively
            return $this->processPropertyPath($nextAvailableProperties, $newPath, ...$remainingExternalPathSegments);
        }

        // ...or finish the processing
        if ([] === $remainingExternalPathSegments) {
            // no parts remain after the attribute, we are done
            return $newPath;
        }

        // the current segment is an attribute followed by more segments,
        // thus we throw an exception
        throw new InvalidArgumentException("The property '$currentExternalPathSegment' exists but it is not a relationship and the path continues after it. Check your access to the schema of the type.");
    }
}
