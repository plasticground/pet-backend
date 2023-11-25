<?php


namespace App\Models;


use App\Services\DatabaseService;
#[\AllowDynamicProperties]
class Model
{
    public const INT = 'int';
    public const FLOAT = 'float';
    public const STRING = 'string';
    public const BOOL = 'bool';
    public const DATETIME = 'datetime';
    public const AVAILABLE_FIELD_TYPES = [
        self::INT,
        self::FLOAT,
        self::STRING,
        self::BOOL,
        self::DATETIME
    ];

    /** @var string Database table name */
    protected string $table;

    /**
     * @var array Database table fields
     * ['field' => ['int'|'float'|'string'|'bool|datetime', ?defaultValue], ...]
     */
    protected array $fields;

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        $attributes = [];

        foreach ($this->getFields() as $field => $properties) {
            $property = $properties[1] ?? null;

            $attributes[$field] = $this->{$field} ?: $property;
        }

        return $attributes;
    }

    /**
     * @return array|bool
     */
    public function create(): bool|array
    {
        $errors = $this->validateFields();

        if (empty($errors)) {
            return (new DatabaseService())->insert($this->getTable(), $this->getAttributes());
        }

        return $errors;
    }

    /**
     * @return array|bool
     */
    public function update(): bool|array
    {
        if (property_exists($this, 'id')) {
            $errors = $this->validateFields();

            if (empty($errors)) {
                return (new DatabaseService())->update($this->getTable(), $this->id, $this->getAttributes());
            }

            return $errors;
        }

        return false;
    }

    /**
     * @return array
     */
    public function validateFields(): array
    {
        $errors = [];

        foreach ($this->getFields() as $name => $properties) {
            $properties = array_values($properties);
            $type = $properties[0] ?? null;
            $defaultValue = $properties[1] ?? null;

            switch (count($properties)) {
                case 0:
                    $errors[$name]['properties'] = 'Empty field properties';
                    break;
                case 1:
                case 2:
                    if (!in_array($type, self::AVAILABLE_FIELD_TYPES)) {
                        $errors[$name]['type'] = "Invalid field type: {$type}";
                    } elseif (!$this->isValidDefaultValue($type, $defaultValue)) {
                        $errors[$name]['defaultValue'] = "Invalid field default value: {$defaultValue}";
                    }
                    break;
                default:
                    $errors[$name] = 'Field property has incorrect format (must be [\'type\', ?defaultValue]): ' . $properties[0];
                    break;
            }
        }

        return $errors;
    }

    /**
     * @param $type
     * @param $value
     * @return bool
     */
    private function isValidDefaultValue($type, $value): bool
    {
        if ($value === null) {
            return true;
        }

        return match ($type) {
            self::INT => is_int($value),
            self::FLOAT => is_float($value),
            self::STRING => is_string($value),
            self::BOOL => is_bool($value),
            self::DATETIME => is_null($value),
            default => false
        };
    }

    /**
     * @param int $id
     * @return Model|null
     */
    public static function find(int $id): ?Model
    {
        $model = new static();

        $attributes = (new DatabaseService())->select($model->getTable(), ['id' => $id], true);

        if ($attributes) {
            return $model->fill($attributes);
        }

        return null;
    }

    /**
     * @param string $key
     * @param $value
     * @return Model|null
     */
    public static function findBy(string $key, $value): ?Model
    {
        $model = new static();

        $attributes = (new DatabaseService())->select($model->getTable(), [$key => $value], true);

        if ($attributes) {
            return $model->fill($attributes);
        }

        return null;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes = []): static
    {
        $attributes = array_merge($this->getAttributes(), $attributes);

        foreach ($attributes as $attribute => $value) {
            $this->{$attribute} = $value;
        }

        return $this;
    }

    /**
     * Model constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        return $this->fill($attributes);
    }
}