<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use EDT\JsonApi\RequestHandling\Body\CreationRequestBody;
use EDT\Querying\Contracts\PathsBasedInterface;

/**
 * @template TCondition of PathsBasedInterface
 * @template TSorting of PathsBasedInterface
 *
 * @template-implements ConstructorParameterInterface<TCondition, TSorting>
 */
class AttributeConstructorParameter implements ConstructorParameterInterface
{
    /**
     * @param non-empty-string $parameterName
     */
    public function __construct(
        protected readonly string $parameterName
    ) {}

    public function getValue(CreationRequestBody $requestBody): mixed
    {
        return $requestBody->getAttributeValue($this->parameterName);
    }
}
