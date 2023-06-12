<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

trait EntityVerificationTrait
{
    /**
     * @template TExpected of object
     *
     * @param class-string<TExpected> $relationshipClass
     *
     * @return TExpected|null
     *
     * @throws InvalidArgumentException
     */
    protected function assertValidToOneValue(mixed $relationshipEntity, string $relationshipClass): ?object
    {
        if (null === $relationshipEntity) {
            return null;
        }

        Assert::isInstanceOf($relationshipEntity, $relationshipClass);

        // Checks above are not yet understood by phpstan, adding manual checks that can be removed
        // at some point. (phpstan should change its concern, making it visible)
        return !$relationshipEntity instanceof $relationshipClass
            ? throw new InvalidArgumentException("non-$relationshipClass value")
            : $relationshipEntity;
    }

    /**
     * @template TExpected of object
     *
     * @param class-string<TExpected> $entityClass
     *
     * @return list<TExpected>
     *
     * @throws InvalidArgumentException
     */
    protected function assertValidToManyValue(mixed $entities, string $entityClass): array
    {
        Assert::isArray($entities);
        Assert::allIsInstanceOf($entities, $entityClass);

        // Checks above are not yet understood by phpstan, adding manual checks that can be removed
        // at some point. (phpstan should change its concern, making it visible)
        $uncheckedEntities = $entities;
        $entities = array_filter(
            $uncheckedEntities,
            static fn (mixed $item): bool => $item instanceof $entityClass
        );
        if (count($entities) !== count($uncheckedEntities)) {
            throw new InvalidArgumentException("non-$entityClass items");
        }

        return array_values($entities);
    }
}
