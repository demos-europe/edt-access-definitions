<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

use Exception;

interface PropertySchemaInterface
{
    /**
     * Shall return the schema information of this attribute in the OpenAPI specification format.
     *
     * Examples:
     *
     * ----
     * ```
     * type: string
     * minLength: 0
     * maxLength: 5
     * ```
     * ----
     * ```
     * type: integer
     * format: int32
     * minimum: 1
     * maximum: 20
     * description: foobar
     * ```
     * ----
     *
     * @return array<non-empty-string, mixed>
     *
     * @throws Exception
     *
     * @see Type provides type constants
     * @see https://swagger.io/docs/specification/data-models/data-types/
     * @see https://github.com/OAI/OpenAPI-Specification/blob/main/versions/3.0.3.md#schema-object
     * @see https://json-schema.org/draft/2020-12/json-schema-validation.html
     */
    public function getPropertySchema(): array;
}
