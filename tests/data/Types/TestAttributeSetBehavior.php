<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Querying\Contracts\PropertyAccessorInterface;
use EDT\Wrapping\PropertyBehavior\Attribute\AbstractAttributeSetBehavior;
use Webmozart\Assert\Assert;

class TestAttributeSetBehavior extends AbstractAttributeSetBehavior
{
    public function __construct(
        string $propertyName,
        protected readonly array $propertyPath,
        protected readonly PropertyAccessorInterface $propertyAccessor,
        OptionalField $optional
    ) {
        parent::__construct($propertyName, [], $optional);
    }

    protected function updateAttributeValue(object $entity, mixed $value): array
    {
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
    }
}
