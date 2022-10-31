<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\ConditionFactory\PathsBasedConditionFactoryInterface;
use EDT\Querying\Contracts\PathsBasedInterface;
use EDT\Wrapping\Contracts\Types\FilterableTypeInterface;
use EDT\Wrapping\Contracts\Types\IdentifiableTypeInterface;
use EDT\Wrapping\Contracts\Types\ReadableTypeInterface;
use EDT\Wrapping\Contracts\Types\SortableTypeInterface;
use EDT\Wrapping\Contracts\Types\UpdatableTypeInterface;
use Tests\data\Model\Person;

/**
 * @template-implements ReadableTypeInterface<Person>
 * @template-implements IdentifiableTypeInterface<Person>
 * @template-implements FilterableTypeInterface<Person>
 * @template-implements SortableTypeInterface<Person>
 */
class AuthorType implements ReadableTypeInterface, FilterableTypeInterface, SortableTypeInterface, IdentifiableTypeInterface, UpdatableTypeInterface
{
    private PathsBasedConditionFactoryInterface $conditionFactory;

    public function __construct(PathsBasedConditionFactoryInterface $conditionFactory)
    {
        $this->conditionFactory = $conditionFactory;
    }

    public function getReadableProperties(): array
    {
        $properties = $this->getFilterableProperties();
        $properties['birth'] = BirthType::class;

        return $properties;
    }

    public function getFilterableProperties(): array
    {
        return [
            'name' => null,
            'pseudonym' => null,
            'books' => BookType::class,
            'birthCountry' => null,
        ];
    }

    public function getSortableProperties(): array
    {
        return [
            'name' => null,
            'pseudonym' => null,
            'birthCountry' => null,
        ];
    }

    public function getAccessCondition(): PathsBasedInterface
    {
        return $this->conditionFactory->allConditionsApply(
            $this->conditionFactory->propertyHasNotSize(0, 'books'),
            $this->conditionFactory->propertyHasNotSize(0, 'writtenBooks')
        );
    }

    public function getEntityClass(): string
    {
        return Person::class;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getIdentifierPropertyPath(): array
    {
        return ['name'];
    }

    public function getAliases(): array
    {
        return [
            'birthCountry' => ['birth', 'country'],
            'writtenBooks' => ['books'],
        ];
    }

    public function isReferencable(): bool
    {
        return true;
    }

    public function isDirectlyAccessible(): bool
    {
        return true;
    }

    public function getDefaultSortMethods(): array
    {
        return [];
    }

    public function getUpdatableProperties(object $updateTarget): array
    {
        return [
            'name' => null,
            'birthCountry' => null,
            'books' => BookType::class,
        ];
    }

    public function getInternalProperties(): array
    {
        return [
            'books' => BookType::class,
            'writtenBooks' => BookType::class,
            'name' => null,
            'birthCountry' => null,
            'pseudonym' => null,
        ];
    }
}
