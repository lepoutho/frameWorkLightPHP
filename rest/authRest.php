<?php

/**
 * Endpoint d'authentification : inscription / connexion / déconnexion / statut.
 *
 * Routes (via ?action=...) :
 *   POST ?action=register  { email, password }
 *   POST ?action=login     { email, password }
 *   POST ?action=logout    (pas de corps)
 *   GET  ?action=me        (pas de paramètre)
 */

session_start(); // doit être appelé avant tout output, et avant d'utiliser $_SESSION

require_once __DIR__ . '/../autoload.php';

header('Content-Type: application/json; charset=utf-8');

$config = new IniConfig(__DIR__ . '/../config/settings.ini');
$dbConf = $config->get('database');

$db = new Database(
    $dbConf['db_name'],
    $dbConf['user_name'],
    $dbConf['password'],
    $dbConf['sql_sock']
);

$auth = new AuthService($db);

$action = $_GET['action'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

// Corps JSON pour les actions qui en ont besoin (register / login)
function lireCorpsJson(): array
{
    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);
    return is_array($data) ? $data : [];
}

switch ($action) {
    case 'register':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée, utilisez POST']);
            exit;
        }
        $input = lireCorpsJson();
        $result = $auth->register($input['email'] ?? '', $input['password'] ?? '');
        http_response_code($result['success'] ? 201 : 422);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    case 'login':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée, utilisez POST']);
            exit;
        }
        $input = lireCorpsJson();
        $result = $auth->login($input['email'] ?? '', $input['password'] ?? '');
        http_response_code($result['success'] ? 200 : 401);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    case 'logout':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée, utilisez POST']);
            exit;
        }
        echo json_encode($auth->logout(), JSON_UNESCAPED_UNICODE);
        break;

    case 'me':
        $user = $auth->currentUser();
        echo json_encode(['success' => true, 'connected' => $user !== null, 'user' => $user], JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => "Action inconnue : {$action}"]);
}
