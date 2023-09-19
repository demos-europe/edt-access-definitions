<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior\Identifier;

use Webmozart\Assert\Assert;

/**
 * @template TEntity of object
 *
 * @template-implements IdentifierReadabilityInterface<TEntity>
 */
class CallbackIdentifierReadability implements IdentifierReadabilityInterface
{
    /**
     * @param callable(TEntity): non-empty-string $readCallback
     */
    public function __construct(
        protected readonly mixed $readCallback
    ) {}

    public function getValue(object $entity): string
    {
        $idValue = ($this->readCallback)($entity);
        Assert::stringNotEmpty($idValue);

        return $idValue;
    }
}
