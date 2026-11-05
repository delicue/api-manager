<?php

namespace App;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static Database $instance;
    private PDO $connection;

    private function __construct(string $dsn)
    {
        try {
            $this->connection = new PDO($dsn, options: [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $exception) {
            die("Error: {$exception->getMessage()}");
        }
    }

    public static function getInstance()
    {
        return static::$instance;
    }

    public static function config(string $dsn)
    {
        static::$instance = new self($dsn);
    }

    public function query(string $query, $params = [])
    {
        $statement = $this->connection->prepare($query);
        $statement->execute($params);
        return $statement;
    }

    public function fetch(string $query, $params = [])
    {
        return self::query($query, $params)->fetch();
    }

    public function fetchAll(string $query, $params = [])
    {
        return self::query($query, $params)->fetchAll();
    }

    /**
     * Creates a new table in the database, automatically including an `id` column in the table.
     * @param string $table
     * @param array $columns
     * @return bool|PDOStatement
     */
    public function createTable(string $table, $columns): bool|PDOStatement {
        return self::query("CREATE TABLE IF NOT EXISTS $table (" . implode(', ', $columns) . ")", []);
    }

    /**
     * Count the amount of records in a table.
     * @param string $table
     * @return int
     */
    public function count(string $table): int {
        // Only allow alphanumeric and underscore characters in table names
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new \InvalidArgumentException('Invalid table name');
        }
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM {$table}");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function isValidApiKey(string $apiKey)
    {
        $stmt = $this->connection->prepare('SELECT COUNT(*) FROM api_keys WHERE api_key = :api_key');
        $stmt->bindParam(':api_key', $apiKey);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Finds a single record from the database.
     *
     * @param string $query
     * @param array $params
     * @return array|null
     */
    public function fetchOne(string $query, $params = []): ?array {
        $stmt = self::query($query, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    public function createApiKey(string $apiKey): bool|PDOStatement {
        $statement = self::query("INSERT INTO api_keys (api_key) VALUES (:api_key)", [
            ':api_key' => $apiKey
        ]);

        return $statement;
    }
}