<?php

declare(strict_types=1);

namespace Tests\data\AdModel;

class Birth
{
    /**
     * @var string
     */
    private $country;
    /**
     * @var string|null
     */
    private $region;
    /**
     * @var string
     */
    private $locality;
    /**
     * @var int
     */
    private $day;
    /**
     * @var int
     */
    private $month;
    /**
     * @var int
     */
    private $year;

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
