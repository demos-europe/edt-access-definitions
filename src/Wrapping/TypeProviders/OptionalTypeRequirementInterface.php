<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template TType of \EDT\Wrapping\Contracts\Types\TypeInterface
 */
interface OptionalTypeRequirementInterface
{
    /**
     * @return OptionalTypeRequirementInterface<TType>
     */
    public function availableOrNull(bool $available);

    /**
     * @return OptionalTypeRequirementInterface<TType>
     */
    public function directlyAccessibleOrNull(bool $accessible);

    /**
     * @return OptionalTypeRequirementInterface<TType>
     */
    public function referencableOrNull(bool $referencable);

    /**
     * @return TType|null
     */
    public function getTypeInstanceOrNull(): ?TypeInterface;
}
