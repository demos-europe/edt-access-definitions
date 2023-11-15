<?php

declare(strict_types=1);

namespace EDT\Wrapping\WrapperFactories;

use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\ContentField;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\Types\NamedTypeInterface;
use EDT\Wrapping\Contracts\Types\PropertyReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\EntityData;
use EDT\Wrapping\PropertyBehavior\EntityVerificationTrait;
use EDT\Wrapping\ResourceBehavior\ResourceUpdatability;
use Exception;
use InvalidArgumentException;
use Safe\Exceptions\PcreException;
use Webmozart\Assert\Assert;
use function array_key_exists;
use function count;
use function in_array;
use function Safe\preg_match;

/**
 * Wraps a given object, corresponding to a {@link TransferableTypeInterface}.
 *
 * Instances will provide read and write access to specific properties of the given object.
 *
 * The properties allowed to be read depend on the return of {@link TransferableTypeInterface::getReadability()}.
 *
 * Returned relationships will be wrapped themselves inside {@link WrapperObject} instances.
 *
 * @template TEntity of object
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 */
class WrapperObject
{
    use EntityVerificationTrait;

    /**
     * @var non-empty-string
     */
    private const METHOD_PATTERN = '/(get|set)([A-Z_]\w*)/';

    /**
     * @param TEntity $entity
     * @param TransferableTypeInterface<TCondition, TSorting, TEntity> $type
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        protected readonly object $entity,
        protected readonly TransferableTypeInterface $type
    ) {
        try {
            // ensure the given entity instance actually matches the restrictions of the given type
            $this->type->assertMatchingEntity($this->entity, []);
        } catch (Exception $exception) {
            throw new InvalidArgumentException("Given entity is not a valid '{$this->type->getTypeName()}' type instance.", 0, $exception);
        }
    }

    /**
     * @return TransferableTypeInterface<TCondition, TSorting, TEntity>
     */
    public function getResourceType(): TransferableTypeInterface
    {
        return $this->type;
    }

    /**
     * @param non-empty-string $methodName
     * @param array<int|string, mixed> $arguments
     *
     * @return mixed|null|void If no parameters given:<ul>
     *   <li>In case of a relationship: an array, {@link WrapperObject} or <code>null</code></li>
     *   <li>Otherwise a primitive type</li><li>If parameters given: `void`</li></ul>
     */
    public function __call(string $methodName, array $arguments = [])
    {
        [$access, $propertyName] = $this->parseMethodAccess($methodName);
        $argumentsCount = count($arguments);

        if ('get' === $access && 0 === $argumentsCount) {
            return $this->__get($propertyName);
        }
        if ('set' === $access && 1 === $argumentsCount) {
            $this->__set($propertyName, array_pop($arguments));

            return;
        }

        throw AccessException::unexpectedArguments($this->type, 0, $argumentsCount);
    }

    /**
     * @return non-empty-string
     */
    public function getTypeName(): string
    {
        return $this->type->getTypeName();
    }

    /**
     * @return non-empty-string
     */
    public function getIdentifier(): string
    {
        return $this->type->getReadability()->getIdentifierReadability()->getValue($this->entity);
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @throws InvalidArgumentException if no attribute is available for the given name
     */
    public function getAttribute(string $propertyName): mixed
    {
        // we allow reading of properties that are actually accessible
        $readableProperties = $this->type->getReadability();

        if (array_key_exists($propertyName, $readableProperties->getAttributes())) {
            return $readableProperties->getAttribute($propertyName)->getValue($this->entity);
        }

        throw new InvalidArgumentException("No attribute named `$propertyName` available.");
    }

    /**
     * @param non-empty-string $propertyName
     * @param list<TCondition> $conditions
     *
     * @return WrapperObject<object, TCondition, TSorting>|null
     */
    public function getToOneRelationship(string $propertyName, array $conditions = []): ?WrapperObject
    {
        // we allow reading of properties that are actually accessible
        $readableProperties = $this->type->getReadability();

        $readability = $readableProperties->getToOneRelationship($propertyName);
        $relationshipType = $readability->getRelationshipType();
        $relationshipEntity = $readability->getValue($this->entity, $conditions);
        if (null === $relationshipEntity) {
            return null;
        }

        return $this->createWrapper($relationshipEntity, $relationshipType);
    }

    /**
     * @param non-empty-string $propertyName
     * @param list<TCondition> $conditions
     *
     * @return non-empty-string|null
     */
    public function getToOneRelationshipReference(string $propertyName, array $conditions = []): ?string
    {
        return $this->getToOneRelationship($propertyName, $conditions)?->getIdentifier();
    }

    /**
     * @param non-empty-string $propertyName
     * @param list<TCondition> $conditions
     * @param list<TSorting> $sortMethods
     *
     * @return list<WrapperObject<object, TCondition, TSorting>>
     */
    public function getToManyRelationships(string $propertyName, array $conditions = [], array $sortMethods = []): array
    {
        // we allow reading of properties that are actually accessible
        $readableProperties = $this->type->getReadability();

        $readability = $readableProperties->getToManyRelationship($propertyName);
        $relationshipType = $readability->getRelationshipType();
        $relationshipEntities = $readability->getValue($this->entity, $conditions, $sortMethods);

        // wrap the entities
        return array_map(
            fn (object $entityToWrap) => $this->createWrapper($entityToWrap, $relationshipType),
            $relationshipEntities
        );
    }

    /**
     * @param non-empty-string $propertyName
     * @param list<TCondition> $conditions
     * @param list<TSorting> $sortMethods
     *
     * @return list<non-empty-string>
     */
    public function getToManyRelationshipReferences(string $propertyName, array $conditions = [], array $sortMethods = []): array
    {
        return array_map(
            static fn (WrapperObject $wrapper): string => $wrapper->getIdentifier(),
            $this->getToManyRelationships($propertyName, $conditions, $sortMethods)
        );
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return mixed|null The value of the accessed property. Each relationship entity will be  wrapped into another wrapper instance.
     */
    public function __get(string $propertyName)
    {
        // we allow reading of properties that are actually accessible
        $readableProperties = $this->type->getReadability();

        // TODO: consider readability settings like default include and default field?

        if (array_key_exists($propertyName, $readableProperties->getAttributes())) {
            return $readableProperties->getAttribute($propertyName)->getValue($this->entity);
        }

        if (ContentField::ID === $propertyName) {
            return $this->getIdentifier();
        }

        if (ContentField::TYPE === $propertyName) {
            return $this->getTypeName();
        }

        if ($readableProperties->hasToOneRelationship($propertyName)) {
            return $this->getToOneRelationship($propertyName, []);
        }

        if ($readableProperties->hasToManyRelationship($propertyName)) {
            return $this->getToManyRelationships($propertyName, [], []);
        }

        throw PropertyAccessException::propertyNotAvailableInReadableType(
            $propertyName,
            $this->type,
            $readableProperties->getPropertyKeys()
        );
    }

    /**
     * @param non-empty-string $propertyName
     */
    public function setAttribute(string $propertyName, mixed $value): bool
    {
        $entityUpdatability = $this->type->getUpdatability();
        $entityData = $this->createAttributeEntityData($propertyName, $value);

        // check if entity is allowed to be adjusted
        return $this->updateEntity($entityUpdatability, $entityData);
    }

    /**
     * @template TRelationship of object
     *
     * @param non-empty-string $propertyName
     * @param PropertyReadableTypeInterface<TCondition, TSorting, TRelationship>&NamedTypeInterface&EntityBasedInterface<TRelationship> $relationshipType
     */
    public function setToOneRelationship(
        string $propertyName,
        ?object $relationship,
        PropertyReadableTypeInterface&NamedTypeInterface&EntityBasedInterface $relationshipType
    ): bool {
        $entityUpdatability = $this->type->getUpdatability();

        $relationshipClass = $relationshipType->getEntityClass();
        $relationship = $this->assertValidToOneValue($relationship, $relationshipClass);
        $entityData = $this->createToOneRelationshipEntityData($propertyName, $relationship, $relationshipType);

        return $entityUpdatability->updateProperties($this->entity, $entityData);
    }

    /**
     * @template TRelationship of object
     *
     * @param non-empty-string $propertyName
     * @param list<TRelationship> $values
     * @param PropertyReadableTypeInterface<TCondition, TSorting, TRelationship>&NamedTypeInterface&EntityBasedInterface<TRelationship> $relationshipType
     */
    public function setToManyRelationship(
        string $propertyName,
        array $values,
        PropertyReadableTypeInterface&NamedTypeInterface&EntityBasedInterface $relationshipType
    ): bool {
        $entityUpdatability = $this->type->getUpdatability();

        // create entity data
        $relationshipClass = $relationshipType->getEntityClass();
        $relationships = $this->assertValidToManyValue($values, $relationshipClass);
        $entityData = $this->createToManyRelationshipEntityData($propertyName, $relationships, $relationshipType);

        return $this->updateEntity($entityUpdatability, $entityData);
    }

    /**
     * @template TRelationship of object
     *
     * @param array<non-empty-string, PropertyReadableTypeInterface<TCondition, TSorting, TRelationship>&NamedTypeInterface&EntityBasedInterface<TRelationship>> $relationshipTypes
     *
     * @return PropertyReadableTypeInterface<TCondition, TSorting, TRelationship>&NamedTypeInterface&EntityBasedInterface<TRelationship>
     */
    protected function getSingleRelationshipType(array $relationshipTypes): object
    {
        return match (count($relationshipTypes)) {
            0 => throw new InvalidArgumentException('There were no update behaviors defined for the property'),
            1 => array_pop($relationshipTypes),
            default => throw new InvalidArgumentException('Multiple update behaviors with different relationship type names were defined. This is currently not supported.'),
        };
    }

    /**
     * @param ResourceUpdatability<TCondition, TSorting, TEntity> $updatability
     */
    protected function updateEntity(ResourceUpdatability $updatability, EntityData $entityData): bool
    {
        $relevantEntityConditions = $updatability->getEntityConditions($entityData);
        $this->type->assertMatchingEntity($this->entity, $relevantEntityConditions);

        return $updatability->updateProperties($this->entity, $entityData);
    }

    /**
     * This method will prevent access to properties that should not be accessible.
     *
     * @param non-empty-string $propertyName
     * @param mixed $value The value to set. Will only be allowed if the property name matches with an allowed property
     *                     (must be {@link TransferableTypeInterface::getUpdatability() updatable}.
     *
     * @throws AccessException
     */
    public function __set(string $propertyName, mixed $value): void
    {
        try {
            $entityUpdatability = $this->type->getUpdatability();
            $attributeNames = $entityUpdatability->getAttributeNames();
            $toOneRelationshipNames = $entityUpdatability->getToOneRelationshipNames();
            $toManyRelationshipNames = $entityUpdatability->getToManyRelationshipNames();

            if (in_array($propertyName, $attributeNames, true)) {
                Assert::allNotContains($toOneRelationshipNames, $propertyName);
                Assert::allNotContains($toManyRelationshipNames, $propertyName);
                $this->setAttribute($propertyName, $value);

                return;
            }

            if (in_array($propertyName, $toOneRelationshipNames, true)) {
                Assert::allNotContains($attributeNames, $propertyName);
                Assert::allNotContains($toManyRelationshipNames, $propertyName);
                Assert::nullOrObject($value);

                $entityUpdatability = $this->type->getUpdatability();
                $relationshipTypes = $entityUpdatability->getToOneRelationshipTypes($propertyName);
                $relationshipType = $this->getSingleRelationshipType($relationshipTypes);

                $this->setToOneRelationship($propertyName, $value, $relationshipType);

                return;
            }

            if (in_array($propertyName, $toManyRelationshipNames, true)) {
                Assert::allNotContains($attributeNames, $propertyName);
                Assert::allNotContains($toOneRelationshipNames, $propertyName);
                Assert::isList($value);
                Assert::allObject($value);

                $entityUpdatability = $this->type->getUpdatability();
                $relationshipTypes = $entityUpdatability->getToManyRelationshipTypes($propertyName);
                $relationshipType = $this->getSingleRelationshipType($relationshipTypes);

                $this->setToManyRelationship($propertyName, $value, $relationshipType);

                return;
            }
        } catch (\Exception $exception) {
            throw PropertyAccessException::update($this->type, $propertyName, $exception);
        }

        $setBehaviorKeys = array_merge(
            $attributeNames,
            $toOneRelationshipNames,
            $toManyRelationshipNames,
        );

        throw PropertyAccessException::propertyNotAvailableInUpdatableType($propertyName, $this->type, ...$setBehaviorKeys);
    }

    /**
     * @param list<TCondition> $conditions
     */
    public function isMatchingAllConditions(array $conditions): bool
    {
        return $this->type->isMatchingEntity($this->entity, $conditions);
    }

    /**
     * @param non-empty-string $propertyName
     */
    public function getPropertyValue(string $propertyName): mixed
    {
        return $this->__get($propertyName);
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @throws AccessException
     */
    public function setPropertyValue(string $propertyName, mixed $value): void
    {
        $this->__set($propertyName, $value);
    }

    /**
     * Expects a getter or setter property name (e.g. `getFoo`/`setFoo`). Splits method name into
     * the `get` or `set` part and the upper-cased property name.
     *
     * @param non-empty-string $methodName
     *
     * @return array{0: 'set'|'get', 1: non-empty-string}
     *
     * @throws AccessException
     * @throws InvalidArgumentException
     */
    protected function parseMethodAccess(string $methodName): array
    {
        try {
            preg_match(self::METHOD_PATTERN, $methodName, $match);
        } catch (PcreException $exception) {
            throw new InvalidArgumentException($methodName, 0, $exception);
        }
        Assert::isArray($match);
        Assert::count($match, 3);
        Assert::keyExists($match, 1);
        Assert::keyExists($match, 2);

        $propertyName = $match[2];
        Assert::string($propertyName);
        $access = $match[1];
        $propertyName = lcfirst($propertyName);

        if ('' === $propertyName || ('get' !== $access && 'set' !== $access)) {
            throw AccessException::failedToParseAccessor($this->type, $methodName);
        }

        return [$access, $propertyName];
    }

    /**
     * @return TEntity
     *
     * @internal Warning: exposing the backing object is dangerous, as it allows to read values
     * unrestricted not only from the returned object but all its relationships.
     *
     * @deprecated use {@link self::getEntity} instead
     */
    public function getObject(): object
    {
        return $this->entity;
    }

    /**
     * @return TEntity
     *
     * @internal Warning: exposing the backing object is dangerous, as it allows to read values
     * unrestricted not only from the returned object but all its relationships.
     */
    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * Creates a wrapper around an instance of a {@link EntityBasedInterface::getEntityClass() backing object}.
     *
     * @template TRelationship of object
     *
     * @param TRelationship $entity
     * @param TransferableTypeInterface<TCondition, TSorting, TRelationship> $type
     *
     * @return WrapperObject<TRelationship, TCondition, TSorting>
     *
     * @throws InvalidArgumentException
     */
    protected function createWrapper(object $entity, TransferableTypeInterface $type): WrapperObject
    {
        return new WrapperObject($entity, $type);
    }

    /**
     * @param non-empty-string $propertyName
     */
    protected function createAttributeEntityData(string $propertyName, mixed $value): EntityData
    {
        $attributes = [$propertyName => $value];

        return new EntityData($this->type->getTypeName(), $attributes, [], []);
    }

    /**
     * @template TRelationship of object
     *
     * @param non-empty-string $propertyName
     * @param TRelationship|null $relationship
     * @param PropertyReadableTypeInterface<TCondition, TSorting, TRelationship>&NamedTypeInterface $relationshipType
     */
    protected function createToOneRelationshipEntityData(
        string $propertyName,
        ?object $relationship,
        PropertyReadableTypeInterface&NamedTypeInterface $relationshipType
    ): EntityData {
        $toOneRelationships = [
            $propertyName => null === $relationship ? null : [
                ContentField::ID => $relationshipType->getReadability()->getIdentifierReadability()->getValue($relationship),
                ContentField::TYPE => $relationshipType->getTypeName(),
            ],
        ];

        return new EntityData($this->type->getTypeName(), [], $toOneRelationships, []);
    }

    /**
     * @template TRelationship of object
     *
     * @param non-empty-string $propertyName
     * @param list<TRelationship> $relationships
     * @param PropertyReadableTypeInterface<TCondition, TSorting, TRelationship>&NamedTypeInterface $relationshipType
     */
    protected function createToManyRelationshipEntityData(
        string $propertyName,
        array $relationships,
        PropertyReadableTypeInterface&NamedTypeInterface $relationshipType
    ): EntityData {

        $relationshipTypeName = $relationshipType->getTypeName();

        $toManyRelationships = [
            $propertyName => array_map(
                static fn(object $relationship): array => [
                    ContentField::ID => $relationshipType->getReadability()->getIdentifierReadability()->getValue($relationship),
                    ContentField::TYPE => $relationshipTypeName,
                ],
                $relationships
            ),
        ];

        return new EntityData($this->type->getTypeName(), [], [],$toManyRelationships);
    }
}
