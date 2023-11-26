<?php


namespace App\Helpers;

class DataHelper
{
    const QUERY = 'query', BODY = 'body', USER = 'user';

    public function __construct(protected array $data = [])
    {
        return $this;
    }

    public function __set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    public function __get(string $name)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    public function get($from, $key, $default = null)
    {
        return array_key_exists($key, $this->data[$from]) ? $this->data[$from][$key] : $default;
    }

    public function set($to, $key, $value)
    {
        $this->data[$to][$key] = $value;
    }

    public function query(?string $key = null, $default = null)
    {
        if ($key) {
            return $this->get(self::QUERY, $key, $default);
        }

        return $this->{self::QUERY};
    }

    public function body(?string $key = null, $default = null)
    {
        if ($key) {
            return $this->get(self::BODY, $key, $default);
        }

        return $this->{self::BODY};
    }

    public static function only(array $keys, array $data): array
    {
        $newData = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $keys)) {
                $newData[$key] = $value;
            }
        }

        return $newData;
    }

    public function user()
    {
        return $this->{self::USER}[0] ?? null;
    }
}