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
    <h1>Test appel AJAX vers serveurRest.php</h1>

    <button id="btnAppel">Envoyer la requête POST</button>

    <pre id="resultat">(résultat affiché ici)</pre>

    <script>
        // URL injectée côté serveur depuis config/settings.ini (section [POST])
        const REST_URL = <?php echo json_encode($restUrl); ?>;

        document.getElementById('btnAppel').addEventListener('click', function () {
            const resultatEl = document.getElementById('resultat');
            resultatEl.textContent = 'Chargement...';

            fetch(REST_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ nom: 'Thomas' })
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Erreur HTTP : ' + response.status);
                    }
                    return response.json();
                })
                .then(function (data) {
                    resultatEl.textContent = JSON.stringify(data, null, 2);
                })
                .catch(function (error) {
                    resultatEl.textContent = 'Erreur : ' + error.message;
                });
        });
    </script>
</body>
</html>
