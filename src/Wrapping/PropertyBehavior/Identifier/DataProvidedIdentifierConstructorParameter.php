<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier;

use EDT\Wrapping\CreationDataInterface;
use EDT\Wrapping\PropertyBehavior\ConstructorParameterInterface;
use Webmozart\Assert\Assert;

/**
 * When used, instances require a specific attribute to be present in the request, which will
 * be directly used as constructor argument.
 */
class DataProvidedIdentifierConstructorParameter implements ConstructorParameterInterface
{
    /**
     * @param non-empty-string $argumentName
     */
    public function __construct(
        protected readonly string $argumentName
    ) {}

    /**
     * @return non-empty-string
     */
    public function getArgument(CreationDataInterface $entityData): string
    {
        $entityIdentifier = $entityData->getEntityIdentifier();
        Assert::stringNotEmpty($entityIdentifier);

        return $entityIdentifier;
    }

    public function getArgumentName(): string
    {
        return $this->argumentName;
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
