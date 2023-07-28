<?php

declare(strict_types=1);

namespace EDT\Wrapping\Contracts\Types;

use Exception;

interface IdDeletableTypeInterface
{
    /**
     * @param non-empty-string $entityId
     *
     * @throws Exception If the deletion or corresponding side effects failed for some reason. As the caller is aware of the type name and given entity ID, there is no need to include them in the exception.
     */
    public function deleteEntityByIdentifier(string $entityId): void;
}
