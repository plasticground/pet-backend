<?php

namespace App\Services;

use App\Interfaces\DatabaseInterface;

class DatabaseService implements DatabaseInterface
{
    public const TABLES = ['users', 'pets', 'users_pets', 'foods'];

    private \PDO $db;
    private string $dsn = 'mysql:dbname=petbackend;host=db';
    private string $user = 'root';
    private string $password = 'drowssap';

    public function __construct()
    {
        $this->db = new \PDO($this->dsn, $this->user, $this->password);
    }

    public function select($table, array $where = [], bool $first = false)
    {
        $table = match ($table) {
            'users', 'pets', 'users_pets', 'foods'  => $table,
            default => null
        };

        if (!$table) {
            return null;
        }

        $sql = "SELECT * FROM {$table}";
        $whereValues = null;

        if ($where) {
            if (array_key_exists('_in', $where)) {
                $sql .= " WHERE {$where['_in']['field']} IN (" . rtrim(str_repeat('?,' , count($where['_in']['values'])), ',') . ")";
                $whereValues = $where['_in']['values'];
            } else {
                foreach (array_keys($where) as $key) {
                    $sql .= " WHERE {$key}=?";

                    if (array_key_last($where) !== $key) {
                        $sql .= " AND";
                    }
                }

                $whereValues = array_values($where);
            }
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($whereValues);

        return $first ? $sth->fetch(\PDO::FETCH_ASSOC) : $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $table
     * @param array $data
     * @return int|bool
     */
    public function insert($table, array $data): int|bool
    {
        $data = array_filter($data, fn($field) => $field !== null);
        $fields = array_keys($data);
        $values = array_values($data);
        $count = count($fields);

        $sql = "INSERT INTO {$table} ("
            . implode(',', $fields)
            . ") VALUES ("
            . rtrim(str_repeat('?,', $count), ',')
            . ")";

        $this->db->prepare($sql)->execute($values);

        $idOrFalse = $this->db->lastInsertId();

        return is_string($idOrFalse) ? (int)$idOrFalse : $idOrFalse;
    }

    /**
     * @param $table
     * @param $id
     * @param array $data
     * @return bool
     */
    public function update($table, $id, array $data)
    {
        $data = array_filter($data, fn($field) => $field !== null);
        $fields = array_map(fn($field) => "$field=?", array_keys($data));
        $values = array_values($data);
        array_push($values, $id);

        $sql = "UPDATE {$table} SET " . implode(',', $fields) . " WHERE id=?";

        return $this->db->prepare($sql)->execute($values);
    }

    public function showTables()
    {
        return $this->db->query('SHOW TABLES')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function dropTables()
    {
        $this->db->exec('DROP TABLE users');
        $this->db->exec('DROP TABLE pets');
        $this->db->exec('DROP TABLE users_pets');
        $this->db->exec('DROP TABLE foods');

        return true;
    }

    public function createTables()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username varchar(255),
    balance INT,
    password varchar(255),
    token varchar(255),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE (username)
);

CREATE TABLE IF NOT EXISTS pets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name varchar(255),
    health INT UNSIGNED,
    happiness INT UNSIGNED,
    vivacity INT UNSIGNED,
    satiety INT UNSIGNED,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS users_pets (
    user_id BIGINT UNSIGNED NOT NULL,
    pet_id BIGINT UNSIGNED NOT NULL
);

CREATE TABLE IF NOT EXISTS foods (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name varchar(255),
    power INT UNSIGNED,
    price INT UNSIGNED,
    PRIMARY KEY (id)
);
SQL;

        return $this->db->exec($sql);
    }

    /**
     * @param string $name
     * @return bool
     */
    private function validateTableName(string $name): bool
    {
        return in_array($name, self::TABLES);
    }

    private function validateTableField(string $table, string $field)
    {

    }
}