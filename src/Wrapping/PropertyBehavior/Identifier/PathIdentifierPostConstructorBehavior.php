<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\Contracts\ContentField;
use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\EntityDataInterface;
use EDT\Wrapping\PropertyBehavior\Identifier\Factory\IdentifierPostConstructorBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\Relationship\ToMany\AbstractPropertySetBehavior;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * This class will directly set the {@link ContentField::ID} property via a given path.
 *
 * The means of setting the identifier are provided to instances via a {@link PropertyAccessorInterface}.
 *
 * @template TEntity of object
 *
 * @template-implements IdentifierPostConstructorBehaviorInterface<TEntity>
 * @template-extends AbstractPropertySetBehavior<TEntity>
 */
class PathIdentifierPostConstructorBehavior extends AbstractPropertySetBehavior implements IdentifierPostConstructorBehaviorInterface
{
    /**
     * @param class-string<TEntity> $entityClass
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param list<DrupalFilterInterface> $entityConditions
     */
    public function __construct(
        protected readonly string $entityClass,
        protected readonly mixed $propertyPath,
        protected readonly PropertyAccessorInterface $propertyAccessor,
        OptionalField $optional,
        array $entityConditions
    ) {
        parent::__construct(ContentField::ID, $entityConditions, $optional);
    }

    /**
     * @param list<DrupalFilterInterface> $entityConditions
     *
     * @return IdentifierPostConstructorBehaviorFactoryInterface<object>
     */
    public static function createFactory(OptionalField $optional, PropertyAccessorInterface $propertyAccessor, array $entityConditions): IdentifierPostConstructorBehaviorFactoryInterface
    {
        return new class($optional, $propertyAccessor, $entityConditions) implements IdentifierPostConstructorBehaviorFactoryInterface {
            /**
             * @param list<DrupalFilterInterface> $entityConditions
             */
            public function __construct(
                protected readonly OptionalField $optional,
                protected readonly PropertyAccessorInterface $propertyAccessor,
                protected readonly array $entityConditions
            ){}

            public function __invoke(array $propertyPath, string $entityClass): IdentifierPostConstructorBehaviorInterface
            {
                return new PathIdentifierPostConstructorBehavior($entityClass, $propertyPath, $this->propertyAccessor, $this->optional, $this->entityConditions);
            }

            public function createIdentifierPostConstructorBehavior(array $propertyPath, string $entityClass): IdentifierPostConstructorBehaviorInterface
            {
                return $this($propertyPath, $entityClass);
            }
        };
    }

    public function getDescription(): string
    {
        $descriptionPart = ' The identifier value will then directly be set into the entity corresponding to the modified resource.';

        return ($this->optional->equals(OptionalField::YES)
            ? 'Allows the resource ID to be present in the request but does not require it.'
            : 'Requires the resource ID to be present in the request. ').$descriptionPart;
    }

    public function setIdentifier(object $entity, CreationDataInterface $entityData): array
    {
        $entityIdentifier = $entityData->getEntityIdentifier();
        if (!$this->hasPropertyValue($entityData)) {
            if (!$this->optional->equals(OptionalField::YES)) {
                throw new InvalidArgumentException('Entity identifier must be provided.');
            }

            return [];
        }

        $propertyPath = $this->propertyPath;
        $propertyName = array_pop($propertyPath);
        $target = [] === $propertyPath
            ? $entity
            : $this->propertyAccessor->getValueByPropertyPath($entity, ...$propertyPath);
        Assert::object($target);

        $this->propertyAccessor->setValue($target, $entityIdentifier, $propertyName);

        return [];
    }

    public function isIdRequired(): bool
    {
        return $this->optional->equals(OptionalField::NO);
    }

    public function isIdOptional(): bool
    {
        return $this->optional->equals(OptionalField::YES);
    }

    protected function hasPropertyValue(EntityDataInterface $entityData): bool
    {
        return $entityData instanceof CreationDataInterface && null !== $entityData->getEntityIdentifier();
    }

    protected function setPropertyValue(object $entity, EntityDataInterface $entityData): array
    {
        // should not get triggered, as we check the type beforehand
        Assert::isInstanceOf($entityData, CreationDataInterface::class);

        return $this->setIdentifier($entity, $entityData);
    }
}
