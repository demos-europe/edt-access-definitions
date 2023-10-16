<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Attribute;

use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;
use Webmozart\Assert\Assert;

/**
 * When used, instances require a specific attribute to be present in the request, which will
 * be directly used as constructor argument.
 */
class AttributeConstructorBehavior implements ConstructorBehaviorInterface
{
    /**
     * @param non-empty-string $attributeName
     * @param non-empty-string $argumentName
     * @param null|callable(CreationDataInterface): mixed $fallback
     */
    public function __construct(
        protected readonly string $attributeName,
        protected readonly string $argumentName,
        protected readonly mixed $fallback
    ) {}

    public function getArguments(CreationDataInterface $entityData): array
    {
        $attributes = $entityData->getAttributes();
        if (array_key_exists($this->attributeName, $attributes)) {
            $attributeValue = $attributes[$this->attributeName];
        } elseif (null !== $this->fallback) {
            $attributeValue = ($this->fallback)($entityData);
        } else {
            throw new \InvalidArgumentException("No attribute '$this->attributeName' present and no fallback set.");
        }

        return [
            $this->argumentName => $attributeValue,
        ];
    }

    public function getRequiredAttributes(): array
    {
        return [$this->attributeName];
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
