<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\JsonApi\RequestHandling\ContentField;
use EDT\Querying\Contracts\PathsBasedInterface;
use InvalidArgumentException;
use Webmozart\Assert\Assert;
use function array_key_exists;

/**
 * Collection of readable properties.
 *
 * For now set to be `final`, as extending classes may otherwise choose to return different
 * results for multiple calls of the same method or evne weaken sanity checks, which may
 * have unpredictable effects.
 *
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 * @template TEntity of object
 */
final class ReadabilityCollection
{
    /**
     * @param array<non-empty-string, AttributeReadabilityInterface<TEntity>> $attributes
     * @param array<non-empty-string, ToOneRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>> $toOneRelationships
     * @param array<non-empty-string, ToManyRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>> $toManyRelationships
     * @param IdReadabilityInterface<TEntity> $idReadability
     */
    public function __construct(
        protected readonly array $attributes,
        protected readonly array $toOneRelationships,
        protected readonly array $toManyRelationships,
        protected readonly IdReadabilityInterface $idReadability
    ) {
        $allProperties = $this->getAllProperties();
        // check for duplicated property names
        Assert::count(
            $allProperties,
            count($this->attributes) + count($this->toOneRelationships) + count($this->toManyRelationships)
        );
        // check for invalid property names
        Assert::keyNotExists($allProperties, ContentField::ID);
        Assert::keyNotExists($allProperties, ContentField::TYPE);
    }

    /**
     * @return array<non-empty-string, AttributeReadabilityInterface<TEntity>>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<non-empty-string, ToOneRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>>
     */
    public function getToOneRelationships(): array
    {
        return $this->toOneRelationships;
    }

    /**
     * @return array<non-empty-string, ToManyRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>>
     */
    public function getToManyRelationships(): array
    {
        return $this->toManyRelationships;
    }

    /**
     * @return array<non-empty-string, AttributeReadabilityInterface<TEntity>|ToOneRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>|ToManyRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>>
     */
    public function getAllProperties(): array
    {
        return array_merge(
            $this->attributes,
            $this->toOneRelationships,
            $this->toManyRelationships
        );
    }

    /**
     * @return array<non-empty-string, ToOneRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>|ToManyRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>>
     */
    public function getRelationships(): array
    {
        return array_merge($this->toOneRelationships, $this->toManyRelationships);
    }

    /**
     * @param non-empty-string $propertyName
     */
    public function hasRelationship(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->toOneRelationships)
            || array_key_exists($propertyName, $this->toManyRelationships);
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return ToOneRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>|ToManyRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>
     *
     * @throws InvalidArgumentException
     */
    public function getRelationship(string $propertyName): ToOneRelationshipReadabilityInterface|ToManyRelationshipReadabilityInterface
    {
        return $this->toOneRelationships[$propertyName]
            ?? $this->toManyRelationships[$propertyName]
            ?? throw new InvalidArgumentException("No relationship for property name '$propertyName'.");
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return AttributeReadabilityInterface<TEntity>
     *
     * @throws InvalidArgumentException
     */
    public function getAttribute(string $propertyName): AttributeReadabilityInterface
    {
        return $this->attributes[$propertyName]
            ?? throw new InvalidArgumentException("No attribute for property name '$propertyName'.");
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return ToOneRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>
     */
    public function getToOneRelationship(string $propertyName): ToOneRelationshipReadabilityInterface
    {
        return $this->toOneRelationships[$propertyName]
            ?? throw new InvalidArgumentException("No to-one relationship for property name '$propertyName'.");
    }

    /**
     * @param non-empty-string $propertyName
     *
     * @return ToManyRelationshipReadabilityInterface<TCondition, TSorting, TEntity, object>
     */
    public function getToManyRelationship(string $propertyName): ToManyRelationshipReadabilityInterface
    {
        return $this->toManyRelationships[$propertyName]
            ?? throw new InvalidArgumentException("No to-many relationship for property name '$propertyName'.");
    }

    /**
     * @param non-empty-string $propertyName
     */
    public function hasToOneRelationship(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->toOneRelationships);
    }

    /**
     * @param non-empty-string $propertyName
     */
    public function hasToManyRelationship(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->toManyRelationships);
    }

    /**
     * @return list<non-empty-string>
     */
    public function getPropertyKeys(): array
    {
        return array_keys($this->getAllProperties());
    }

    /**
     * Provides a readability for the identifier that uniquely identifies an instance of the
     * corresponding entity.
     *
     * @return IdReadabilityInterface<TEntity>
     */
    public function getIdentifierReadability(): IdReadabilityInterface
    {
        return $this->idReadability;
    }
}
