<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template TType of \EDT\Wrapping\Contracts\Types\TypeInterface
 *           
 * @template-implements OptionalTypeRequirementInterface<TType>
 */
class TypeRequirement implements OptionalTypeRequirementInterface
{
    /**
     * @var TType
     */
    private TypeInterface $typeInstance;

    /**
     * @var non-empty-string
     */
    private string $identifier;

    /**
     * @param TType            $type
     * @param non-empty-string $identifier
     */
    public function __construct(TypeInterface $type, string $identifier)
    {
        $this->typeInstance = $type;
        $this->identifier = $identifier;
    }

    /**
     * @template TImpl
     *
     * @param class-string<TImpl> $fqn
     *
     * @return TypeRequirement<TImpl&TType>
     */
    public function instanceOf(string $fqn): TypeRequirement
    {
        if (!is_a($this->typeInstance, $fqn)) {
            throw TypeRetrievalAccessException::noNameWithImplementation($this->identifier, $fqn);
        }

        return new TypeRequirement($this->typeInstance, $this->identifier);
    }

    /**
     * @return $this
     */
    public function available(bool $available): self
    {
        if ($this->typeInstance->isAvailable() !== $available) {
            throw TypeRetrievalAccessException::typeExistsButNotAvailable($this->identifier);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function directlyAccessible(bool $accessible): self
    {
        if ($this->typeInstance->isDirectlyAccessible() !== $accessible) {
            throw TypeRetrievalAccessException::typeExistsButNotDirectlyAccessible($this->identifier);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function referencable(bool $referencable): self
    {
        if ($this->typeInstance->isReferencable() !== $referencable) {
            throw TypeRetrievalAccessException::typeExistsButNotReferencable($this->identifier);
        }

        return $this;
    }

    public function availableOrNull(bool $available)
    {
        if ($this->typeInstance->isAvailable() !== $available) {
            return new EmptyTypeRequirement($this->typeInstance, $this->identifier);
        }

        return $this;
    }

    public function directlyAccessibleOrNull(bool $accessible)
    {
        if ($this->typeInstance->isDirectlyAccessible() !== $accessible) {
            return new EmptyTypeRequirement($this->typeInstance, $this->identifier);
        }

        return $this;
    }

    public function referencableOrNull(bool $referencable)
    {
        if ($this->typeInstance->isReferencable() !== $referencable) {
            return new EmptyTypeRequirement($this->typeInstance, $this->identifier);
        }

        return $this;
    }

    /**
     * @return TType
     */
    public function getTypeInstance(): TypeInterface
    {
        return $this->typeInstance;
    }

    public function getTypeInstanceOrNull(): ?TypeInterface
    {
        return $this->typeInstance;
    }
}
