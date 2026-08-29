<?php

require_once __DIR__ . '/autoload.php';

$config = new IniConfig(__DIR__ . '/config/settings.ini');
$authUrl = $config->get('AUTH', 'url');

if ($authUrl === null) {
    throw new RuntimeException("Clé 'url' introuvable dans la section [AUTH] de settings.ini");
}
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
        const AUTH_URL = <?php echo json_encode($authUrl); ?>;
        const resultatEl = document.getElementById('resultat');

        async function appelAuth(action, method, body) {
            resultatEl.textContent = 'Chargement...';

            try {
                const url = AUTH_URL + '?action=' + encodeURIComponent(action);
                const options = {
                    method: method,
                    credentials: 'same-origin' // envoie/reçoit le cookie de session PHPSESSID
                };

                if (body) {
                    options.headers = { 'Content-Type': 'application/json' };
                    options.body = JSON.stringify(body);
                }

                const response = await fetch(url, options);
                const data = await response.json();
                resultatEl.textContent = 'HTTP ' + response.status + '\n' + JSON.stringify(data, null, 2);
            } catch (error) {
                resultatEl.textContent = 'Erreur : ' + error.message;
            }
        }

        function getCredentials() {
            return {
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            };
        }

        document.getElementById('btnRegister').addEventListener('click', function () {
            appelAuth('register', 'POST', getCredentials());
        });

        document.getElementById('btnLogin').addEventListener('click', function () {
            appelAuth('login', 'POST', getCredentials());
        });

        document.getElementById('btnLogout').addEventListener('click', function () {
            appelAuth('logout', 'POST', null);
        });

        document.getElementById('btnMe').addEventListener('click', function () {
            appelAuth('me', 'GET', null);
        });
    </script>
</body>
</html>
