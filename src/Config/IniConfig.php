<?php

class IniConfig
{
    private $data;

    public function __construct(string $filePath, bool $processSections = true)
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException("Fichier INI introuvable ou illisible : {$filePath}");
        }

        $result = @parse_ini_file($filePath, $processSections, INI_SCANNER_TYPED);

        if ($result === false) {
            $error = error_get_last();
            throw new RuntimeException(
                "Erreur lors du parsing du fichier INI : " . ($error['message'] ?? 'inconnue')
            );
        }

        $this->data = $result;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function get(string $section, ?string $key = null)
    {
        if (!isset($this->data[$section])) {
            return null;
        }

        if ($key === null) {
            return $this->data[$section];
        }

        return $this->data[$section][$key] ?? null;
    }
}
