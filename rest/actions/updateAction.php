<?php

/**
 * Action PUT/PATCH : modification d'une ressource existante.
 * $input contient le JSON décodé du corps de la requête (doit inclure 'id').
 */

$id = $input['id'] ?? null;

if ($id === null) {
    http_response_code(422);
    return ['success' => false, 'error' => "Le champ 'id' est requis pour une modification"];
}

// Pas de vraie persistance ici : on renvoie simplement ce qui aurait été modifié.
$updatedItem = [
    'id'    => (int) $id,
    'label' => $input['label'] ?? '(inchangé)',
];

return ['success' => true, 'message' => "Élément id={$id} modifié", 'item' => $updatedItem];
