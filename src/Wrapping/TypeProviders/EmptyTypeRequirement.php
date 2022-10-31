<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template TType of \EDT\Wrapping\Contracts\Types\TypeInterface
 *
 * @template-implements OptionalTypeRequirementInterface<TType>
 */
class EmptyTypeRequirement implements OptionalTypeRequirementInterface
{
    /**
     * @var non-empty-string
     */
    protected string $identifier;

    /**
     * @var TType
     */
    protected TypeInterface $initialType;

    /**
     * @param non-empty-string $identifier
     * @param TType            $initialType
     */
    public function __construct(TypeInterface $initialType, string $identifier)
    {
        $this->identifier = $identifier;
        $this->initialType = $initialType;
    }

    /**
     * @return $this
     */
    public function availableOrNull(bool $available): self
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function directlyAccessibleOrNull(bool $accessible): self
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function referencableOrNull(bool $referencable): self
    {
        return $this;
    }

    /**
     * @return null
     */
    public function getTypeInstanceOrNull(): ?TypeInterface
    {
        return null;
    }
}
