# Mapping capabilities

The main purpose of Types is to restrict accesses to
the backing classes and instances. However, if needed
they provide simple mapping capabilities too.

Different mappings can be done using different methods:
 
## `TypeInterface::getEntityClass()`

The naming of Types used in the examples (`XyzType` for a `Xyz` class) is a suggestion only. You
can you use `XyzSchema`, `XyzAuthorization` or `XyzRestriction` or anything else instead.

This gives you the possibility to name your Type
differently than the backing class if you are not satisfied with its name. For example, you can implement an `AuthorType` for a
`ArticleWriter` class. This will however not change the name of your backing class. It just
allows you to interact with the types under a new name in your application and stop
spreading the use of the old name.

## `TypeInterface::getAliases`

This method gives you the ability to set aliases or shortcuts for properties.

### Aliases

For example if your `Author` class has a property `name` but you want to use `fullName` as 
property in your Type schema instead you can add `fullName` to the return of `getPropertyAliases` with
the value `['name']`. The method will be invoked
automatically by the library to apply the mapping.
In combination with other methods like `TransferableTypeInterface::getReadableProperties` you
can either support both `fullName` and `name` in the Type or just one of them.

TODO: EXAMPLE OUT OF DATE, NEEDS TO BE UPDATED

```php
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
class AuthorType implements TransferableTypeInterface
{
    // ...

    public function getReadableProperties() : array {
        return [
            'fullName' => null,
            'name' => null,
        ];
    }

    public function getAliases(): array {
        return [
            'fullName' => ['name'],
        ];
    }
}
```

Another (experimental!) use case is redirecting the path to a different type without changing the current target object.
E.g. a `Book` object may contain properties like `booksAuthorName`, `booksAuthorAddress` and so on. Even if no `Author`
object exists it is still possible to create an `AuthorType` by adding an alias to the `BookType` with an empty target
as shown below:

TODO: EXAMPLE OUT OF DATE, NEEDS TO BE UPDATED

```php
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
class BookType implements TransferableTypeInterface
{
    // ...

    public function getReadableProperties() : array {
        return [
            'author' => AuthorType::class,
        ];
    }

    public function getAliases(): array {
        return [
            'author' => [],
        ];
    }
}
```

The `AuthorType` will then access the `Book` object to access the properties:

TODO: EXAMPLE OUT OF DATE, NEEDS TO BE UPDATED

```php
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
class AuthorType implements TransferableTypeInterface
{
    // ...
    
    public function getEntityClass() : string{
        return Book::class;
    }

    public function getReadableProperties() : array {
        return [
            'name' => null,
            'address' => null,
        ];
    }

    public function getAliases(): array {
        return [
            'name' => ['booksAuthorName'],
            'address' => ['booksAuthorAddress'],
        ];
    }
}
```

### Shortcuts

Instead of returning a single property name like when using aliases you can instead return a
path. When the path of the Type is used, the actual value of the property will be the one of the property of the targeted
backing object.

A usage example may be a heavily normalized database schema, that you want to
denormalize a bit for your API. Exposing separate Types for `Address` and `Street` objects
may not be necessary, and you may want to make its properties available directly
on your `AuthorType`.

```php
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
class AuthorType implements TransferableTypeInterface
{
    // ...

    public function getAliases(): array {
        return [
            'country' => ['address', 'country'],
            'city' => ['address', 'city'],
            'streetName' => ['address', 'street', 'name'],
            'streetNumber' => ['address', 'street', 'number'],
        ];
    }
}
```

## `TypeInterface::getAccessCondition()`

The access filters can be used too to implement a mapping, or to be more specific, split
your backing class into multiple types.

Referring to the `DraftArticleType` in [How to implement Types](how-to-implement-types.md) lets assume
you have stored draft articles and finished articles as a single `Article` class in your database
with a boolean `inDraftState` property to distinguish them from each other.

If you want to expose them as separate Types you can use a filter accessing drafts only in your
`DraftArticleType` and an inverted filter in your `ArticleType`.

```php
use EDT\Querying\Contracts\FunctionInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
class DraftArticleType implements TransferableTypeInterface
{
    // ...

    public function getEntityClass() : string{
        return Article::class;
    }

    public function getAccessCondition(): FunctionInterface {
        return $this->conditionFactory->propertyHasValue(true, 'inDraftState');
    }
}
```

```php
use EDT\Querying\Contracts\FunctionInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
class ArticleType implements TransferableTypeInterface
{
    // ...

    public function getEntityClass() : string{
        return Article::class;
    }

    public function getAccessCondition(): FunctionInterface {
        return $this->conditionFactory->propertyHasValue(false, 'inDraftState');
    }
}
```
