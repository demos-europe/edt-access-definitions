<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\PropertyBehavior\Attribute\AttributeReadabilityInterface;

class TestAttributeReadability implements AttributeReadabilityInterface
{
    public function __construct(
        protected readonly array $propertyPath,
        protected readonly PropertyAccessorInterface $propertyAccessor
    ) {}

    public function getPropertySchema(): array
    {
        return [];
    }

    public function getValue(object $entity): mixed
    {
        return $this->propertyAccessor->getValueByPropertyPath($entity, ...$this->propertyPath);
    }

    public function isDefaultField(): bool
    {
        return false;
    }
}
