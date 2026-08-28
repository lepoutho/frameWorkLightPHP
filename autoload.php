<?php

/**
 * Autoloader maison.
 *
 * Convention : chaque classe "MaClasse" doit se trouver dans un fichier
 * "MaClasse.php", quelque part sous le dossier src/ (peu importe le
 * sous-dossier). Pas de namespace : le nom de fichier = nom de la classe.
 */

spl_autoload_register(function (string $className): void {
    static $classMap = null;

    // On construit la table (classe => chemin de fichier) une seule fois,
    // en scannant récursivement le dossier src/.
    if ($classMap === null) {
        $classMap = [];

        $srcDir = __DIR__ . '/src';

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $classMap[$file->getBasename('.php')] = $file->getPathname();
            }
        }
    }

    if (isset($classMap[$className])) {
        require_once $classMap[$className];
    }
});
