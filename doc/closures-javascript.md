# Les closures en JavaScript

Explication du point le plus déroutant du fichier `js/auth.js` : pourquoi `csrfToken` garde sa valeur entre plusieurs clics, alors que la fonction qui l'a déclaré ne s'exécute qu'une seule fois.

## 1. Le problème observé dans le code

```javascript
document.addEventListener('DOMContentLoaded', function () {
    let csrfToken = null; // déclaré UNE SEULE FOIS, au chargement de la page

    async function appelAuth(action, method, body) {
        if (csrfToken === null) {
            await recupererCsrfToken(); // ne se redéclenche que si csrfToken est encore null
        }
        // ... utilise csrfToken
    }

    document.getElementById('btnLogin').addEventListener('click', function () {
        appelAuth('login', 'POST', getCredentials());
    });

    document.getElementById('btnLogout').addEventListener('click', function () {
        appelAuth('logout', 'POST', null);
    });
});
```

Le bloc `DOMContentLoaded` ne s'exécute **qu'une fois** (au chargement de la page). Pourtant, chaque bouton (`btnLogin`, `btnLogout`...) déclenche `appelAuth`, qui lit/modifie la **même** variable `csrfToken` — le token n'est récupéré qu'une seule fois, puis réutilisé pour tous les clics suivants.

## 2. Exemple minimal pour isoler le mécanisme

```javascript
function creerCompteur() {
    let compteur = 0; // déclaré une seule fois, quand creerCompteur() est appelée

    function incrementer() {
        compteur = compteur + 1;
        console.log(compteur);
    }

    return incrementer;
}

const monCompteur = creerCompteur();

monCompteur(); // affiche 1
monCompteur(); // affiche 2
monCompteur(); // affiche 3
```

`creerCompteur()` n'est appelée **qu'une seule fois**. Pourtant, chaque appel à `monCompteur()` se souvient de la valeur précédente de `compteur` au lieu de repartir de `0`.

## 3. Pourquoi la variable n'est pas détruite

Normalement, une variable locale (`let compteur = 0;`) est détruite dès que la fonction qui la contient (`creerCompteur`) termine son exécution.

**Mais** la fonction `incrementer` continue d'exister après la fin de `creerCompteur()` — on la retourne, et on la stocke dans `monCompteur`. Cette fonction a été **définie à l'intérieur** de `creerCompteur`, donc elle garde un lien invisible vers les variables de son environnement de création, dont `compteur`.

**C'est ça, une closure** : une fonction "emporte avec elle" les variables de son environnement de création, même après que cet environnement a fini de s'exécuter.

## 4. Portée (scope) ≠ Durée de vie (lifetime)

Point de confusion fréquent : on sait que `let`/`const` sont limitées au **bloc** ou à la **fonction** où elles sont déclarées — alors pourquoi `compteur` survit-elle après la fin de `creerCompteur` ? La réponse : la portée et la durée de vie sont **deux notions différentes**.

- **La portée** répond à : *"depuis quel endroit du code puis-je écrire le nom de cette variable ?"* — une règle **syntaxique**, décidée à l'écriture du code.
- **La durée de vie** répond à : *"combien de temps la valeur reste-t-elle en mémoire avant d'être détruite ?"* — décidée à l'exécution, par le **garbage collector** (ramasse-miettes) du moteur JavaScript.

### La portée est bien respectée

```javascript
function creerCompteur() {
    let compteur = 0;
    function incrementer() {
        compteur = compteur + 1; // OK, "compteur" est visible ici
        return compteur;
    }
    return incrementer;
}

console.log(compteur); // ❌ ReferenceError : compteur n'est pas défini ICI
```

`compteur` reste bien **inaccessible en dehors** de `creerCompteur` — impossible de taper `compteur` depuis l'extérieur. La portée par bloc/fonction n'est jamais violée.

### La règle réelle de destruction

Une valeur est détruite quand **plus rien ne peut y accéder** (elle devient *unreachable*) — pas simplement "quand on sort du bloc où elle a été déclarée".

Quand `creerCompteur()` se termine, le corps de la fonction cesse de s'exécuter — **mais** `incrementer` a été retournée et stockée dans `monCompteur`, donc elle "survit". Or `incrementer` référence encore `compteur` dans son propre code. `compteur` reste donc **atteignable** via `monCompteur` → le garbage collector ne peut pas la détruire, même si on a "quitté" le bloc où elle a été écrite.

### Analogie

- **Portée** = une règle de politesse : *"tu n'as pas le droit d'appeler quelqu'un par son prénom si tu n'as jamais été présenté"* (règle du code, statique).
- **Durée de vie** = une question de survie : *"cette personne existe tant que quelqu'un a son numéro de téléphone"* (réalité de la mémoire, dynamique).

`compteur` n'est **présenté** (visible) qu'à l'intérieur de `creerCompteur` — ça reste vrai. Mais `incrementer` a "le numéro de téléphone" de `compteur` inscrit dans son propre code, donc `compteur` reste vivant tant qu'`incrementer` existe quelque part.

### Contre-exemple : sans closure, la variable est bien détruite

```javascript
function creerCompteur() {
    let compteur = 0;
    compteur = compteur + 1;
    console.log(compteur); // affiche 1
    // aucune fonction interne n'est retournée, rien ne référence compteur ailleurs
}

creerCompteur(); // affiche 1
creerCompteur(); // affiche 1 (repart bien de 0, pas de mémoire persistante)
```

Ici, rien ne "s'échappe" de `creerCompteur` → dès que la fonction se termine, `compteur` devient inatteignable → le garbage collector la détruit réellement. C'est le comportement attendu d'une variable locale classique — la nuance n'apparaît que lorsqu'une fonction interne s'échappe en la référençant.

## 5. L'analogie du casier commun

Imaginez `csrfToken` comme un **casier commun** installé une fois pour toutes dans une pièce (le scope de `DOMContentLoaded`). Toutes les fonctions créées dans cette pièce (`appelAuth`, les callbacks de clic) ont **la clé de ce même casier**. Peu importe combien de fois on clique — c'est toujours le même casier qu'on ouvre, avec le contenu laissé par la dernière visite.

## 6. Le lien avec ce qu'on connaît déjà en PHP

```php
spl_autoload_register(function (string $className): void {
    static $classMap = null; // EXACTEMENT le même principe
    ...
});
```

| | PHP | JavaScript |
|---|---|---|
| Mot-clé nécessaire | `static` explicite | aucun — automatique |
| Déclenchement | dès qu'une variable locale doit survivre entre les appels d'une fonction enregistrée (ex: `spl_autoload_register`) | dès qu'une fonction est définie à l'intérieur d'une autre et capture une variable de son environnement |
| Risque si oublié | la variable se réinitialise à chaque appel | n'existe pas : la capture est automatique dès qu'il y a une fonction imbriquée |

En PHP, on doit **dire explicitement** "cette variable doit survivre". En JavaScript, ça arrive **automatiquement** dès qu'une fonction (ex: un callback de clic) est déclarée à l'intérieur d'une autre fonction qui possède la variable.

## 7. Exercice pour vérifier sa compréhension

```javascript
function creerSaluteur(nom) {
    return function () {
        console.log('Bonjour ' + nom);
    };
}

const salueAlice = creerSaluteur('Alice');
const salueBob = creerSaluteur('Bob');

salueAlice(); // affiche "Bonjour Alice"
salueBob();   // affiche "Bonjour Bob"
salueAlice(); // affiche "Bonjour Alice"
```

Contrairement à l'exemple de `csrfToken`, ici `creerSaluteur` est appelée **deux fois** (une fois par variable `salueAlice`/`salueBob`) → **deux closures distinctes** sont créées, chacune avec son propre `nom` capturé indépendamment. `salueAlice` et `salueBob` ne partagent rien : chacune a son propre "casier".

**Point clé à retenir** : le nombre de closures créées dépend du nombre de fois où la fonction *englobante* est appelée — pas du nombre de fois où la fonction *interne* (retournée) est ensuite invoquée.

- `creerCompteur()` appelée 1 fois → 1 seul casier `compteur`, partagé par tous les appels à `monCompteur()`
- `creerSaluteur()` appelée 2 fois → 2 casiers `nom` indépendants, un pour `salueAlice`, un pour `salueBob`

## 8. Application au projet

Dans `js/auth.js`, `DOMContentLoaded` ne se déclenche qu'**une fois par chargement de page** → une seule closure est créée → `csrfToken` est un casier unique, partagé par `appelAuth` et par tous les callbacks de clic enregistrés à l'intérieur. C'est pour cette raison que le token n'est récupéré qu'une seule fois côté serveur (via `?action=csrf`), puis réutilisé pour toutes les requêtes suivantes tant que la page n'est pas rechargée.
