<?php

require_once __DIR__ . '/autoload.php';

$config = new IniConfig(__DIR__ . '/config/settings.ini');
$dbConf = $config->get('database');

$db = new Database(
    $dbConf['db_name'],
    $dbConf['user_name'],
    $dbConf['password'],
    $dbConf['sql_sock']
);

$toto = 1;
$user = $db->fetchOne('SELECT * FROM titi WHERE id = :titi', ['titi' => $toto]);
var_dump($user);
