<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier;

use EDT\Querying\Contracts\PropertyAccessorInterface;
use Webmozart\Assert\Assert;

/**
 * @template TEntity of object
 *
 * @template-implements IdentifierReadabilityInterface<TEntity>
 */
class PathIdentifierReadability implements IdentifierReadabilityInterface
{
    /**
     * @param non-empty-list<non-empty-string> $propertyPath
     * @param class-string<TEntity> $entityClass
     */
    public function __construct(
        protected readonly string $entityClass,
        protected readonly mixed $propertyPath,
        protected readonly PropertyAccessorInterface $propertyAccessor
    ) {}

    public function getValue(object $entity): string
    {
        $propertyValue = $this->propertyAccessor->getValueByPropertyPath($entity, ...$this->propertyPath);
        Assert::stringNotEmpty($propertyValue);

        return $propertyValue;
    }
}
