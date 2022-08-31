<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use EDT\Querying\Contracts\FunctionInterface;

/**
 * @template T of object
 *
 * @template-extends TypeInterface<T>
 */
interface CreatableTypeInterface extends TypeInterface
{
    /**
     * The return defines what properties are allowed to be set when a new instance of
     * the {@link TypeInterface::getEntityClass() backing class} is created.
     *
     * Each entry consists of the name of the property as key and a {@link FunctionInterface condition} (for relationships)
     * or `null` (for non-relationships) as value. The condition is needed because users may be
     * allowed to use another {@link TypeInterface type} as relationship value
     * when creating an instance, while not being allowed to read this other type. Because of that
     * the return of the {@link TypeInterface::getAccessCondition()} method is ignored to determine
     * the usability of a relationship value and the condition returned by this method is used instead.
     *
     * This behavior is to be distinguished from the validation of the state of the created {@link TypeInterface::getEntityClass() instance}
     * itself. Such validation is not in the scope of this library, but we assume it happens authorization-independent, which
     * is the reason why we need the conditions returned by this method.
     *
     * As an example for the distinction between authorization dependent and independent validation lets
     * assume an application allows to add `Book` entities to a book store. Each book must be connected to a
     * `Publisher` entity. In general, we allow the connection from any `Book` to any `Publisher` in the
     * database. However, _who_ is allowed to create that connection is a matter of authorization. Only
     * users are assigned to a `Publisher` are allowed to add books to that `Publisher`. By returning
     * an array `['publisher' => $currentUserAssignedToPublisherCondition]` we can allow to create `Book`
     * instances with a publisher initially set while still preventing the user from assigning a
     * `Publisher` instance he is not allowed to use.
     *
     * @return array<string,FunctionInterface<bool>|null>
     */
    public function getInitializableProperties(): array;
}
