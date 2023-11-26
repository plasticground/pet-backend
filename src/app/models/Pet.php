<?php

namespace App\Models;

use App\Exceptions\PetException;
use App\Services\DatabaseService;
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

    public static function findByUser(int $userId, ?string $petName = null)
    {
        $usersPets = (new DatabaseService())->select('users_pets', ['user_id' => $userId]);

        if ($usersPets) {
            $ids = array_map(fn($pair) => $pair['pet_id'], $usersPets);

            $pets = self::findAllWhereIn('id', $ids);
            $pets = array_map(fn(self $pet) => $pet->asArray(), $pets);

            if ($petName) {
                $petName = strtolower($petName);
                $pets = array_filter($pets, fn($pet) => strtolower($pet['name']) === $petName);

                return $pets[array_key_first($pets)] ?? null;
            }

            return $pets;
        }

        return [];
    }

    public static function createPair(int $petId, int $userId)
    {
        if (!self::find($petId)) {
            throw new PetException('Unable to create pair, Pet with that ID is not exists', 404);
        }

        if (!User::find($userId)) {
            throw new PetException('Unable to create pair, User with that ID is not exists', 404);
        }

        $success = is_int((new DatabaseService())->insert('users_pets', ['user_id' => $userId, 'pet_id' => $petId]));

        return $success ?: throw new PetException('Pet-User pair creation failed', 500);
    }

    public static function validateCreation($name): array
    {
        return array_filter([
            'name' => self::validateName($name)
        ]);
    }

    public static function validateName($name)
    {
        $errors = [];

        if (empty($name)) {
            $errors['required'] = 'Field is required';

            return $errors;
        }

        $length = strlen($name);

        if ($length < 2) {
            $errors['length'] = 'Min length 2 symbols';
        } elseif ($length > 32) {
            $errors['length'] = 'Max length 32 symbols';
        }

        if (!preg_match('/^\w+$/', $name)) {
            $errors['format'] = 'Available only letters, digits and _ symbol';
        }

        return $errors;
    }

    public function asArray(): array
    {
        return [
            'name' => $this->name,
            'health' => $this->health,
            'happiness' => $this->happiness,
            'vivacity' => $this->vivacity,
            'satiety' => $this->satiety
        ];
    }
}