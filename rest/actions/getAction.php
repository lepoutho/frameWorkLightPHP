<?php

/**
 * Action GET : lecture de données.
 * $input contient les paramètres de la query string (ex: ?id=1).
 */

$id = $input['id'] ?? null;

$items = [
    1 => ['id' => 1, 'label' => 'Premier élément'],
    2 => ['id' => 2, 'label' => 'Deuxième élément'],
    3 => ['id' => 3, 'label' => 'Troisième élément'],
];

if ($id !== null) {
    if (!isset($items[(int) $id])) {
        http_response_code(404);
        return ['success' => false, 'error' => "Élément id={$id} introuvable"];
    }

    return ['success' => true, 'item' => $items[(int) $id]];
}

return ['success' => true, 'items' => array_values($items)];
