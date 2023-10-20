<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier\Factory;

use EDT\Wrapping\Contracts\ContentField;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;
use EDT\Wrapping\PropertyBehavior\Identifier\DataProvidedIdentifierConstructorBehavior;

/**
 * @template TEntity of object
 *
 * @template-implements IdentifierConstructorBehaviorFactoryInterface<TEntity>
 */
class DataProvidedIdentifierConstructorBehaviorFactory implements IdentifierConstructorBehaviorFactoryInterface
{
    /**
     * @param non-empty-string|null $argumentName
     */
    public function __construct(
        protected readonly ?string $argumentName
    ) {}

    public function createIdentifierConstructorBehavior(array $propertyPath, string $entityClass): ConstructorBehaviorInterface
    {
        return new DataProvidedIdentifierConstructorBehavior($this->argumentName ?? ContentField::ID);
    }
}
