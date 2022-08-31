## Making a Type readable

A Type that should be avaiable for reading, filtering and sorting needs to implement
`ReadableTypeInterface`.
We continue the implementation from [how to implement types](how-to-implement-types.md)
and add the three methods needed for overall readability.

In these the properties of the backing class that should be accessible can be defined. In case
of the `ArticleType` these are for now the `title` and `text` as non-relationships and
a relationsihp to the `UserType`.

If we do not want to restrict any accesses the method implementations can simply define
all properties in the `getReadableProperties()` return its result in the other two
methods as is done below for the `ArticleType`.

Note that the types do **not** define a complete data model but can be understood as allow-list instead.
The `getReadableProperties()` method defines only which properties of the backing object class are part of the schema of the type.
Because multiple Types can exist for a single backing class we also set the relationship for each property.  
As `title`, `text` and are non-relationships we can simply set `null`.

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
       ];
    }

    public function getFilterableProperties(): array {
        return $this->getReadableProperties();
    }

    public function getSortableProperties(): array {
        return $this->getReadableProperties();
    }
}
```

Let's suppose we want to give strikes to users posting inappropriate comments under articles.
This way when a user gets obtrusive a moderator can see if it is continuous behaviour and ban
her/him as a consequence. We do however not want to expose this functionality to the average user,
let alone making the number of strikes visible to the struck user.

To do so we can add a `strikeCount` property to the `UserType`, but make it visible for only for
moderators. To do so the `UserType` needs to get access the role of the currently logged-in user. How
this is done depends heavily on the implementation of authorizations in your application. In this
example we will simply assume that the Type instances are created on each request and the
`UserType` receives a `moderator` boolean on instantiation. The result may look something like
this:

```php
use EDT\Querying\Contracts\ConditionFactoryInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
/** 
 * @template-implements ReadableTypeInterface<Article>
 */
class UserType implements ReadableTypeInterface
{
    // ...

    /**
     * @var bool
     */
    private $moderator;

    public function __construct(ConditionFactoryInterface $conditionFactory, bool $moderator) {
        $this->conditionFactory = $conditionFactory;
        $this->moderator = $moderator;
    }

    // ...

    public function getReadableProperties(): array {
        $readableProperties = [
           'accountName' => null,
           'articles' => ArticleType::class,
        ];

        if ($this->moderator) {
            $readableProperties['strikeCount'] = null;
        }
        
        return $readableProperties;
    }

    public function getFilterableProperties(): array {
        return $this->getReadableProperties();
    }

    public function getSortableProperties(): array {
        return $this->getReadableProperties();
    }
}
```

As can be seen the `strikeCount` of any user will be only made readable if the accessing user is
a moderator. In most cases the `getFilterableProperties()` and `getSortableProperties()` methods
can simply return the result of `getReadableProperties()`. Returning more properties in these
two methods than in `getReadableProperties()` may accidentally allow users to guess the values
of a property that is not supposed to be readable. However, returning fewer properties may be
desired if sorting/filtering needs to be limited due to performance reasons.
