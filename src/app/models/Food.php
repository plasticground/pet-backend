<?php

namespace App\Models;

use App\Traits\HasAttributes;

/**
 * Class Food
 * @package App\Models
 */
class Food
{
    use HasAttributes;

    /**
     * Food constructor.
     * @param string $name
     * @param int $power
     * @param float $price
     */
    public function __construct(
        private string $name,
        private int $power,
        private float $price
    ) {
        return $this;
    }
}