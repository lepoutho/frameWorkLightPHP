<?php

require_once __DIR__ . '/autoload.php';

$config = new IniConfig(__DIR__ . '/config/settings.ini');
$restUrl = $config->get('POST', 'url');

if ($restUrl === null) {
    throw new RuntimeException("Clé 'url' introuvable dans la section [POST] de settings.ini");
}
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
        // URL injectée côté serveur depuis config/settings.ini (section [POST])
        const REST_URL = <?php echo json_encode($restUrl); ?>;

        const resultatEl = document.getElementById('resultat');

        function appelRest(method, params, body) {
            resultatEl.textContent = 'Chargement...';

            let url = REST_URL;
            const options = { method: method, headers: {} };

            if (method === 'GET') {
                const query = new URLSearchParams(params || {}).toString();
                if (query) {
                    url += '?' + query;
                }
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(body || {});
            }

            fetch(url, options)
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { status: response.status, data: data };
                    });
                })
                .then(function (result) {
                    resultatEl.textContent = 'HTTP ' + result.status + '\n' + JSON.stringify(result.data, null, 2);
                })
                .catch(function (error) {
                    resultatEl.textContent = 'Erreur : ' + error.message;
                });
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
