<?php

/**
 * Dispatcher REST : route chaque verbe HTTP vers un fichier d'action dédié.
 * Données en dur pour l'instant (le but est de valider le mécanisme de routage) :
 * pas de vraie persistance, chaque requête repart d'un jeu de données fixe.
 */

session_start(); // nécessaire pour retrouver le token CSRF stocké en session

require_once __DIR__ . '/../autoload.php';

header('Content-Type: application/json; charset=utf-8');

// Pour GET, les paramètres viennent de la query string ($_GET).
// Pour POST/PUT/PATCH/DELETE, le corps est du JSON à décoder.
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $input = $_GET;
} else {
    $rawBody = file_get_contents('php://input');
    $input = json_decode($rawBody, true);

    if ($rawBody !== '' && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'JSON invalide dans le corps de la requête']);
        exit;
    }

    $input = $input ?? [];

    // GET est en lecture seule (pas d'effet de bord) : pas besoin de CSRF.
    // Tout le reste modifie potentiellement des données → token obligatoire.
    if (!Csrf::isValid($input['csrf_token'] ?? null)) {
        http_response_code(403); // Forbidden
        echo json_encode(['success' => false, 'error' => 'Token CSRF invalide ou manquant']);
        exit;
    }
}

$routes = [
    'GET'    => __DIR__ . '/actions/getAction.php',
    'POST'   => __DIR__ . '/actions/createAction.php',
    'PUT'    => __DIR__ . '/actions/updateAction.php',
    'PATCH'  => __DIR__ . '/actions/updateAction.php',
    'DELETE' => __DIR__ . '/actions/deleteAction.php',
];

if (!isset($routes[$method])) {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => "Méthode {$method} non supportée"]);
    exit;
}

// Chaque fichier d'action doit se terminer par : return [...tableau de réponse...];
$result = require $routes[$method];

echo json_encode($result, JSON_UNESCAPED_UNICODE);
