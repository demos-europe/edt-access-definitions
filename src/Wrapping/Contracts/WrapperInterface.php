<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

interface WrapperInterface
{
    /**
     * @return mixed
     */
    public function getPropertyValue(string $propertyName);
}
