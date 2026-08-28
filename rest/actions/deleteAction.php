<?php

/**
 * Action DELETE : suppression d'une ressource.
 * $input contient le JSON décodé du corps de la requête (doit inclure 'id').
 */

$id = $input['id'] ?? null;

if ($id === null) {
    http_response_code(422);
    return ['success' => false, 'error' => "Le champ 'id' est requis pour une suppression"];
}

// Pas de vraie persistance ici : on simule juste la confirmation.
return ['success' => true, 'message' => "Élément id={$id} supprimé"];
