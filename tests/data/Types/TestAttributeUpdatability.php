<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\Wrapping\Properties\AttributeUpdatability;

class TestAttributeUpdatability extends AttributeUpdatability
{
    public function isValidValue(mixed $attributeValue): bool
    {
        return true;
    }
}
