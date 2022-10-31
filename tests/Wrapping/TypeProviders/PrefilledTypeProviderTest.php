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
    protected PrefilledTypeProvider $typeProvider;

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
        $this->expectExceptionMessage("Type instance with identifier 'foobar' matching the defined criteria was not found due to the following reasons: identifier 'foobar' not known");
        $this->typeProvider->requestType('foobar')->getInstanceOrThrow();
    }
}
