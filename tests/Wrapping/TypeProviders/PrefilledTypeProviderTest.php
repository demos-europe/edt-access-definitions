<?php

declare(strict_types=1);

namespace Tests\Wrapping\TypeProviders;

use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\TypeProviders\PrefilledTypeProvider;
use PHPUnit\Framework\TestCase;
use Tests\data\Types\AuthorType;
use Tests\data\Types\BookType;

class PrefilledTypeProviderTest extends TestCase
{
    /**
     * @var PrefilledTypeProvider
     */
    protected $typeProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $conditionFactory = new PhpConditionFactory();
        $authorType = new AuthorType($conditionFactory);
        $bookType = new BookType($conditionFactory);
        $this->typeProvider = new PrefilledTypeProvider([
            $authorType,
            $bookType,
        ]);
    }

    public function testUnknownTypeIdentifier(): void
    {
        $this->expectException(AccessException::class);
        $this->expectExceptionMessage('Type identifier \'foobar\' not known. Known type identifiers are: Tests\data\Types\AuthorType, Tests\data\Types\BookType.');
        $this->typeProvider->requestType('foobar');
    }
}
