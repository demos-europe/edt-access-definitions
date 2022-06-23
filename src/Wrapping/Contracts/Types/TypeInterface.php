<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\FunctionInterface;
use EDT\Querying\Contracts\SortMethodInterface;

/**
 * @template T of object The class of the backing entity returned by {@link TypeInterface::getEntityClass()}
 */
interface TypeInterface
{
    /**
     * @return class-string<T> The fully qualified name of the class backing entities this type defines.
     */
    public function getEntityClass(): string;

    /**
     * This is **not** a simplified version of {@link TypeInterface::getAccessCondition()}.
     * While the condition will prevent access to
     * instances, this method prevents exposing the existence of this type at all.
     *
     * If for example the Types `A`, `B` and `C` have relationship to Type `X` and only specific
     * users should be able to know that Type `X` exists you don't need to hide the
     * relationship using {@link ReadableTypeInterface::getReadableProperties()} or similar methods with
     * duplicated conditions in A, B and C but can instead add the condition once here for Type `X`.
     *
     * If this method returns false all relationships to the Type will be hidden and no
     * access to instances of this Type will be possible. This does not only affect direct read
     * accesses but also filtering if it uses properties of this Type directly or indirectly.
     *
     * @return bool True if the existence of this type is allowed to be known at all,
     *              may depend on the user accessing the type. False otherwise.
     */
    public function isAvailable(): bool;

    /**
     * Determines if this type can be referenced by other types.
     *
     * This affects if instances of this type can be accessed (e.g. read, if readable) through
     * a relationship referencing this type.
     *
     * Like {@link TypeInterface::isDirectlyAccessible()} this has **no** precedence over {@link TypeInterface::isAvailable()}. Even
     * if {@link TypeInterface::isReferencable()} returns `true`, the type can still not be accessed
     * as long as {@link TypeInterface::isAvailable()} returns `false`.
     */
    public function isReferencable(): bool;

    /**
     * Determines if this type can be used directly.
     * This affects if instances of this type can be accessed (eg. read, if readable) directly
     * without being reached through a reference.
     *
     * Like {@link TypeInterface::isReferencable()} this has **no** precedence over {@link TypeInterface::isAvailable()}. Even
     * if {@link TypeInterface::isDirectlyAccessible()} returns `true`, the type can still not be accessed
     * as long as {@link TypeInterface::isAvailable()} returns `false`.
     */
    public function isDirectlyAccessible(): bool;

    /**
     * Returns the condition limiting the access to {@link TypeInterface::getEntityClass() entities}
     * corresponding to this type.
     *
     * The returned condition is applied to the schema of this type and **not** the schema of
     * the {@link TypeInterface::getEntityClass() entity}. To not restrict your condition
     * building to the properties available to users you need to define all properties
     * used in these conditions in the {@link TypeInterface::getInternalProperties()} method
     * of the types corresponding to your path segments.
     *
     * This allows you to access internal properties not exposed in any way. Aliases will
     * be applied to the paths used in the returned condition, meaning you can define
     * aliases in {@link TypeInterface::getAliases()} and use them in your condition.
     *
     * The reason why the schema of the type needs to be accessed instead of the schema
     * of the {@link TypeInterface::getEntityClass() entity} is because you may want to use
     * the condition to apply it to a different schema than the one of the entity.
     *
     * For example your entity may reside in your relational database and may be highly
     * normalized. The schema of this type can lift some of these normalizations for the everyday use using
     * {@link TypeInterface::getAliases()}. If you maintain an object based database next
     * to your relational one in which the objects are already denormalized and maybe even already
     * in the schema of this type (eg. for search request optimizations), then a
     * condition accessing the schema of the normalized entity would be incompatible with that
     * object database.
     *
     * Beside limiting the access depending on the authorization of the accessing user, this
     * condition also be used to filter out invalid instances of the backing class:
     * Eg. even though a database may store different animals as a single `Animal` entity/table
     * there may be different types for different kinds of animals (`CatType`, `DogType`, ...).
     * For a list query on a `CatType` the condition returned by this method must define
     * limits to only get `Animal` instances that are a `Cat`.
     *
     * @return FunctionInterface<bool>
     */
    public function getAccessCondition(): FunctionInterface;

    /**
     * Get the properties of the schema of this type that are aliases to different properties
     * in the schema of the target {@link TypeInterface}.
     *
     * If a path was directed to a property name of the schema of this type (eg. for filtering
     * or sorting) and it is only an alias, then the return of this method will contain
     * that property name as a key and the (array) path to the actual property of the
     * {@link TypeInterface::getEntityClass() backing entity class} as value.
     * 
     * Make sure to **never** use an alias path over to-many relationships. For example aliasing
     * an `authorName` property in a `Book` Type to `['author', 'name']` is fine **if** your book
     * always has a single author. It is also ok if that author has a list of names stored in its
     * `name` property, because the `name` property is the last part in the alias path.
     * However in case your `Book` has multiple authors and you use an alias path like
     * `['authors', 'name']` you may get errors or undesired/unexpected behavior.
     *
     * @return array<string,array<int,string>>
     */
    public function getAliases(): array;

    /**
     * The properties of this type that are allowed to be use in conditions returned by
     * {@link TypeInterface::getAccessCondition()} and sort methods returned by
     * {@link TypeInterface::getDefaultSortMethods()}.
     *
     * These properties are used to convert a path directed to the schema of this type
     * into the schema of the backing object. Any aliasing defined by {@link TypeInterface::getAliases()}
     * will be applied automatically.
     *
     * @return array<string,string|null> The mapping from property name (in the schema of
     * this type) to the identifier of the target type of the relationship, or `null` if
     * the property is a non-relationship.
     */
    public function getInternalProperties(): array;

    /**
     * Get the sort method to apply when a collection of this property is fetched directly
     * and no sort methods were specified.
     *
     * The schema the sort methods property paths access must be the one of the this type, **not** the one of the
     * {@link TypeInterface::getEntityClass() entity class}.
    .*
     * Inside the method your paths can access all properties defined in the {@link TypeInterface::getInternalProperties()}
     * Thus you have unrestricted access to all properties of that schema and no limitations by {@link SortableTypeInterface::getSortableProperties}
     * will be applied.
     *
     * Return an empty array to not define any default sorting.
     *
     * @return array<int,SortMethodInterface>
     */
    public function getDefaultSortMethods(): array;
}
