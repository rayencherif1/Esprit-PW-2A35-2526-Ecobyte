<?php
// external/nutriscore_service.php
// "Service externe" (simulé) pour calculer un Nutri‑Score à partir d'ingrédients.
// Objectif: fournir un résultat stable et cohérent pour le projet même si OpenFoodFacts ne matche pas.

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

function readJsonBody(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $json = json_decode($raw, true);
    return is_array($json) ? $json : [];
}

function normalizeText(?string $s): string {
    $s = $s ?? '';
    $s = trim(mb_strtolower($s));
    // Garder lettres/nombres/espaces/virgules
    $s = preg_replace('/[^\p{L}\p{N}\s,]/u', ' ', $s);
    $s = preg_replace('/\s+/u', ' ', $s);
    return trim($s);
}

function tokenizeIngredients(string $ingredients): array {
    $parts = preg_split('/[\s,]+/u', $ingredients, -1, PREG_SPLIT_NO_EMPTY);
    $tokens = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if (mb_strlen($p) < 3) continue;
        $tokens[$p] = true;
    }
    return array_keys($tokens);
}

function containsAny(string $haystack, array $needles): bool {
    foreach ($needles as $n) {
        if ($n !== '' && mb_strpos($haystack, $n) !== false) return true;
    }
    return false;
}

function computeNutriScoreFromIngredients(string $name, string $ingredients): array {
    // Heuristique de scoring (0..~20). Plus bas = meilleur.
    // Inspiré du principe Nutri‑Score (pénalités sucre/sel/gras, bonus fibres/fruits/noix/légumineuses).
    $score = 10.0; // base

    $t = $ingredients !== '' ? $ingredients : $name;

    // Pénalités
    if (containsAny($t, ['sucre', 'sirop', 'glucose', 'fructose', 'maltodextrine', 'dextrose', 'miel'])) $score += 3.5;
    if (containsAny($t, ['sel', 'sodium'])) $score += 2.0;
    if (containsAny($t, ['huile de palme', 'palme'])) $score += 3.0;
    if (containsAny($t, ['beurre', 'crème', 'creme', 'graisse', 'huile', 'frit'])) $score += 1.5;
    if (containsAny($t, ['chocolat', 'cacao'])) $score += 0.8;

    // Bonus
    if (containsAny($t, ['avoine', 'céréale complète', 'cereale complete', 'blé complet', 'ble complet', 'son', 'fibres'])) $score -= 2.2;
    if (containsAny($t, ['amande', 'noix', 'noisette', 'pistache', 'cacahuete', 'cacahuète', 'graines', 'chia', 'lin', 'sésame', 'sesame'])) $score -= 1.8;
    if (containsAny($t, ['dattes', 'raisins', 'banane', 'pomme', 'poire', 'fruits', 'fruit'])) $score -= 1.4;
    if (containsAny($t, ['légumineuse', 'legumineuse', 'pois chiche', 'lentille', 'haricot'])) $score -= 2.0;
    if (containsAny($t, ['yaourt', 'yogourt', 'lait fermenté', 'lait fermente'])) $score -= 0.6;
    // Bonus spécial pour barres énergétiques avec dattes
    if (containsAny($t, ['barre']) && containsAny($t, ['dattes'])) $score -= 2.0;

    // Si on n'a vraiment aucun ingrédient, on refuse de "mentir"
    $hasSignal = $ingredients !== '' && mb_strlen($ingredients) >= 10;
    if (!$hasSignal) {
        return [
            'nutriscore' => null,
            'reason' => 'missing_ingredients'
        ];
    }

    // Clamp + mapping
    if ($score < 0) $score = 0;
    if ($score > 20) $score = 20;

    // Intervalles: plus strict pour éviter des A trop faciles
    $grade = 'C';
    if ($score <= 5) $grade = 'A';
    elseif ($score <= 8) $grade = 'B';
    elseif ($score <= 12) $grade = 'C';
    elseif ($score <= 16) $grade = 'D';
    else $grade = 'E';

    return [
        'nutriscore' => $grade,
        'score' => round($score, 2)
    ];
}

$input = array_merge($_GET, $_POST, readJsonBody());
$name = normalizeText($input['name'] ?? '');
$ingredientsRaw = $input['ingredients'] ?? '';
$ingredients = normalizeText(is_string($ingredientsRaw) ? $ingredientsRaw : '');

// Autoriser "Ingrédients: ..."
if (preg_match('/ingr(?:e|é)dients?\s*:\s*(.+)$/iu', $ingredientsRaw, $m)) {
    $ingredients = normalizeText($m[1]);
}

$result = computeNutriScoreFromIngredients($name, $ingredients);

echo json_encode([
    'ok' => true,
    'nutriscore' => $result['nutriscore'],
    'score' => $result['score'] ?? null,
    'reason' => $result['reason'] ?? null,
    'service' => 'ecobite-nutriscore-simulated',
], JSON_UNESCAPED_UNICODE);

