<?php

declare(strict_types=1);

namespace Tests\data\AdModel;

class Birth
{
    private string $country;

    private ?string $region;

    private string $locality;

    private int $day;

    private int $month;

    private int $year;

    public function __construct(string $country, ?string $region, string $locality, int $y, int $m, int $d)
    {
        $this->country = $country;
        $this->region = $region;
        $this->locality = $locality;
        $this->day = $d;
        $this->month = $m;
        $this->year = $y;
    }
}
