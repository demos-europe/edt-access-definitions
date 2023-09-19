<?php

declare(strict_types=1);

namespace EDT\Wrapping;

interface CreationDataInterface extends EntityDataInterface
{
    /**
     * @return non-empty-string|null
     */
    public function getEntityIdentifier(): ?string;
}
