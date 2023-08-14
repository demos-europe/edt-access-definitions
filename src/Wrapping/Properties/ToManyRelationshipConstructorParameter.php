<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\JsonApi\RequestHandling\Body\CreationRequestBody;
use EDT\JsonApi\Requests\PropertyUpdaterTrait;
use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-extends AbstractRelationshipConstructorParameter<TCondition, TSorting>
 */
class ToManyRelationshipConstructorParameter extends AbstractRelationshipConstructorParameter
{
    use PropertyUpdaterTrait;

    /**
     * @return list<object>
     */
    public function getValue(CreationRequestBody $requestBody): array
    {
        $relationshipRefs = $requestBody->getToManyRelationshipReferences($this->parameterName);

        return $this->determineToManyRelationshipValues(
            $this->getRelationshipType(),
            $this->getRelationshipConditions(),
            $relationshipRefs
        );
    }
}
