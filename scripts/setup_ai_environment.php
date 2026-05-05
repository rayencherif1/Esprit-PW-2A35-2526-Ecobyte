<?php
/**
 * Usage : php scripts/setup_ai_environment.php
 *
 * - Met à jour .env (OLLAMA_* uniquement) depuis les constantes PHP
 * - Crée la table ollama_ia_profils si PDO MySQL disponible en CLI
 */
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

require $root . '/config/config.php';

$envPath = $root . DIRECTORY_SEPARATOR . '.env';
$lines = is_file($envPath) ? file($envPath, FILE_IGNORE_NEW_LINES) : [];
if ($lines === false) {
    $lines = [];
}

$stripKeys = ['OLLAMA_MODEL', 'OLLAMA_BASE_URL', 'GEMINI_API_KEY', 'GEMINI_MODEL'];
$filtered = [];
foreach ($lines as $line) {
    $skip = false;
    foreach ($stripKeys as $sk) {
        if (preg_match('/^\s*' . preg_quote($sk, '/') . '\s*=/', $line)) {
            $skip = true;
            break;
        }
    }
    if (!$skip) {
        $filtered[] = $line;
    }
}

$block = ['# Mis à jour par scripts/setup_ai_environment.php'];
$om = trim((string) OLLAMA_MODEL);
if ($om !== '') {
    $block[] = 'OLLAMA_MODEL=' . envEscapeValue($om);
}
$ob = trim((string) OLLAMA_BASE_URL);
$block[] = 'OLLAMA_BASE_URL=' . envEscapeValue($ob !== '' ? $ob : 'http://127.0.0.1:11434');

$out = rtrim(implode("\n", array_merge($filtered, $block))) . "\n";
if (file_put_contents($envPath, $out) === false) {
    fwrite(STDERR, "Impossible d'écrire .env\n");
    exit(1);
}

echo "OK — .env synchronisé (OLLAMA_MODEL : " . ($om !== '' ? 'oui' : 'non') . ").\n";

require $root . '/app/bootstrap.php';

try {
    $pdo = Database::getPdo();
    $sqlCreate = <<<'SQL'
CREATE TABLE IF NOT EXISTS ollama_ia_profils (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    modele VARCHAR(120) NOT NULL DEFAULT '',
    instructions_supplementaires TEXT NOT NULL,
    actif TINYINT(1) NOT NULL DEFAULT 0,
    date_creation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ollama_ia_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
    $pdo->exec($sqlCreate);

    $stmt = $pdo->query('SELECT COUNT(*) AS c FROM ollama_ia_profils');
    $n = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
    if ($n === 0) {
        $pdo->exec("INSERT INTO ollama_ia_profils (nom, modele, instructions_supplementaires, actif) VALUES ('Défaut', '', '', 1)");
        $n = 1;
    }
    echo "OK — table ollama_ia_profils : {$n} ligne(s).\n";
} catch (Throwable $e) {
    echo "Note : MySQL non joignable depuis ce PHP (" . $e->getMessage() . ").\n";
    echo "      Importez database/migration_ollama_ia_profils.sql dans phpMyAdmin.\n";
    echo "      Si vous aviez gemini_ia_profils : database/migration_rename_gemini_table_to_ollama.sql\n";
}

echo "cURL PHP : " . (function_exists('curl_init') ? 'oui' : 'NON — activez extension=curl') . "\n";

function envEscapeValue(string $v): string
{
    if ($v === '') {
        return '""';
    }
    if (preg_match('/[\s#"\'\\\\]/', $v)) {
        return '"' . addcslashes($v, "\\\"\r\n\t") . '"';
    }

    return $v;
}
