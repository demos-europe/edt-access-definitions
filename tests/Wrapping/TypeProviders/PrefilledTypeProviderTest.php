<?php

declare(strict_types=1);

namespace Tests\Wrapping\TypeProviders;

use EDT\Querying\ConditionFactories\PhpConditionFactory;
use EDT\Wrapping\Contracts\AccessException;
use EDT\Wrapping\TypeProviders\LazyTypeProvider;
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
        $lazyTypeProvider = new LazyTypeProvider();
        $authorType = new AuthorType($conditionFactory, $lazyTypeProvider);
        $bookType = new BookType($conditionFactory, $lazyTypeProvider);
        $this->typeProvider = new PrefilledTypeProvider([
            $authorType,
            $bookType,
        ]);
        $lazyTypeProvider->setAllTypes($this->typeProvider);
    }

    public function testUnknownTypeIdentifier(): void
    {
        $this->expectException(AccessException::class);
        $this->expectExceptionMessage("Type instance with identifier 'foobar' matching the defined criteria was not found due to the following reasons: identifier 'foobar' not known");
        $this->typeProvider->requestType('foobar')->getInstanceOrThrow();
    }
}
