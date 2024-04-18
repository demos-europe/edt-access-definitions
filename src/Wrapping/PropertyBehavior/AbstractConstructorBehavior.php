<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use EDT\JsonApi\ApiDocumentation\OptionalField;
use EDT\Wrapping\CreationDataInterface;
use InvalidArgumentException;

/**
 * Instances of this class can be used to connect resource properties with behavior to be executed on a creation request.
 */
abstract class AbstractConstructorBehavior implements ConstructorBehaviorInterface
{
    use IdUnrelatedTrait;

    /**
     * The combination of `$optional` and `$customBehavior` will strongly influence the behavior of the created
     * instance. Consider the following cases
     *
     * 1. {@link Optional::NO} && no custom behavior given: Property is required in the request and is used directly, if it is not present an exception is thrown
     * 2. {@link Optional::NO} && custom behavior given: Property is required in the request and will pass through the callable, if it is not present an exception is thrown
     * 3. {@link Optional::YES} && no custom behavior given: Property is optional in the request, if it is present, it is used directly, if not, nothing is done and this instance has no effect
     * 4. {@link Optional::YES} && custom behavior given: Property is optional in the request, regardless of whether it is present or not, the callable will be invoked
     *
     * @param non-empty-string $resourcePropertyName The name of the resource property that is looked for in the request body
     * @param non-empty-string $constructorArgumentName The constructor argument the resulting value will be used for
     * @param null|callable(CreationDataInterface): array{mixed, list<non-empty-string>} $customBehavior Custom behavior to be executed, the result is a pair with the first item being the value to be used for the constructor argument and the second one being the list of resource properties that were adjusted differently than requested
     */
    public function __construct(
        protected readonly string $resourcePropertyName,
        protected readonly string $constructorArgumentName,
        protected readonly OptionalField $optional,
        protected readonly mixed $customBehavior
    ) {}

    public function getArguments(CreationDataInterface $entityData): array
    {
        if ($this->optional->equals(OptionalField::NO)) {
            if ($this->isValueInRequest($entityData)) {
                if (null === $this->customBehavior) {
                    // The (required) property is present in the request and no custom behavior is set. Hence, we simply
                    // use the given request value directly.
                    $argumentValue = $this->getArgumentValueFromRequest($entityData);
                    $propertyDeviations = [];
                } else {
                    // The (required) property is present in the request and a custom behavior is set. Hence, we will
                    // call the custom behavior and use its return.
                    [$argumentValue, $propertyDeviations] = ($this->customBehavior)($entityData);
                }
            } else {
                // The (required) property is not present in the request and no custom behavior is set that could be
                // used to create a fallback. This should have been noticed and handled during the request validation.
                // At this point we can only raise an exception.
                throw new InvalidArgumentException("Required property '$this->resourcePropertyName' not present and no fallback available.");
            }
        } elseif (null !== $this->customBehavior) {
            // Regardless of whether the property is present in the request or not, if a custom behavior is set we will
            // always call it, instead of using the value in the request.
            [$argumentValue, $propertyDeviations] = ($this->customBehavior)($entityData);
        } else {
            // The (optional) property is not present in the request and no custom behavior is set that could be
            // used to create a fallback. This is completely valid, and we will simply not execute any behavior,
            // i.e. not return a constructor argument to be used.
            return [];
        }

        // we determined the value to use as constructor argument and a list of corresponding deviations
        return [$this->constructorArgumentName => [$argumentValue, $propertyDeviations]];
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

    /**
     * @return list<non-empty-string>
     */
    protected function getRequiredPropertyList(): array
    {
        return $this->optional->equals(OptionalField::NO) ? [$this->resourcePropertyName] : [];
    }

    /**
     * @return list<non-empty-string>
     */
    protected function getOptionalPropertyList(): array
    {
        return $this->optional->equals(OptionalField::YES) ? [$this->resourcePropertyName] : [];
    }

    abstract protected function isValueInRequest(CreationDataInterface $entityData): bool;

    abstract protected function getArgumentValueFromRequest(CreationDataInterface $entityData): mixed;
}
