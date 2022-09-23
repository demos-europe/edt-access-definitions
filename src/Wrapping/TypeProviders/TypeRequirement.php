<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\TypeInterface;

/**
 * @template T of \EDT\Wrapping\Contracts\Types\TypeInterface
 */
class TypeRequirement
{
    /**
     * @var T&TypeInterface
     */
    private $typeInstance;

    /**
     * @var non-empty-string
     */
    private $identifier;

    /**
     * @param T                $type
     * @param non-empty-string $name
     */
    public function __construct(object $type, string $name)
    {
        $this->typeInstance = $type;
        $this->identifier = $name;
    }

    /**
     * @template I
     *
     * @param class-string<I> $fqn
     *
     * @return TypeRequirement<I&T>
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
     * @return T
     */
    public function getTypeInstance(): TypeInterface
    {
        return $this->typeInstance;
    }
}
