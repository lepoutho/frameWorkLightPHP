<?php

/**
 * Action POST : création d'une nouvelle ressource.
 * $input contient le JSON décodé du corps de la requête.
 */

$label = $input['label'] ?? null;

if ($label === null || trim($label) === '') {
    http_response_code(422); // Unprocessable Entity
    return ['success' => false, 'error' => "Le champ 'label' est requis"];
}

// Pas de vraie persistance ici : on simule un id généré.
$newItem = [
    'id'    => random_int(100, 999),
    'label' => $label,
];

http_response_code(201); // Created

return ['success' => true, 'message' => 'Élément créé', 'item' => $newItem];
