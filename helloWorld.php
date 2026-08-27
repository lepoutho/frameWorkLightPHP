<?php
echo "<strong>Hello, World!!</br></strong>";
// Test de connexion à la base de données
// (config MAMP : root/root, port MySQL 8889)
//$mysqli = new mysqli('localhost', 'root', 'root', 'app_db', 8889);

//echo $mysqli->host_info;

///Applications/MAMP/tmp/mysql/mysql.sock


try {
    $pdo = new PDO(
        'mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=app_db;charset=utf8mb4',
        'root',
        'root'
    );
    echo "Connexion réussie !";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
/*
if ($mysqli->connect_error) {
    die('Erreur de connexion : ' . $mysqli->connect_error);
}



echo "\nConnexion à la base de données réussie,  bien!";
$mysqli->close();
*/