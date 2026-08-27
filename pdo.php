<?php

class IniConfig
{
    private $data;

    public function __construct(string $filePath, bool $processSections = true)
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException("Fichier INI introuvable ou illisible : {$filePath}");
        }

        $result = @parse_ini_file($filePath, $processSections, INI_SCANNER_TYPED);

        if ($result === false) {
            $error = error_get_last();
            throw new RuntimeException(
                "Erreur lors du parsing du fichier INI : " . ($error['message'] ?? 'inconnue')
            );
        }

        $this->data = $result;
    }
    

    public function all(): array
    {
        return $this->data;
    }

    public function get(string $section, ?string $key = null)
    {
        if (!isset($this->data[$section])) {
            return null;
        }

        if ($key === null) {
            return $this->data[$section];
        }

        return $this->data[$section][$key] ?? null;
    }
}



class Database extends PDO
{
    public function __construct(
        string $dbname,
        string $user,
        string $password,
        string $socket = NULL,
        string $charset = 'utf8mb4'
    ) {
        
        //for information SQL QUERY => SHOW VARIABLES LIKE 'socket' to see $socket;
       
         $dsn = $socket != NULL ? "mysql:unix_socket={$socket};dbname={$dbname};charset={$charset}":"mysql:dbname={$dbname};charset={$charset}";
        
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

/*
$db = new Database('app_db','root','root','/Applications/MAMP/tmp/mysql/mysql.sock');
$toto=1;
$user = $db->fetchOne('SELECT * FROM titi WHERE id = :titi', ['titi' => $toto]);
var_dump($user);
*/


$config = new IniConfig('/Users/thomasetanne-marie/Sites/localhost/public/config/settings.ini');
$dbConf = $config->get('database');

$db = new Database(
    $dbConf['db_name'],
    $dbConf['user_name'],
    $dbConf['password'],
    $dbConf['sql_sock']
);
$toto=1;
$user = $db->fetchOne('SELECT * FROM titi WHERE id = :titi', ['titi' => $toto]);
var_dump($user);