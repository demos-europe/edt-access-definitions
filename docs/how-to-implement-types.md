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
use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\FunctionInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
/**
 * @template-implements TypeInterface<Article>
 */
class ArticleType implements TypeInterface
{
    /**
     * @var PathsBasedConditionFactoryInterface
     */
    private $conditionFactory;

    public function __construct(PathsBasedConditionFactoryInterface $conditionFactory) {
        $this->conditionFactory = $conditionFactory;
    }

    public function getAccessCondition(): FunctionInterface {
        return $this->conditionFactory->true();
    }

    public function getEntityClass(): string {
        return Article::class;
    }

    public function getAliases() : array {
        return [];
    }
}
```

```php
use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\FunctionInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
/**
 * @template-implements TypeInterface<User>
 */
class UserType implements TypeInterface
{
    /**
     * @var PathsBasedConditionFactoryInterface
     */
    private $conditionFactory;

    public function __construct(PathsBasedConditionFactoryInterface $conditionFactory) {
        $this->conditionFactory = $conditionFactory;
    }

    public function getAccessCondition(): FunctionInterface {
        return $this->conditionFactory->true();
    }

    public function getEntityClass(): string {
        return User::class;
    }

    public function getAliases() : array {
        return [];
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

On the level of the `TypeInteface` there are four ways to restrict accesses.
* Using `ExposableRelationshipTypeInterface::isExposedAsRelationship`
* Using `ExposablePrimaryResourceTypeInterface::isExposedAsPrimaryResource` (in JSON:API context)
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

TODO: EXAMPLE OUT OF DATE, NEEDS TO BE UPDATED

```php
use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\FunctionInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
/**
 * @template-implements TransferableTypeInterface<DraftArticle>
 */
class DraftArticleType implements TransferableTypeInterface
{
    /**
     * @var PathsBasedConditionFactoryInterface
     */
    private $conditionFactory;

    public function __construct(PathsBasedConditionFactoryInterface $conditionFactory) {
        $this->conditionFactory = $conditionFactory;
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

TODO: EXAMPLE OUT OF DATE, NEEDS TO BE UPDATED

```php
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
/** 
 * @template-implements TransferableTypeInterface<Article>
 */
class ArticleType implements TransferableTypeInterface
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
use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\FunctionInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
/**
 * @template-implements TypeInterface<DraftArticle>
 */
class DraftArticleType implements TransferableTypeInterface
{
    /**
     * @var PathsBasedConditionFactoryInterface
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
class AuthorType implements \EDT\Wrapping\Contracts\Types\TransferableTypeInterface
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
