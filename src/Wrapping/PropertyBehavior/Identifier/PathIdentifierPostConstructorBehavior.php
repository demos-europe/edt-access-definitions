<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier;

use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\CreationDataInterface;
use Webmozart\Assert\Assert;

/**
 * @template TEntity of object
 *
 * @template-implements IdentifierPostConstructorBehaviorInterface<TEntity>
 */
class PathIdentifierPostConstructorBehavior implements IdentifierPostConstructorBehaviorInterface
{
    /**
     * @param class-string<TEntity> $entityClass
     * @param non-empty-list<non-empty-string> $propertyPath
     */
    public function __construct(
        protected readonly string $entityClass,
        protected readonly mixed $propertyPath,
        protected readonly PropertyAccessorInterface $propertyAccessor,
        protected readonly bool $optional
    ) {
    }

    public function setIdentifier(object $entity, CreationDataInterface $entityData): bool
    {
        $entityIdentifier = $entityData->getEntityIdentifier();
        if (null === $entityIdentifier) {
            if (!$this->optional) {
                throw new \InvalidArgumentException('Entity identifier must be provided.');
            }

            return false;
        }

        $propertyPath = $this->propertyPath;
        $propertyName = array_pop($propertyPath);
        $target = [] === $propertyPath
            ? $entity
            : $this->propertyAccessor->getValueByPropertyPath($entity, ...$propertyPath);
        Assert::object($target);

        $this->propertyAccessor->setValue($target, $entityIdentifier, $propertyName);

        return false;
    }

    public function getRequiredAttributes(): array
    {
        return [];
    }

    public function getOptionalAttributes(): array
    {
        return [];
    }

    public function getRequiredToOneRelationships(): array
    {
        return [];
    }

    public function getOptionalToOneRelationships(): array
    {
        return [];
    }

    public function getRequiredToManyRelationships(): array
    {
        return [];
    }

    public function getOptionalToManyRelationships(): array
    {
        return [];
    }
}
