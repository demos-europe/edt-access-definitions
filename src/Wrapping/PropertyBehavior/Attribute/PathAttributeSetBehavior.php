<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Attribute;

use EDT\ConditionFactory\DrupalFilterInterface;
use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\PropertyBehavior\Attribute\Factory\PathAttributeSetBehaviorFactory;
use Webmozart\Assert\Assert;

/**
 * @template TEntity of object
 *
 * @template-extends AbstractAttributeSetBehavior<TEntity>
 */
class PathAttributeSetBehavior extends AbstractAttributeSetBehavior
{
    use AttributeTrait;

    /**
     * @param non-empty-string $propertyName
     * @param class-string<TEntity> $entityClass
     * @param list<DrupalFilterInterface> $entityConditions
     * @param non-empty-list<non-empty-string> $propertyPath
     */
    public function __construct(
        string $propertyName,
        protected readonly string $entityClass,
        array $entityConditions,
        protected readonly mixed $propertyPath,
        protected readonly PropertyAccessorInterface $propertyAccessor,
        OptionalField $optional
    ) {
        parent::__construct($propertyName, $entityConditions, $optional);
    }

    /**
     * @param list<DrupalFilterInterface> $entityConditions
     *
     * @return PathAttributeSetBehaviorFactory<object>
     */
    public static function createFactory(
        PropertyAccessorInterface $propertyAccessor,
        array $entityConditions,
        OptionalField $optional
    ): PathAttributeSetBehaviorFactory {
        return new PathAttributeSetBehaviorFactory($propertyAccessor, $entityConditions, $optional);
    }

    protected function updateAttributeValue(object $entity, mixed $value): array
    {
        $value = $this->assertValidValue($value);

        $propertyPath = $this->propertyPath;
        $propertyName = array_pop($propertyPath);
        $target = [] === $propertyPath
            ? $entity
            : $this->propertyAccessor->getValueByPropertyPath($entity, ...$propertyPath);
        Assert::object($target);
        $this->propertyAccessor->setValue($target, $value, $propertyName);

        return [];
    }

    public function getDescription(): string
    {
        $propertyPathString = implode('.', $this->propertyPath);

        return
            ($this->optional->equals(OptionalField::YES)
                ? "Allows an attribute `$this->propertyName` to be present in the request body, but does not require it. "
                : "Requires an attribute `$this->propertyName` to be present in the request body.")
            . "The attribute will be stored in $this->entityClass::$propertyPathString. "
            . ([] === $this->entityConditions
                ? 'The entity does not need to '
                : 'The entity must ')
            . 'match additional conditions beside the ones defined by its type.';
    }
}
