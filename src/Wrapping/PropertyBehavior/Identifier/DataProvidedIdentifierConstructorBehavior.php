<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier;

use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorBehaviorInterface;
use Webmozart\Assert\Assert;

/**
 * When used, instances require a specific attribute to be present in the request, which will
 * be directly used as constructor argument.
 */
class DataProvidedIdentifierConstructorBehavior implements ConstructorBehaviorInterface
{
    /**
     * @param non-empty-string $argumentName
     */
    public function __construct(
        protected readonly string $argumentName
    ) {}

    public function getArguments(CreationDataInterface $entityData): array
    {
        $entityIdentifier = $entityData->getEntityIdentifier();
        Assert::stringNotEmpty($entityIdentifier);

        return [$this->argumentName => [$entityIdentifier, []]];
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
