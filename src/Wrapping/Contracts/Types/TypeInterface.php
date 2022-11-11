<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TSorting of \EDT\Querying\Contracts\PathsBasedInterface
 * @template TEntity of object
 *
 * @template-extends EntityBasedInterface<TEntity>
 */
interface TypeInterface extends EntityBasedInterface
{
    /**
     * Returns the condition limiting the access to {@link TypeInterface::getEntityClass() entities}
     * corresponding to this type.
     *
     * The returned condition is applied to the schema of this type and **not** the schema of
     * the {@link TypeInterface::getEntityClass() entity}. To enable the engine to resolve aliases
     * you need to define all properties used in these conditions in the
     * {@link TypeInterface::getInternalProperties()} method of the types corresponding to your
     * path segments.
     *
     * This allows you to access internal properties not exposed in any way. Aliases will
     * be applied to the paths used in the returned condition, meaning you can define
     * aliases in {@link AliasableTypeInterface::getAliases()} and use them in your condition.
     *
     * The reason why the schema of the type needs to be accessed instead of the schema
     * of the {@link TypeInterface::getEntityClass() entity} is because you may want to use
     * the condition to apply it to a different schema than the one of the entity.
     *
     * For example your entity may reside in your relational database and may be highly
     * normalized. The schema of this type can lift some of these normalizations for the everyday use using
     * {@link AliasableTypeInterface::getAliases()}. If you maintain an object based database next
     * to your relational one in which the objects are already denormalized and maybe even already
     * in the schema of this type (e.g. for search request optimizations), then a
     * condition accessing the schema of the normalized entity would be incompatible with that
     * object database.
     *
     * Beside limiting the access depending on the authorization of the accessing user, this
     * condition also be used to filter out invalid instances of the backing class:
     * E.g. even though a database may store different animals as a single `Animal` entity/table
     * there may be different types for different kinds of animals (`CatType`, `DogType`, ...).
     * For a list query on a `CatType` the condition returned by this method must define
     * limits to only get `Animal` instances that are a `Cat`.
     *
     * @return TCondition
     */
    public function getAccessCondition(): PathsBasedInterface;

    /**
     * The properties of this type that are allowed to be use in conditions returned by
     * {@link TypeInterface::getAccessCondition()} and sort methods returned by
     * {@link TypeInterface::getDefaultSortMethods()}.
     *
     * These properties are used to convert a path directed to the schema of this type
     * into the schema of the backing object. Any aliasing defined by {@link AliasableTypeInterface::getAliases()}
     * will be applied automatically.
     *
     * @return array<non-empty-string, TypeInterface<TCondition, TSorting, object>|null> The mapping from property name (in the schema of this type)
     *                                   to the target type of the relationship,
     *                                   or `null` if the property is a non-relationship.
     */
    public function getInternalProperties(): array;

    /**
     * Get the sort method to apply when a collection of this property is fetched directly
     * and no sort methods were specified.
    * .*
     * Inside the method your paths can access all properties defined in the {@link TypeInterface::getInternalProperties()}
     * Thus you have unrestricted access to all properties of that schema and no limitations by {@link SortableTypeInterface::getSortableProperties}
     * will be applied.
     *
     * Note however, that if {@link AliasableTypeInterface::getAliases() aliases} are configured,
     * that they will be applied. The reasoning is the same as is in
     * {@link TypeInterface::getAccessCondition()}, where it is already explained in detail.
     *
     * Return an empty array to not define any default sorting.
     *
     * @return list<TSorting>
     */
    public function getDefaultSortMethods(): array;
}
