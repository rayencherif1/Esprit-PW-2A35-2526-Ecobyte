<?php
/**
 * Charge un fichier .env (KEY=valeur) vers $_ENV et putenv — sans Composer.
 * Lignes vides et commentaires # ignorés. Valeurs entre guillemets acceptées.
 */

declare(strict_types=1);

final class EnvLoader
{
    public static function load(string $filePath): void
    {
        if (!is_readable($filePath)) {
            return;
        }

        $raw = file($filePath, FILE_IGNORE_NEW_LINES);
        if ($raw === false) {
            return;
        }

        foreach ($raw as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if ($name === '') {
                continue;
            }

            if ($value !== '' && strlen($value) >= 2) {
                $q0 = $value[0];
                $q1 = $value[strlen($value) - 1];
                if (($q0 === '"' && $q1 === '"') || ($q0 === "'" && $q1 === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            $_ENV[$name] = $value;
            putenv($name . '=' . $value);
        }
    }
}
