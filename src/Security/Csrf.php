<?php

/**
 * Protection CSRF (Cross-Site Request Forgery).
 *
 * Principe : un jeton aléatoire est stocké en session ; toute requête qui
 * modifie des données (POST/PUT/PATCH/DELETE) doit renvoyer ce même jeton.
 * Un site tiers ne peut pas le connaître, donc une requête forgée depuis
 * un autre site est rejetée même si le cookie de session est envoyé.
 *
 * Nécessite que session_start() ait déjà été appelé avant utilisation.
 */
class Csrf
{
    /**
     * Retourne le token courant, en le générant s'il n'existe pas encore.
     */
    public static function token(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie qu'un token reçu correspond à celui en session.
     * hash_equals() compare en temps constant (évite les attaques par timing).
     */
    public static function isValid($token): bool
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
