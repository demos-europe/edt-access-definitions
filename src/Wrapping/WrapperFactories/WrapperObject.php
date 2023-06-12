<?php

declare(strict_types=1);

namespace EDT\Wrapping\WrapperFactories;

use EDT\JsonApi\RequestHandling\ContentField;
use EDT\Querying\Contracts\EntityBasedInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\Contracts\PropertyAccessException;
use EDT\Wrapping\Contracts\RelationshipAccessException;
use EDT\Wrapping\Contracts\Types\ExposableRelationshipTypeInterface;
use EDT\Wrapping\Contracts\Types\TransferableTypeInterface;
use EDT\Wrapping\Properties\EntityVerificationTrait;
use EDT\Wrapping\Properties\PropertyAccessibilityInterface;
use Exception;
use InvalidArgumentException;
use Safe\Exceptions\PcreException;
use Webmozart\Assert\Assert;
use function array_key_exists;
use function count;
use function Safe\preg_match;

/**
 * Wraps a given object, corresponding to a {@link TransferableTypeInterface}.
 *
 * Instances will provide read and write access to specific properties of the given object.
 *
 * The properties allowed to be read depend on the return of {@link TransferableTypeInterface::getReadableProperties()}.
 * Only those relationships will be readable whose target type return `true` in
 * {@link ExposableRelationshipTypeInterface::isExposedAsRelationship()}
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
     * @param non-empty-string $propertyName
     *
     * @return mixed|null The value of the accessed property. Each relationship entity will be  wrapped into another wrapper instance.
     */
    public function __get(string $propertyName)
    {
        // we allow reading of properties that are actually accessible
        $readableProperties = $this->type->getReadableProperties();

        // TODO: consider readability settings like default include and default field?

        if (array_key_exists($propertyName, $readableProperties->getAttributes())) {
            return $readableProperties->getAttribute($propertyName)->getValue($this->entity);
        }

        if (ContentField::ID === $propertyName) {
            return $readableProperties->getIdentifierReadability()->getValue($this->entity);
        }

        if ($readableProperties->hasToOneRelationship($propertyName)) {
            $readability = $readableProperties->getToOneRelationship($propertyName);
            $relationshipType = $readability->getRelationshipType();
            $relationshipEntity = $readability->getValue($this->entity, []);
            if (null === $relationshipEntity) {
                return null;
            }

            return $this->createWrapper($relationshipEntity, $relationshipType);
        }

        if ($readableProperties->hasToManyRelationship($propertyName)) {
            $readability = $readableProperties->getToManyRelationship($propertyName);
            $relationshipType = $readability->getRelationshipType();
            $relationshipEntities = $readability->getValue($this->entity, [], []);

            // wrap the entities
            return array_map(
                fn (object $entityToWrap) => $this->createWrapper($entityToWrap, $relationshipType),
                $relationshipEntities
            );
        }

        throw PropertyAccessException::propertyNotAvailableInReadableType(
            $propertyName,
            $this->type,
            $readableProperties->getPropertyKeys()
        );
    }

    /**
     * This method will prevent access to properties that should not be accessible.
     *
     * @param non-empty-string $propertyName
     * @param mixed $value The value to set. Will only be allowed if the property name matches with an allowed property
     *                     (must be {@link TransferableTypeInterface::getUpdatableProperties() updatable}.
     *
     * @throws AccessException
     */
    public function __set(string $propertyName, mixed $value): void
    {
        try {
            $propertyCollection = $this->type->getUpdatableProperties();
            $attributeSetabilities = $propertyCollection->getAttributes();
            $toOneSetabilities = $propertyCollection->getToOneRelationships();
            $toManySetabilities = $propertyCollection->getToManyRelationships();

            if (array_key_exists($propertyName, $attributeSetabilities)) {
                Assert::allKeyNotExists([$toOneSetabilities, $toManySetabilities], $propertyName);
                $setability = $attributeSetabilities[$propertyName];
                $this->assertMatchingEntityConditions($setability);
                $setability->updateAttributeValue($this->entity, $value);

                return;
            }

            if (array_key_exists($propertyName, $toOneSetabilities)) {
                Assert::allKeyNotExists([$attributeSetabilities, $toManySetabilities], $propertyName);
                $setability = $toOneSetabilities[$propertyName];
                $relationshipType = $setability->getRelationshipType();
                $relationshipClass = $relationshipType->getEntityClass();
                $relationship = $this->assertValidToOneValue($value, $relationshipClass);

                $this->assertMatchingEntityConditions($setability);

                // TODO: how to disallow a `null` relationship?
                if (null !== $relationship) {
                    $relationshipConditions = $setability->getRelationshipConditions();
                    try {
                        $relationshipType->assertMatchingEntity($relationship, $relationshipConditions);
                    } catch (Exception $exception) {
                        throw RelationshipAccessException::updateRelationshipCondition($this->type, $propertyName, $exception);
                    }
                }

                $setability->updateToOneRelationship($this->entity, $relationship);

                return;
            }

            if (array_key_exists($propertyName, $toManySetabilities)) {
                Assert::allKeyNotExists([$attributeSetabilities, $toOneSetabilities], $propertyName);
                $setability = $toManySetabilities[$propertyName];
                $relationshipType = $setability->getRelationshipType();
                $relationshipClass = $relationshipType->getEntityClass();
                $relationships = $this->assertValidToManyValue($value, $relationshipClass);

                $this->assertMatchingEntityConditions($setability);

                // TODO: how to disallow an empty relationship?
                if ([] !== $relationships) {
                    $relationshipConditions = $setability->getRelationshipConditions();
                    try {
                        $relationshipType->assertMatchingEntities($relationships, $relationshipConditions);
                    } catch (Exception $exception) {
                        throw RelationshipAccessException::updateRelationshipsCondition($this->type, $propertyName, $exception);
                    }
                }

                $setability->updateToManyRelationship($this->entity, $relationships);

                return;
            }
        } catch (\Exception $exception) {
            throw PropertyAccessException::update($this->type, $propertyName, $exception);
        }

        $setabilityKeys = array_keys(array_merge(
            $attributeSetabilities,
            $toOneSetabilities,
            $toManySetabilities,
        ));

        throw PropertyAccessException::propertyNotAvailableInUpdatableType($propertyName, $this->type, ...$setabilityKeys);
    }

    /**
     * Ensures that {@link $entity} matches the {@link PropertyAccessibilityInterface conditions} of the given updatability.
     *
     * @param PropertyAccessibilityInterface<TCondition> $property
     *
     * @throws InvalidArgumentException
     */
    protected function assertMatchingEntityConditions(PropertyAccessibilityInterface $property): void
    {
        try {
            $this->type->assertMatchingEntity($this->entity, $property->getEntityConditions());
        } catch (Exception $exception) {
            throw new InvalidArgumentException('The entity to be updated did not meet all property conditions.', 0, $exception);
        }
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
}
