<?php

declare(strict_types=1);

namespace Tests\ResourceConfig\Builder;

use EDT\JsonApi\PropertyConfig\Builder\ToManyRelationshipConfigBuilderInterface;
use EDT\JsonApi\ResourceConfig\Builder\TypeConfig;
use EDT\Querying\Contracts\PathsBasedInterface;
use Tests\data\Model\Book;
use Tests\data\Model\Person;

/**
 * @property-read ToManyRelationshipConfigBuilderInterface<PathsBasedInterface, PathsBasedInterface, Person, Book> $books
 */
class PersonBasedConfigBuilder extends TypeConfig
{
}
