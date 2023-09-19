<?php

declare(strict_types=1);

namespace EDT\Wrapping;

class EntityData implements EntityDataInterface
{
    /**
     * @param non-empty-string $type
     * @param array<non-empty-string, mixed> $attributes
     * @param array<non-empty-string, JsonApiRelationship|null> $toOneRelationships
     * @param array<non-empty-string, list<JsonApiRelationship>> $toManyRelationships
     */
    public function __construct(
        protected readonly string $type,
        protected readonly array $attributes,
        protected readonly array $toOneRelationships,
        protected readonly array $toManyRelationships
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getToOneRelationships(): array
    {
        return $this->toOneRelationships;
    }

    public function getToManyRelationships(): array
    {
        return $this->toManyRelationships;
    }

    public function getPropertyNames(): array
    {
        return array_keys(array_merge($this->attributes, $this->toOneRelationships, $this->toManyRelationships));
    }
}
