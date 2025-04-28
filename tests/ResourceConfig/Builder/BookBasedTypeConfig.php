<?php

declare(strict_types=1);

namespace Tests\ResourceConfig\Builder;

use EDT\JsonApi\PropertyConfig\Builder\ToOneRelationshipConfigBuilderInterface;
use EDT\JsonApi\ResourceConfig\Builder\TypeConfig;
use Tests\data\Model\Book;
use Tests\data\Model\Person;

/**
 * @property-read ToOneRelationshipConfigBuilderInterface<Book, Person> $author
 */
class BookBasedTypeConfig extends TypeConfig
{
}
