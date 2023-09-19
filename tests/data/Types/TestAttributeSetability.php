<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\PropertyBehavior\Attribute\AbstractAttributeSetability;
use Webmozart\Assert\Assert;

class TestAttributeSetability extends AbstractAttributeSetability
{
    public function __construct(
        string $propertyName,
        protected readonly array $propertyPath,
        protected readonly PropertyAccessorInterface $propertyAccessor,
        bool $optional
    ) {
        parent::__construct($propertyName, [], $optional);
    }

    protected function updateAttributeValue(object $entity, mixed $value): bool
    {
        $propertyPath = $this->propertyPath;
        $propertyName = array_pop($propertyPath);
        $target = [] === $propertyPath
            ? $entity
            : $this->propertyAccessor->getValueByPropertyPath($entity, ...$propertyPath);
        Assert::object($target);
        $this->propertyAccessor->setValue($target, $value, $propertyName);

        return false;
    }
}
