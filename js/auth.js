// Utilise window.AUTH_URL, injecté par auth.php via une petite balise <script> inline
// (nécessaire car l'URL vient de config/settings.ini, lu côté PHP).

document.addEventListener('DOMContentLoaded', function () {
    const resultatEl = document.getElementById('resultat');
    let csrfToken = null;

    // Récupère le token CSRF une fois au chargement de la page.
    // Toute requête POST devra ensuite le renvoyer pour être acceptée par le serveur.
    async function recupererCsrfToken() {
        const response = await fetch(window.AUTH_URL + '?action=csrf', { credentials: 'same-origin' });
        const data = await response.json();
        csrfToken = data.csrf_token;
    }

    async function appelAuth(action, method, body) {
        resultatEl.textContent = 'Chargement...';

        try {
            if (csrfToken === null) {
                await recupererCsrfToken();
            }

            const url = window.AUTH_URL + '?action=' + encodeURIComponent(action);
            const options = {
                method: method,
                credentials: 'same-origin' // envoie/reçoit le cookie de session PHPSESSID
            };

            if (method !== 'GET') {
                // On ajoute systématiquement le token CSRF aux requêtes qui modifient l'état.
                const bodyAvecToken = Object.assign({}, body || {}, { csrf_token: csrfToken });
                options.headers = { 'Content-Type': 'application/json' };
                options.body = JSON.stringify(bodyAvecToken);
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

    // Pré-charge le token dès l'arrivée sur la page (facultatif, évite un aller-retour
    // supplémentaire au premier clic sur un bouton qui modifie l'état).
    recupererCsrfToken();
});
