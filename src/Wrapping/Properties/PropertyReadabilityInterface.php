<?php

declare(strict_types=1);

namespace EDT\Wrapping\Properties;

/**
 * Provides general readability information for a specific property.
 */
interface PropertyReadabilityInterface
{
    /**
     * If `true`, the property represented by this instance shall be included in the result when no
     * specific properties were requested. If `false`, the property shall only be included in the
     * result if it was specifically requested.
     *
     * @see https://jsonapi.org/format/#fetching-sparse-fieldsets JSON:API sparse fieldsets
     */
    public function isDefaultField(): bool;
}
