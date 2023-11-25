<?php

namespace App\Models;

use App\Traits\HasAttributes;

class Food extends Model
{
    use HasAttributes;

    protected string $table = 'users';

    protected array $fields = [
        'name' => ['string'],
        'power' => ['int', 0],
        'price' => ['int', 0],
        'updated_at' => ['datetime'],
        'created_at' => ['datetime']
    ];
}