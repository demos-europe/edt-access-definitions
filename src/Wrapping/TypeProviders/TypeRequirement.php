<?php

declare(strict_types=1);

namespace EDT\Wrapping\TypeProviders;

use EDT\Wrapping\Contracts\TypeRetrievalAccessException;
use EDT\Wrapping\Contracts\Types\ExposableRelationshipTypeInterface;
use EDT\Wrapping\Contracts\Types\TypeInterface;
use function in_array;

/**
 * @template TType of TypeInterface
 */
class TypeRequirement
{
    /**
     * @param TType|null             $typedInstance
     * @param TypeInterface|null     $plainInstance
     * @param non-empty-string       $identifier
     * @param list<non-empty-string> $problems
     */
    public function __construct(
        private ?TypeInterface $typedInstance,
        private readonly ?TypeInterface $plainInstance,
        private readonly string $identifier,
        private array $problems
    ) {}

    /**
     * @template TTestType
     *
     * @param class-string<TTestType> $testTypeFqn
     *
     * @return TypeRequirement<TTestType&TType>
     */
    public function instanceOf(string $testTypeFqn): TypeRequirement
    {
        $problems = $this->problems;
        $instance = $this->typedInstance;
        if (null !== $instance && !$instance instanceof $testTypeFqn) {
            if (null !== $this->plainInstance && !$this->plainInstance instanceof $testTypeFqn) {
                $problems = $this->addProblem("does not implement '$testTypeFqn'");
            }
            $instance = null;
        }

        return new self($instance, $this->plainInstance, $this->identifier, $problems);
    }

    /**
     * @return TypeRequirement<ExposableRelationshipTypeInterface&TType>
     */
    public function exposedAsRelationship(): self
    {
        $self = $this->instanceOf(ExposableRelationshipTypeInterface::class);

        if (null !== $self->typedInstance && !$self->typedInstance->isExposedAsRelationship()) {
            if ($self->plainInstance instanceof ExposableRelationshipTypeInterface
                && !$self->plainInstance->isExposedAsRelationship()
            ) {
                $self->problems = $self->addProblem('not set as exposable');
            }
            $self->typedInstance = null;
        }

        return $self;
    }

    /**
     * @return TType
     *
     * @throws TypeRetrievalAccessException
     */
    public function getInstanceOrThrow(): TypeInterface
    {
        if (null === $this->typedInstance) {
            throw TypeRetrievalAccessException::notPresent($this->identifier, $this->problems);
        }

        return $this->typedInstance;
    }

    /**
     * @return TType|null
     */
    public function getInstanceOrNull(): ?TypeInterface
    {
        return $this->typedInstance;
    }

    public function isPresent(): bool
    {
        return null !== $this->typedInstance;
    }

    /**
     * @param non-empty-string $value
     *
     * @return list<non-empty-string>
     */
    private function addProblem(string $value): array
    {
        $problems = $this->problems;
        if (!in_array($value, $problems, true)) {
            $problems[] = $value;
        }

        return $problems;
    }
}
