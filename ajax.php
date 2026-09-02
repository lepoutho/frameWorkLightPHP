<?php

require_once __DIR__ . '/autoload.php';

$config = new IniConfig(__DIR__ . '/config/settings.ini');
$baseUrl = $config->get('app', 'base_url');
$restPath = $config->get('POST', 'path');
$authPath = $config->get('AUTH', 'path');

if ($baseUrl === null || $restPath === null || $authPath === null) {
    throw new RuntimeException("Clé 'base_url', 'POST.path' ou 'AUTH.path' introuvable dans settings.ini");
}

$restUrl = rtrim($baseUrl, '/') . '/' . ltrim($restPath, '/');
// Le token CSRF est fourni par l'endpoint d'authentification (route ?action=csrf),
// commun à tout le site puisqu'il est lié à la session, pas à une route en particulier.
$csrfUrl = rtrim($baseUrl, '/') . '/' . ltrim($authPath, '/') . '?action=csrf';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test AJAX / REST</title>
</head>
<body>
    <h1>Test des routes REST</h1>

    <button id="btnGet">GET (liste)</button>
    <button id="btnGetOne">GET (id=1)</button>
    <button id="btnPost">POST (créer)</button>
    <button id="btnPut">PUT (modifier id=1)</button>
    <button id="btnDelete">DELETE (id=1)</button>

    <pre id="resultat">(résultat affiché ici)</pre>

    <script>
        // URLs injectées côté serveur depuis config/settings.ini
        const REST_URL = <?php echo json_encode($restUrl, JSON_UNESCAPED_SLASHES); ?>;
        const CSRF_URL = <?php echo json_encode($csrfUrl, JSON_UNESCAPED_SLASHES); ?>;

        const resultatEl = document.getElementById('resultat');
        let csrfToken = null;

        async function recupererCsrfToken() {
            const response = await fetch(CSRF_URL, { credentials: 'same-origin' });
            const data = await response.json();
            csrfToken = data.csrf_token;
        }

        async function appelRest(method, params, body) {
            resultatEl.textContent = 'Chargement...';

            try {
                let url = REST_URL;
                const options = { method: method, headers: {}, credentials: 'same-origin' };

                if (method === 'GET') {
                    const query = new URLSearchParams(params || {}).toString();
                    if (query) {
                        url += '?' + query;
                    }
                } else {
                    if (csrfToken === null) {
                        await recupererCsrfToken();
                    }
                    const bodyAvecToken = Object.assign({}, body || {}, { csrf_token: csrfToken });
                    options.headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(bodyAvecToken);
                }

                const response = await fetch(url, options);
                const data = await response.json();
                resultatEl.textContent = 'HTTP ' + response.status + '\n' + JSON.stringify(data, null, 2);
            } catch (error) {
                resultatEl.textContent = 'Erreur : ' + error.message;
            }
        }

        document.getElementById('btnGet').addEventListener('click', function () {
            appelRest('GET');
        });

        document.getElementById('btnGetOne').addEventListener('click', function () {
            appelRest('GET', { id: 1 });
        });

        document.getElementById('btnPost').addEventListener('click', function () {
            appelRest('POST', null, { label: 'Nouvel élément' });
        });

        document.getElementById('btnPut').addEventListener('click', function () {
            appelRest('PUT', null, { id: 1, label: 'Élément modifié' });
        });

        document.getElementById('btnDelete').addEventListener('click', function () {
            appelRest('DELETE', null, { id: 1 });
        });
    </script>
</body>
</html>
