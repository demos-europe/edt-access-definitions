<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

/**
 * Please note that either
 * {@link ExposablePrimaryResourceTypeInterface::isExposedAsPrimaryResource()} or
 * {@link ExposableRelationshipTypeInterface::isExposedAsRelationship()} in this type must
 * return `true` for the readability to be usable via the generic JSON:API implementation.
 *
 * When used on an attribute the actual attribute value can be accessed. When used on a
 * relationship the relationship reference can be accessed, but to access the properties
 * of the relationship these properties must be set as readable too.
 */
abstract class AbstractReadability
{
    /**
     * Mark this property as readable, i.e. allow its value to be read.
     *
     * Using `$defaultField = true` means the property's value
     * can be accessed but will only be present in the JSON:API response when a
     * [sparse fieldset](https://jsonapi.org/format/#fetching-sparse-fieldsets) request was used
     * requesting that property. To automatically have the value present in the JSON:API response
     * when no sparse fieldset request is used, `true` must be used as `$defaultField` parameter.
     *
     * By passing a non-`null` $customRead` callable you can override the default behavior when the
     * property is read, e.g. when it is written into a JSON:API response. Normally the system
     * will get the value from an entity by looking for a property within it and directly
     * reading the value from it, circumventing any getter method. If an alias is set, it
     * will simply redirect the access through the different properties until the end of the
     * alias path is reached.
     *
     * Directly accessing the property is a good default behavior because it is consistent with
     * the behavior for Doctrine entities when they are filtered or sorted via their properties,
     * because there are no getters for them in the database.
     *
     * By passing the `$customRead` callable here the value will not be read directly from
     * the property anymore but from the callable instead, by passing the object as parameter
     * when calling the callable.
     *
     * This may introduce unintended inconsistencies: if the `$customRead` callable returns
     * a different value than the one stored in the property, then sorting and filtering
     * will be executed on a different value. It is not possible to set a custom
     * read callable for sorting/filtering due to compatibility requirements with Doctrine
     * as explained above.
     *
     * To avoid unintended inconsistencies you can **not** do the following with a property for
     * which a custom read callable was set (an exception will be thrown when the property is used):
     *
     * * set it as sortable
     * * set it as filterable
     * * set an alias
     *
     * If you accept the risk of inconsistencies you can set `$allowingInconsistencies` to `true`,
     * in which case the `$customRead` callable will be used when reading the value of the property,
     * and the value stored in the property will be used when filtering or sorting. The interaction
     * with aliases is undefined.
     *
     * @param bool $defaultField the field is to be returned in responses by default
     * @param bool $allowingInconsistencies sanity checks will be disabled
     *
     * @see https://jsonapi.org/format/#fetching-sparse-fieldsets JSON:API sparse fieldsets
     */
    public function __construct(
        private readonly bool $defaultField,
        private readonly bool $allowingInconsistencies
    ) {}

    public function isDefaultField(): bool
    {
        return $this->defaultField;
    }

    public function isAllowingInconsistencies(): bool
    {
        return $this->allowingInconsistencies;
    }
}
