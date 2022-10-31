<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

interface AliasableTypeInterface
{
    /**
     * Get the properties of the schema of this type that are aliases to different properties
     * in the schema of the target {@link TypeInterface}.
     *
     * If a path was directed to a property name of the schema of this type (e.g. for filtering
     * or sorting) and it is only an alias, then the return of this method will contain
     * that property name as a key and the (array) path to the actual property of the
     * {@link TypeInterface::getEntityClass() backing entity class} as value.
     *
     * Make sure to **never** use an alias path over to-many relationships. For example aliasing
     * an `authorName` property in a `Book` Type to `['author', 'name']` is fine **if** your book
     * always has a single author. It is also ok if that author has a list of names stored in its
     * `name` property, because the `name` property is the last part in the alias path.
     * However, in case your `Book` has multiple authors, and you use an alias path like
     * `['authors', 'name']` you may get errors or undesired/unexpected behavior.
     *
     * @return array<non-empty-string, non-empty-list<non-empty-string>>
     */
    public function getAliases(): array;
}
