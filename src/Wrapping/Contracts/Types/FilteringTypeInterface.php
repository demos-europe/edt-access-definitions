<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Querying\PropertyPaths\PropertyLinkInterface;

/**
 * Defines that the implementing type can be used to filter entities of this type or other types.
 *
 * E.g. if you have a `BookType` that implements {@link TransferableTypeInterface} you can retrieve
 * corresponding `Book` entities. If you want to retrieve books with a specific title, you can
 * implement this interface and return the `title` property in
 * {@link FilteringTypeInterface::getFilteringProperties()}.
 *
 * Note however, that it can make sense to let your type implement this interface without implementing
 * {@link TransferableTypeInterface}. E.g. if you define an `AuthorType` implementing
 * {@link TransferableTypeInterface} and {@link FilteringTypeInterface}, then it you may want to
 * allow filtering of `Author` entities by properties of their written books, without allowing
 * the reading of these `Book` entities, i.e. without having `BookType` implement {@link TransferableTypeInterface}.
 *
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 */
interface FilteringTypeInterface
{
    /**
     * All properties of this type that can be used to filter instances of this type and types that
     * have a relationship to this type.
     *
     * In most use cases this method could return the same properties as
     * {@link TransferableTypeInterface::getReadability()} but you may want to limit
     * the properties further, e.g. if filtering over some properties is computation heavy or not supported
     * at all. You may also want to allow more properties for filtering than you allowed for reading,
     * but be careful as this may allow guessing values of non-readable properties.
     *
     * @return array<non-empty-string, PropertyLinkInterface> The keys in the returned array are the names of the properties.
     */
    public function getFilteringProperties(): array;
}
