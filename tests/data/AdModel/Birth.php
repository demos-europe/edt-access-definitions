<?php

declare(strict_types=1);

namespace Tests\data\AdModel;

class Birth
{
    public function __construct(
        private string $country,
        private ?string $region,
        private string $locality,
        private int $year,
        private int $month,
        private int $day
    ) {}
}
