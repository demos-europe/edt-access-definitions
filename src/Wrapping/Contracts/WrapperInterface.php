<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts;

interface WrapperInterface
{
    /**
     * @param non-empty-string $propertyName
     *
     * @return mixed|null
     *
     * @throws AccessException
     */
    public function getPropertyValue(string $propertyName);
}
