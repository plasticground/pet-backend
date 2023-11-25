<?php

namespace App\Models;

use App\Exceptions\UserException;
use App\Traits\HasAttributes;

class User extends Model
{
    use HasAttributes;

    protected string $table = 'users';

    protected array $fields = [
        'username' => ['string'],
        'balance' => ['int', 0],
        'password' => ['string'],
        'token' => ['string'],
        'updated_at' => ['datetime'],
        'created_at' => ['datetime']
    ];

    public static function signUp(string $username, string $password): string
    {
        $user = self::findBy('username', $username);

        if ($user) {
            throw new UserException('User with the same username already exists', 409);
        }

        $attributes = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'token' => bin2hex(random_bytes(32))
        ];

        $user = new self($attributes);

        if ($user->create()) {
            return $user->token;
        }

        throw new UserException('User creation failed', 500);
    }

    public static function token(string $username, string $password)
    {
        $user = self::findBy('username', $username);

        if ($user === null) {
            throw new UserException('User with this username not found', 404);
        }

        if (password_verify($password, $user->password)) {
            return $user->token;
        }

        throw new UserException('Wrong password', 403);
    }

    public static function validateSignUp(string $username, string $password): array
    {
        return array_filter([
            'username' => self::validateUsername($username),
            'password' => self::validatePassword($password)
        ]);
    }

    public static function validateUsername(string $username)
    {
        $errors = [];
        $length = strlen($username);

        if ($length < 2) {
            $errors['length'] = 'Min length 2 symbols';
        } elseif ($length > 32) {
            $errors['length'] = 'Max length 32 symbols';
        }

        if (preg_match('/^\w+$/', $username) === false) {
            $errors['format'] = 'Available only letters, digits and _ symbol';
        }

        return $errors;
    }

    public static function validatePassword(string $password)
    {
        $errors = [];
        $length = strlen($password);

        if ($length < 6) {
            $errors['length'] = 'Min length 6 symbols';
        } elseif ($length > 32) {
            $errors['length'] = 'Max length 32 symbols';
        }

        preg_match_all('/(?<specials>[!@#$%^&*?_-])|(?<lowercase>[a-z])|(?<uppercase>[A-Z])|(?<digits>[0-9])/', $password, $matches);

        $parts = array_filter(
            $matches,
            fn($value, $key) => in_array($key, ['specials', 'lowercase', 'uppercase', 'digits']) && !empty(array_filter($value)),
            ARRAY_FILTER_USE_BOTH
        );

        if (count($parts) < 4) {
            $errors['format'] = 'Password must contain: uppercase letter, lowercase letter, number and special symbol (available symbols: !, @, #, $, %, ^, &, *, ?, _, -)';
        }

        return $errors;
    }
}