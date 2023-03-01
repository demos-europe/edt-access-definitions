<?php

declare(strict_types=1);

namespace Tests\data\Types;

use EDT\Wrapping\Properties\AttributeReadability;

class TestAttributeReadability extends AttributeReadability
{
    public function isValidValue(mixed $attributeValue): bool
    {
        return true;
    }
}
