<?php

require_once __DIR__ . '/autoload.php';

$config = new IniConfig(__DIR__ . '/config/settings.ini');
$baseUrl = $config->get('app', 'base_url');
$authPath = $config->get('AUTH', 'path');

if ($baseUrl === null || $authPath === null) {
    throw new RuntimeException("Clé 'base_url' ([app]) ou 'path' ([AUTH]) introuvable dans settings.ini");
}

$authUrl = rtrim($baseUrl, '/') . '/' . ltrim($authPath, '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test authentification</title>
</head>
<body>
    <h1>Test inscription / connexion / déconnexion</h1>

    <fieldset>
        <legend>Email / mot de passe</legend>
        <input type="email" id="email" placeholder="email@exemple.fr" value="test@exemple.fr">
        <input type="password" id="password" placeholder="mot de passe" value="motdepasse123">
    </fieldset>

    <button id="btnRegister">S'inscrire</button>
    <button id="btnLogin">Se connecter</button>
    <button id="btnLogout">Se déconnecter</button>
    <button id="btnMe">Suis-je connecté ? (me)</button>

    <pre id="resultat">(résultat affiché ici)</pre>

    <script>
        // Seule valeur injectée depuis le PHP (lue dans config/settings.ini) ;
        // le reste de la logique vit dans js/auth.js
        window.AUTH_URL = <?php echo json_encode($authUrl, JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="js/auth.js"></script>
</body>
</html>
