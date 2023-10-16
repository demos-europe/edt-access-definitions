<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Attribute\Factory;

use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\Attribute\AttributeConstructorBehavior;
use EDT\Wrapping\PropertyBehavior\NonRelationshipConstructorBehaviorFactoryInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;

class AttributeConstructorBehaviorFactory implements NonRelationshipConstructorBehaviorFactoryInterface
{
    /**
     * @param non-empty-string|null $argumentName
     * @param null|callable(CreationDataInterface): mixed $fallback
     */
    public function __construct(
        protected readonly ?string $argumentName,
        protected readonly mixed $fallback
    ) {}

    public function createNonRelationshipConstructorBehavior(string $name, array $propertyPath, string $entityClass): ConstructorBehaviorInterface
    {
        return new AttributeConstructorBehavior(
            $name,
            $this->argumentName ?? $name,
            $this->fallback
        );
    }
}