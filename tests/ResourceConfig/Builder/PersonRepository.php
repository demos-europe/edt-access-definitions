<?php

declare(strict_types=1);

namespace Tests\ResourceConfig\Builder;

use EDT\JsonApi\InputHandling\RepositoryInterface;
use EDT\Querying\Pagination\PagePagination;
use Pagerfanta\Pagerfanta;
use Tests\data\Model\Person;
use Webmozart\Assert\Assert;

class PersonRepository implements RepositoryInterface
{
    public function __construct(
        protected readonly Person $person
    )
    {
    }

    public function getEntityByIdentifier(string $id, array $conditions, array $identifierPropertyPath): object
    {
        Assert::same($id, $this->person->getId());

        return $this->person;
    }

    public function getEntitiesByIdentifiers(array $identifiers, array $conditions, array $sortMethods, array $identifierPropertyPath): array
    {
        Assert::count($identifiers, 1);
        Assert::same($identifiers[0], $this->person->getId());
        Assert::isEmpty($conditions);
        Assert::isEmpty($sortMethods);
        Assert::eq($identifierPropertyPath, ['id']);

        return [$this->person];
    }

    public function getEntities(array $conditions, array $sortMethods): array
    {
        Assert::isEmpty($conditions);
        Assert::isEmpty($sortMethods);

        return [$this->person];
    }

    public function getEntitiesForPage(array $conditions, array $sortMethods, PagePagination $pagination): Pagerfanta
    {
        throw new \InvalidArgumentException();
    }

    public function deleteEntityByIdentifier(string $entityIdentifier, array $conditions, array $identifierPropertyPath): void
    {
        throw new \InvalidArgumentException();
    }

    public function reindexEntities(array $entities, array $conditions, array $sortMethods): array
    {
        throw new \InvalidArgumentException();
    }

    public function isMatchingEntity(object $entity, array $conditions): bool
    {
        throw new \InvalidArgumentException();
    }

    public function assertMatchingEntity(object $entity, array $conditions): void
    {
        throw new \InvalidArgumentException();
    }
}
