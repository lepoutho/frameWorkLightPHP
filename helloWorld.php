<?php
echo "<strong>Hello, World!!</br></strong>";
// Test de connexion à la base de données
// (config MAMP : root/root, port MySQL 8889)
$mysqli = new mysqli('localhost', 'root', 'root', 'app_db', 8889);
if ($mysqli->connect_error) {
    die('Erreur de connexion : ' . $mysqli->connect_error);
}




echo "\nConnexion à la base de données réussie,  bien!";
$mysqli->close();