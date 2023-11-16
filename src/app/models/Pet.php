<?php

namespace App\Models;

use App\Traits\HasAttributes;

class Pet extends Model
{
    use HasAttributes;

    protected string $table = 'pets';

    protected array $fields = [
        'name' => ['string'],
        'health' => ['int', 100],
        'happiness' => ['int', 100],
        'vivacity' => ['int', 100],
        'satiety' => ['int', 100],
        'updated_at' => ['datetime'],
        'created_at' => ['datetime']
    ];
}