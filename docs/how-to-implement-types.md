# How to implement Types

You first need to choose a class in your application you want to limit access to or map the schema of.
Here we will continue the example from the [Wrapping](Wrapping.md) overview with articles and
authors.

All `Types` must implement at least `TypeInterface`.
It defines some basic behavior needed in all Types.

The following example shows a simple `ArticleType` for your `Article` class and an (also) simple
`UserType` for your `User` class. They do not enforce any access restrictions for now.
 
If you need at any point detailed information for any implemented method you can always refer to
the documentation of the corresponding interface.

```php
use EDT\Querying\Contracts\ConditionFactoryInterface;
use EDT\Querying\Contracts\FunctionInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
/**
 * @template-implements TypeInterface<Article>
 */
class ArticleType implements TypeInterface
{
    /**
     * @var ConditionFactoryInterface
     */
    private $conditionFactory;

    public function __construct(ConditionFactoryInterface $conditionFactory) {
        $this->conditionFactory = $conditionFactory;
    }

    public function getAccessCondition(): FunctionInterface {
        return $this->conditionFactory->true();
    }

    public function getEntityClass(): string {
        return Article::class;
    }

    public function isAvailable(): bool {
        return true;
    }

    public function getAliases() : array {
        return [];
    }
    
    public function isReferencable() : bool {
        return true;
    }
    
    public function isDirectlyAccessible() : bool {
        return true;
    }
}
```

```php
use EDT\Querying\Contracts\ConditionFactoryInterface;
use EDT\Querying\Contracts\FunctionInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
/**
 * @template-implements TypeInterface<User>
 */
class UserType implements TypeInterface
{
    /**
     * @var ConditionFactoryInterface
     */
    private $conditionFactory;

    public function __construct(ConditionFactoryInterface $conditionFactory) {
        $this->conditionFactory = $conditionFactory;
    }

    public function getAccessCondition(): FunctionInterface {
        return $this->conditionFactory->true();
    }

    public function getEntityClass(): string {
        return User::class;
    }

    public function isAvailable(): bool {
        return true;
    }
    
    public function getAliases() : array {
        return [];
    }
        
    public function isReferencable() : bool {
        return true;
    }
    
    public function isDirectlyAccessible() : bool {
        return true;
    }
}
```

Because these types implement the basic `TypeInterface` only we can't do much with them yet.
To make the Type actually be usable we need to implement additional interfaces.
Depending on the use case the implementation can be extremely simple
or very complex.

Available interfaces currently only provide ways to read corresponding
instances (and restrict such read access depending on authorizations). More information
on readability can be found in [Making a type readable](making-a-type-readable.md).
Beside that basic mapping capabilities are provided as described in
[Mapping capabilities](mapping.md).

Object update, creation and deletion is planned to be implemented but
not included in this library yet.

![Backend JSON:API Interfaces](./type-interfaces-overview.svg)

([Source file](./type-interfaces-overview.uxf), can be edited with [Umlet](https://www.umlet.com/))

## Limiting access

On the level of the `TypeInteface` there four ways to restrict accesses.
* Using `isAvailable()` you can fully hide Types depending on the state of the application (e.g. authorizations of the accessing user).
* Using `isReferencable` and `isDirectlyAccessible` you can prevent access to types similar to `isAvailable` but more fine-grained based on the nesting of Types.
* Using `getAccessCondition()` you can hide entity instances (not types!) depending on the state of the application.

This is kept separate from more specific interfaces as these restrictions are relevant for readability, updates,
deletions and, partly, for create actions as well. However, currently only readability and updatability are
implemented.

To give an example lets assume each article is preceded by a draft article. Articles are connected to their
draft version in a unidirectional one-to-one relationship. To give a practical example we make
both Types readable. The basics about readability are explained in [Making a type readable](making-a-type-readable.md).
Updatability is also supported and explained in [Updating via Types](Updating_via_Types.md).

In the examples we pass primitive values reflecting the permissions of the accessing user into the constructor.
Instead, you probably want to use a class like `Authorization`, `Role` or `User` to keep the constructor simple and conform to your authorization implementation.

The draft article may look like the following:

```php
use EDT\Querying\Contracts\ConditionFactoryInterface;
use EDT\Querying\Contracts\FunctionInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
/**
 * @template-implements ReadableTypeInterface<DraftArticle>
 */
class DraftArticleType implements ReadableTypeInterface
{
    /**
     * @var ConditionFactoryInterface
     */
    private $conditionFactory;

    public function __construct(ConditionFactoryInterface $conditionFactory) {
        $this->conditionFactory = $conditionFactory;
    }

    public function isAvailable(): bool {
        return true;
    }

    public function getAccessCondition(): FunctionInterface {
        return $this->conditionFactory->true();
    }

    public function getReadableProperties(): array {
        return [
            'text' => null,
            'author' => UserType::class,
        ];
    }

    public function getEntityClass(): string {
        return DraftArticle::class;
    }

    public function getFilterableProperties(): array {
        return $this->getReadableProperties();
    }

    public function getSortableProperties(): array {
        return $this->getReadableProperties();
    }
            
    public function isReferencable() : bool {
        return true;
    }
    
    public function isDirectlyAccessible() : bool {
        return true;
    }

    public function getDefaultSortMethods() : array {
        return [];
    }
    
    public function getAliases() : array {
        return [];
    }

    public function getInternalProperties() : array {
        return [];
    }
}
```

The `ArticleType` stays the same except that we added the relationship.

```php
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
/** 
 * @template-implements ReadableTypeInterface<Article>
 */
class ArticleType implements ReadableTypeInterface
{
    // ...

    public function getReadableProperties(): array {
        return [
           'title' => null,
           'text' => null,
           'author' => UserType::class,
           'draft' => DraftArticle::class,
       ];
    }

    // ...
}
```

### `getAccessCondition`

Now we want to limit the access to draft articles by making them visible to their authors only.
To do so, we can use the `getAccessCondition()` method. The returned instance specifies what requirements
must be met for a draft article to be readable. As our use case is that the author of the draft
must match the user currently accessing the Type, we need to inject an identifier of the current
user and return a condition that applies the filter on that identifier.

Instead of passing an implementation of the
`ConditionFactoryInterface`
you could instantiate instances of `FunctionInterface`
directly in `getAccessCondition()`. However, this is not recommended, as it makes your type implementation
dependent on the data source the `FunctionInterface` implementation was written for and thus less reusable for other data sources.

```php
use EDT\Querying\Contracts\ConditionFactoryInterface;
use EDT\Querying\Contracts\FunctionInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
/**
 * @template-implements TypeInterface<DraftArticle>
 */
class DraftArticleType implements ReadableTypeInterface
{
    /**
     * @var ConditionFactoryInterface
     */
    private $conditionFactory;
    /**
     * @var string
     */
    private $currentUserAccountName;

    public function __construct(ConditionFactoryInterface $conditionFactory, string $currentUserAccountName) {
        $this->conditionFactory = $conditionFactory;
        $this->currentUserAccountName = $currentUserAccountName;
    }

    public function isAvailable(): bool {
        return true;
    }

    public function getAccessCondition(): FunctionInterface {
        return $this->conditionFactory->propertyHasValue(
            $this->currentUserAccountName,
            'author', 'accountName'
        );
    }

    public function getInternalProperties() : array {
        return [
            'author' => \Tests\data\Types\AuthorType::class,
        ];
    }

    // ...
}
```

All properties used in `getAccessCondition` must be present in the array returned by `getInternalProperties`.
In this example we accessed `author` in the context of the `DraftArticleType`, hence its `getInternalProperties`
method must return the `author` property in with its relationship target, in this example `AuthorType`.
As the path in `getAccessCondition` continues with the `accountName` property `accountName`
must be present in the return of the `getInternalProperties` method of the `AuthorType`.
As it is a non-relationship the value is set to `null`, which means no path segment must
follow when `accountName` is used in a property path.

```php
class AuthorType implements ReadableTypeInterface
{
    // ...
    
    public function getInternalProperties() : array {
        return [
            'accountName' => null,
        ];
    }
    
    // ...
}
```

Because the values returned by `getInternalProperties` are used to define the
access condition only and can't be used for anything else (filtering, reading, sorting, updating)
you may choose to include all properties of your entity or just the ones you actually need in
conditions returned by `getAccessCondition` methods.

### `isAvailable`

Assuming only registered users can write articles we may want to hide the existence of draft articles from
non-registered users, as there is no case in which they can access one. To do so, we need to inject into the `DraftArticleType` the information if the currently logged-in
user is registered. Then we can disable the availability using `isAvailable()`.

```php
use EDT\Querying\Contracts\ConditionFactoryInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
/**
 * @template-implements TypeInterface<DraftArticle>
 */
class DraftArticleType implements ReadableTypeInterface
{
    /**
     * @var ConditionFactoryInterface
     */
    private $conditionFactory;
    /**
     * @var string
     */
    private $currentUserAccountName;
    /**
     * @var bool
     */
    private $registered;

    public function __construct(ConditionFactoryInterface $conditionFactory, string $currentUserAccountName, bool $registered) {
        $this->conditionFactory = $conditionFactory;
        $this->currentUserAccountName = $currentUserAccountName;
        $this->registered = $registered;
    }

    public function isAvailable(): bool {
        return $this->registered;
    }

    // ...
}
```

By disabling the availability for non-registered users not only will access to draft articles
be prevented but the `draft` relationship in the `ArticleType` will be hidden as well, even
if it is returned by `getReadableProperties()`, `getFilterableProperties()` or `getSortableProperties()`.

### `isDirectlyAccessible` and `isReferencable`

These two methods allow more fine-grained settings than `isAvailable`. `isAvailable` still takes
precedence, meaning if it returns `false` the return of `isDirectlyAccessible` and `isReferencable`
does not matter.

As an example we assume each `Article` can be connected to any number of comments, represented using
a `CommentType`. Both Types are readable, but in our example the intended process is that an article
is requested, which then contains all related comments. There is no case in which a list of comments,
or a specific comment, should be able to be loaded "directly" without going through the `Article` instance.

This restriction can be done by setting the return value of `CommentType::isDirectlyAccessible` to `false`.
It will be evaluated in the `GenericEntityFetcher`, preventing access to Types that can only be read
when included/nested in other types.

`isReferencable` works the other way around. Access to nested types is only allowed if it returns `true`.
This method must be honored by all `WrapperFactoryInterface` implementations. As they are responsible
to restrict the access to nested types considering all authorization settings (`isAvailable`, `getAccessCondition`,
`ReadableTypeInterface` and so on), including `isReferencable`.
