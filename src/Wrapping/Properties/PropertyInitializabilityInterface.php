<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

/**
 * Provides general initializability information for a specific property. These will be used when
 * an instance of the corresponding entity is to be created.
 */
interface PropertyInitializabilityInterface
{
    /**
     * If `true`, a value for the property represented by this instance does not need to be provided
     * when an instance of the corresponding entity is created. If `false`, a value needs to be
     * provided for the corresponding entity to be creatable.
     */
    public function isOptional(): bool;
}
