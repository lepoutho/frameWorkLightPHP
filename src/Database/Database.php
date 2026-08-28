<?php

class Database extends PDO
{
    public function __construct(
        string $dbname,
        string $user,
        string $password,
        string $socket = null,
        string $charset = 'utf8mb4'
    ) {
        // for information SQL QUERY => SHOW VARIABLES LIKE 'socket' to see $socket;

        $dsn = $socket !== null
            ? "mysql:unix_socket={$socket};dbname={$dbname};charset={$charset}"
            : "mysql:dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            parent::__construct($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            throw new RuntimeException('Connexion à la base de données impossible : ' . $e->getMessage());
        }
    }

    public function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->run($sql, $params)->fetch();
        return $result ?: null;
    }
}
