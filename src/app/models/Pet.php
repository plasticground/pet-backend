<?php

namespace App\Models;

use App\Traits\HasAttributes;

class Pet
{
    use HasAttributes;

    private int $created_at;
    
    public function __construct(
        private string $name,
        private int $health = 100,
        private int $happiness = 100,
        private int $vivacity = 100,
        private int $satiety = 100,
        int|\DateTimeInterface|null $created_at = null
    ) {
        if ($created_at === null) {
            $created_at = (new \DateTimeImmutable())->getTimestamp();
        }

        if ($created_at instanceof \DateTimeInterface) {
            $created_at = $created_at->getTimestamp();
        }

        /** @var int $created_at */
        $this->created_at = $created_at;

        return $this;
    }
}