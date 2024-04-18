<?php

declare(strict_types=1);

namespace EDT\Wrapping\PropertyBehavior;

trait IdUnrelatedTrait
{
    public function isIdOptional(): bool
    {
        return false;
    }

    public function isIdRequired(): bool
    {
        return false;
    }
}
