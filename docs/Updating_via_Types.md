# Updating via Types

To allow updates using a type it needs to implement the `UpdatableTypeInterface` to return
the property names that are allowed to be updated in the `getUpdatableProperties` method.

As with similar methods the returned array defines the property names as key and
relationships as value, with the value being `null` in case of a non-relationship.

To apply the restrictions of the type to an object you need to wrap the object inside
a special wrapper instance. Wrappers are created with `WrapperFactoryInterface` implementations,
however not all implementations support updatability. For instance `WrapperArrayFactory` will
only copy values from an entity into an array. Changes to the arrays content will not be
carried over to the original entity. On the other hand `WrapperObjectFactory` will return
wrappers that contain the original entity. Read and write accesses to the wrapper accessing
the schema of the type will be directed to the backing entity.

To get wrappers for your entities you can either:
* pass the factory into the constructor of classes like the `GenericEntityFetcher`.
  Its methods will automatically return the wrappers instead of the original entities.
* invoke the factories `createWrapper` method manually with the entity to wrap, and the
  type to use as schema.

Please note that the type should be tailored to your entity instance. Otherwise the type
and thus the wrapper
may allow accesses to properties that do not exist in the backing entity, which will
result in errors when actually used.

The wrapper implementation of `WrapperObjectFactory` will allow the usage of
`$wrapper->set<CamelCasedPropertyName>($value)` and `$wrapper-><propertyName> = $value`.
For example in case of the property `title` you can either use `$wrapper->setTitle($value)` or
`$wrapper->title = $value`. However, in both cases your IDE will not be able to help
you with syntax highlighting, as the available properties are generated dynamically
during runtime depending on the type the wrapper instance was based on.

When the setter or property is used to set a value the wrapper will check the
authorizations and, if granted, sets the value in the target property of the backing
entity (which may differ from the property used when accessing the wrapper depending
on the aliases set up for the type implementation). If you need side effects to happen
when a property changes, eg. update a `lastModified` field when any other field is
updated, you need a customized wrapper class.
