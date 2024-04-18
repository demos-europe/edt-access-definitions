<?php

declare(strict_types=1);

namespace EDT\Wrapping;

interface EntityDataInterface
{
    /**
     * @return non-empty-string
     */
    public function getType(): string;

    /**
     * @return array<non-empty-string, mixed>
     */
    public function getAttributes(): array;

    /**
     * @return array<non-empty-string, JsonApiRelationship|null>
     */
    public function getToOneRelationships(): array;

    /**
     * @return array<non-empty-string, list<JsonApiRelationship>>
     */
    public function getToManyRelationships(): array;
}
