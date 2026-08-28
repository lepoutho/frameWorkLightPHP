# Appel AJAX & Promesses JavaScript

Résumé du mécanisme utilisé dans `ajax.php` pour appeler l'API REST (`REST/serveurRest.php`) de façon asynchrone.

## 1. `fetch()` : faire une requête HTTP en JavaScript

```javascript
fetch(url, options)
    .then(response => /* traiter la réponse */)
    .catch(error => /* gérer une erreur réseau */);
```

- `fetch()` est natif au navigateur, pas besoin de librairie.
- Il retourne immédiatement une **Promise** — un objet représentant un résultat pas encore disponible.
- L'appel est **asynchrone** : le reste du code JS continue de s'exécuter sans attendre la réponse réseau. La page ne se fige jamais pendant l'attente.

### Exemple d'ordre d'exécution

```javascript
console.log('1');
fetch(url).then(() => console.log('2'));
console.log('3');
```

Affiche `1`, `3`, puis `2` — car `fetch()` ne bloque pas le programme : JS continue immédiatement sur la suite du code, et le `.then()` ne s'exécute que plus tard, quand la réponse arrive réellement.

## 2. Les Promises : les 3 états

Une Promise, c'est comme un ticket de pressing : on ne récupère pas le résultat tout de suite, juste la promesse qu'il arrivera. Elle a 3 états possibles :

| État | Signification |
|---|---|
| **pending** (en attente) | Résultat pas encore connu |
| **fulfilled** (tenue) | Succès → déclenche `.then()` |
| **rejected** (rejetée) | Échec → déclenche `.catch()` |

Créer une Promise soi-même (pour comprendre le mécanisme interne) :

```javascript
const maPromise = new Promise(function (resolve, reject) {
    setTimeout(function () {
        const succes = true;
        if (succes) {
            resolve('Voici le résultat !');   // → passe à l'état "fulfilled"
        } else {
            reject('Une erreur est survenue'); // → passe à l'état "rejected"
        }
    }, 2000);
});
```

`fetch()` fait exactement ça en interne : il appelle `resolve()` si la requête aboutit, `reject()` si le réseau échoue. On n'appelle jamais `resolve`/`reject` nous-mêmes avec `fetch`.

## 3. `.then()` : enchaîner sur le résultat

**Règle clé** : le code d'un `.then()` ne s'exécute **que si la Promise précédente est tenue (fulfilled)**, avec le résultat transmis en paramètre. Si elle est **rejetée**, le `.then()` est sauté et on passe directement au `.catch()` le plus proche dans la chaîne.

```javascript
fetch(url, options)
    .then(function (response) { ... })   // exécuté seulement si fetch() réussit
    .then(function (result) { ... })     // exécuté seulement si le .then() précédent réussit aussi
    .catch(function (error) { ... });    // exécuté si N'IMPORTE LEQUEL des étages au-dessus échoue
```

Le `.catch()` final agit comme un filet de sécurité unique pour toute la chaîne, peu importe à quel étage l'échec survient.

## 4. Pourquoi deux `.then()` imbriqués pour lire le JSON

```javascript
fetch(url, options)
    .then(function (response) {
        // response = métadonnées (status, headers...), PAS encore le corps exploitable
        return response.json().then(function (data) {
            return { status: response.status, data: data };
        });
    })
    .then(function (result) {
        // ici, result.data contient enfin l'objet JS équivalent au JSON renvoyé par le serveur
        resultatEl.textContent = JSON.stringify(result.data, null, 2);
    })
    .catch(function (error) {
        resultatEl.textContent = 'Erreur : ' + error.message;
    });
```

- `response.json()` lit le corps de la réponse — lire prend du temps, donc **cette méthode retourne elle aussi une Promise**.
- Quand un `.then()` retourne une Promise, le `.then()` suivant attend automatiquement sa résolution avant de s'exécuter. C'est ce qui permet d'enchaîner sans tout imbriquer.

## 5. Piège important : `.catch()` n'attrape pas les erreurs HTTP (404, 500...)

`.catch()` ne se déclenche que pour une **erreur réseau** (serveur injoignable, CORS bloqué...) — **pas** pour un code HTTP d'erreur. Pour `fetch()`, une réponse 404 est un "succès" au sens JS : la requête a bien abouti, seul son contenu indique une erreur métier.

Il faut donc vérifier `response.ok` manuellement si on veut traiter les codes 4xx/5xx comme des échecs :

```javascript
.then(function (response) {
    if (!response.ok) {
        throw new Error('Erreur HTTP : ' + response.status);
    }
    return response.json();
})
```

## 6. Alternative plus lisible : `async` / `await`

Même logique, syntaxe différente — se lit presque comme du code synchrone :

```javascript
async function appelRest(method, params, body) {
    try {
        const response = await fetch(url, options);
        const data = await response.json();
        resultatEl.textContent = 'HTTP ' + response.status + '\n' + JSON.stringify(data, null, 2);
    } catch (error) {
        resultatEl.textContent = 'Erreur : ' + error.message;
    }
}
```

`await` met en pause l'exécution de la fonction jusqu'à ce que la Promise se résolve, sans jamais bloquer le reste de la page.

## 7. Application concrète dans `ajax.php`

```javascript
fetch(REST_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ label: 'Nouvel élément' })
})
```

Côté serveur (`REST/serveurRest.php`), la lecture du corps se fait avec :

```php
$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody, true);
```

`php://input` est un flux donnant accès au corps brut de la requête HTTP, quel que soit son format — contrairement à `$_POST`, qui n'est rempli automatiquement que pour `application/x-www-form-urlencoded` ou `multipart/form-data`. Un corps envoyé en `application/json` (comme ici) **n'apparaît jamais dans `$_POST`**, d'où la nécessité de lire `php://input` explicitement.
