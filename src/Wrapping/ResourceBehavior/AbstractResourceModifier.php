<?php

declare(strict_types=1);

namespace EDT\Wrapping\ResourceBehavior;

use EDT\JsonApi\RequestHandling\ExpectedPropertyCollection;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\EntityDataInterface;
use EDT\Wrapping\PropertyBehavior\PropertyConstrainingInterface;
use EDT\Wrapping\PropertyBehavior\PropertySetabilityInterface;

abstract class AbstractResourceModifier
{
    public function getExpectedProperties(): ExpectedPropertyCollection
    {
        return new ExpectedPropertyCollection(
            $this->getRequiredAttributeNames(),
            $this->getRequiredToOneRelationshipIdentifiers(),
            $this->getRequiredToManyRelationshipIdentifiers(),
            $this->getOptionalAttributeNames(),
            $this->getOptionalToOneRelationshipIdentifiers(),
            $this->getOptionalToManyRelationshipIdentifiers()
        );
    }

    /**
     * @return list<non-empty-string>
     */
    protected function getRequiredAttributeNames(): array
    {
        $parameters = array_map(
            static fn (PropertyConstrainingInterface $constrain): array => $constrain->getRequiredAttributes(),
            $this->getParameterConstrains()
        );

        return array_merge([], ...$parameters);
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    protected function getRequiredToOneRelationshipIdentifiers(): array
    {
        $parameters = array_map(
            static fn (PropertyConstrainingInterface $constrain): array => $constrain->getRequiredToOneRelationships(),
            $this->getParameterConstrains()
        );

        return array_merge([], ...$parameters);
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    protected function getRequiredToManyRelationshipIdentifiers(): array
    {
        $parameters = array_map(
            static fn (PropertyConstrainingInterface $constrain): array => $constrain->getRequiredToManyRelationships(),
            $this->getParameterConstrains()
        );

        return array_merge([], ...$parameters);
    }

    /**
     * @return list<non-empty-string>
     */
    protected function getOptionalAttributeNames(): array
    {
        $parameters = array_map(
            static fn (PropertyConstrainingInterface $constrain): array => $constrain->getOptionalAttributes(),
            $this->getParameterConstrains()
        );

        return array_merge([], ...$parameters);
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    protected function getOptionalToOneRelationshipIdentifiers(): array
    {
        $parameters = array_map(
            static fn (PropertyConstrainingInterface $constrain): array => $constrain->getOptionalToOneRelationships(),
            $this->getParameterConstrains()
        );

        return array_merge([], ...$parameters);
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    protected function getOptionalToManyRelationshipIdentifiers(): array
    {
        $parameters = array_map(
            static fn (PropertyConstrainingInterface $constrain): array => $constrain->getOptionalToManyRelationships(),
            $this->getParameterConstrains()
        );

        return array_merge([], ...$parameters);
    }

    /**
     * @template TEnt of object
     *
     * @param list<PropertySetabilityInterface<TEnt>> $setabilities
     * @param TEnt $entity
     */
    protected function getSetabilitiesSideEffect(array $setabilities, object $entity, EntityDataInterface $entityData): bool
    {
        $nestedSideEffects = array_map(
            static fn (PropertySetabilityInterface $setability): bool => $setability->updateProperty($entity, $entityData),
            $setabilities
        );

        foreach ($nestedSideEffects as $sideEffect) {
            if ($sideEffect) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<PropertyConstrainingInterface>
     */
    abstract protected function getParameterConstrains(): array;
}
