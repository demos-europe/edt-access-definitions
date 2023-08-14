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
class ToOneRelationshipConstructorParameter extends AbstractRelationshipConstructorParameter
{
    use PropertyUpdaterTrait;

    public function getValue(CreationRequestBody $requestBody): ?object
    {
        $relationshipRef = $requestBody->getToOneRelationshipReference($this->parameterName);

        return $this->determineToOneRelationshipValue(
            $this->getRelationshipType(),
            $this->getRelationshipConditions(),
            $relationshipRef
        );
    }
}
