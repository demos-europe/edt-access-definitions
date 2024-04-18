<?php

declare(strict_types=1);

namespace Tests\ResourceConfig\Builder;

use EDT\JsonApi\InputHandling\RepositoryInterface;
use EDT\Querying\Pagination\PagePagination;
use Pagerfanta\Pagerfanta;
use Tests\data\Model\Book;
use Webmozart\Assert\Assert;

class BookRepository implements RepositoryInterface
{
    public function __construct(protected readonly Book $book)
    {
    }

    public function getEntityByIdentifier(string $id, array $conditions, array $identifierPropertyPath): object
    {
        Assert::same($id, $this->book->getId());
        Assert::isEmpty($conditions);
        Assert::eq($identifierPropertyPath, ['id']);

        return $this->book;
    }

    public function getEntitiesByIdentifiers(array $identifiers, array $conditions, array $sortMethods, array $identifierPropertyPath): array
    {
        Assert::count($identifiers, 1);
        Assert::same($identifiers[0], $this->book->getId());
        Assert::isEmpty($conditions);
        Assert::isEmpty($sortMethods);
        Assert::eq($identifierPropertyPath, ['id']);

        return [$this->book];
    }

    public function getEntities(array $conditions, array $sortMethods): array
    {
        Assert::isEmpty($conditions);
        Assert::isEmpty($sortMethods);

        return [$this->book];
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
        Assert::isEmpty($conditions);
    }
}
