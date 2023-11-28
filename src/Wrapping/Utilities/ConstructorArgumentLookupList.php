<?php

declare(strict_types=1);

namespace EDT\Wrapping\Utilities;

use InvalidArgumentException;
use Webmozart\Assert\Assert;
use function array_key_exists;

class ConstructorArgumentLookupList
{
    /**
     * @var array<non-empty-string, array{mixed, list<non-empty-string>}>
     */
    protected array $arguments = [];

    /**
     * @param non-empty-string $argumentName
     * @param list<non-empty-string> $propertyDeviations
     *
     * @throws InvalidArgumentException a value already exists for the given argument name
     */
    public function add(string $argumentName, mixed $value, array $propertyDeviations): void
    {
        Assert::keyNotExists($this->arguments, $argumentName);
        $this->arguments[$argumentName][] = [$value, $propertyDeviations];
    }

    /**
     * Adds the constructor arguments from the given list if they are not yet present in this list.
     *
     * @param ConstructorArgumentLookupList $fallbacks
     */
    public function addFallbacks(ConstructorArgumentLookupList $fallbacks): void
    {
        foreach ($fallbacks->arguments as $name => $argument) {
            if (!array_key_exists($name, $this->arguments)) {
                $this->add($name, $argument[0], $argument[1]);
            }
        }
    }

    public function hasArgument(string $argumentName): bool
    {
        return array_key_exists($argumentName, $this->arguments);
    }

    /**
     * @param non-empty-string $argumentName
     *
     * @return array{mixed, list<non-empty-string>}
     */
    public function getArgument(string $argumentName): array
    {
        return $this->arguments[$argumentName];
    }
}
