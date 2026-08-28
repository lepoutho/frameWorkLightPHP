<?php

/**
 * Endpoint REST minimal : reçoit un POST, renvoie des données JSON.
 * Pour l'instant, données "en dur" (le but est de valider le mécanisme AJAX).
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Méthode non autorisée, utilisez POST']);
    exit;
}

// Exemple de lecture d'un paramètre envoyé par le client (facultatif ici)
$nom = $_POST['nom'] ?? null;

$data = [
    'success' => true,
    'message' => $nom !== null ? "Bonjour {$nom} !" : 'Bonjour !',
    'items'   => [
        ['id' => 1, 'label' => 'Premier élément'],
        ['id' => 2, 'label' => 'Deuxième élément'],
        ['id' => 3, 'label' => 'Troisième élément'],
    ],
    'timestamp' => date('c'),
];

echo json_encode($data, JSON_UNESCAPED_UNICODE);
