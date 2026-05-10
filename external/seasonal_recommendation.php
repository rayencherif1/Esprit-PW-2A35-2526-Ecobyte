<?php
// external/seasonal_recommendation.php
// API de recommandation saisonnière basée sur la météo ou la date

ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!defined('SEASONAL_API_LOCAL_INCLUDE')) {
    header('Content-Type: application/json; charset=utf-8');
}

function normalizeText(string $text): string {
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
    if ($text === null) {
        return '';
    }
    return preg_replace('/\s+/u', ' ', $text);
}

function normalizeLocation(string $location): string {
    $location = trim($location);
    $location = preg_replace('/[^a-zA-ZÀ-ÿ0-9\s\-]/u', '', $location);
    $location = preg_replace('/\s+/u', ' ', $location);
    return mb_substr($location, 0, 50);
}

function getTunisianCities(): array {
    return [
        'Tunis',
        'Ariana',
        'Ben Arous',
        'Manouba',
        'Nabeul',
        'Zaghouan',
        'Bizerte',
        'Beja',
        'Jendouba',
        'Le Kef',
        'Siliana',
        'Sousse',
        'Monastir',
        'Mahdia',
        'Sfax',
        'Kairouan',
        'Kasserine',
        'Sidi Bouzid',
        'Gabes',
        'Medenine',
        'Tataouine',
        'Gafsa',
        'Tozeur',
        'Kebili'
    ];
}

function sanitizeTunisianCity(string $location): string {
    $city = normalizeLocation($location);
    $allowedCities = getTunisianCities();

    foreach ($allowedCities as $allowed) {
        if (mb_strtolower($city) === mb_strtolower($allowed)) {
            return $allowed;
        }
    }

    return 'Tunis';
}

function containsAny(string $haystack, array $needles): bool {
    foreach ($needles as $needle) {
        if ($needle === '') {
            continue;
        }
        if (mb_strpos($haystack, mb_strtolower($needle)) !== false) {
            return true;
        }
    }
    return false;
}

function getSeasonFromDate(): string {
    $month = intval(date('n'));
    if ($month >= 3 && $month <= 5) return 'printemps';
    if ($month >= 6 && $month <= 8) return 'ete';
    if ($month >= 9 && $month <= 11) return 'automne';
    return 'hiver';
}

function getFallbackWeather(): array {
    $season = getSeasonFromDate();
    $map = [
        'printemps' => ['temp' => 18, 'description' => 'Doux et fleuri'],
        'ete' => ['temp' => 28, 'description' => 'Chaud et ensoleillé'],
        'automne' => ['temp' => 15, 'description' => 'Frais et coloré'],
        'hiver' => ['temp' => 6, 'description' => 'Froid et couvert']
    ];
    $fallback = $map[$season] ?? ['temp' => 20, 'description' => 'Tempéré'];
    return array_merge($fallback, [
        'temp_min' => max(0, intval($fallback['temp']) - 4),
        'temp_max' => intval($fallback['temp']) + 4,
        'day_description' => 'Conditions stables en journée',
        'night_description' => 'Nuit calme et fraîche',
        'sunrise' => '06:00 AM',
        'sunset' => '06:00 PM'
    ]);
}

function getWeather(string $location = 'Tunis'): array {
    $location = sanitizeTunisianCity($location);
    $queryLocation = rawurlencode($location . ',Tunisie');
    $url = 'https://wttr.in/' . $queryLocation . '?format=j1';
    $json = false;

    if (ini_get('allow_url_fopen')) {
        $json = @file_get_contents($url);
    }

    if ($json === false && function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $json = curl_exec($ch);
        curl_close($ch);
    }

    if ($json !== false) {
        $data = json_decode($json, true);
        if ($data && isset($data['current_condition'][0])) {
            $current = $data['current_condition'][0];
            $temp = intval($current['temp_C'] ?? 0);
            $desc = $current['weatherDesc'][0]['value'] ?? 'Inconnue';
            $today = $data['weather'][0] ?? [];
            $hourly = $today['hourly'] ?? [];

            $dayDescription = $desc;
            $nightDescription = $desc;
            if (is_array($hourly) && count($hourly) > 0) {
                $daySlot = $hourly[min(4, count($hourly) - 1)] ?? [];
                $nightSlot = $hourly[min(7, count($hourly) - 1)] ?? [];
                $dayDescription = $daySlot['weatherDesc'][0]['value'] ?? $dayDescription;
                $nightDescription = $nightSlot['weatherDesc'][0]['value'] ?? $nightDescription;
            }

            return [
                'temp' => $temp,
                'description' => $desc,
                'temp_min' => intval($today['mintempC'] ?? $temp),
                'temp_max' => intval($today['maxtempC'] ?? $temp),
                'day_description' => $dayDescription,
                'night_description' => $nightDescription,
                'sunrise' => $today['astronomy'][0]['sunrise'] ?? '',
                'sunset' => $today['astronomy'][0]['sunset'] ?? '',
                'source' => 'live',
                'location' => $location
            ];
        }
    }

    $fallback = getFallbackWeather();
    $fallback['source'] = 'fallback';
    $fallback['location'] = $location;
    return $fallback;
}

function getDbConnection(): ?PDO {
    try {
        $db = new PDO('mysql:host=localhost;dbname=marketplace;charset=utf8', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        return null;
    }
}

function getProducts(PDO $db): array {
    $stmt = $db->prepare(
        'SELECT p.id, p.nom, p.prix, p.description, p.categorie_id, p.ventes, p.stock, p.nutriscore, p.is_promo, p.prix_promo, COALESCE(c.nom, "") AS categorie_nom
         FROM produits p
         LEFT JOIN categories c ON p.categorie_id = c.id'
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function buildRecommendationRules(string $season, array $weather): array {
    $rules = [];

    $seasonRules = [
        'ete' => [
            ['keywords' => ['smoothie', 'jus', 'boisson', 'eau', 'salade', 'glace', 'fruit', 'fruits', 'frais', 'menthe', 'coco', 'citron', 'melon', 'pastèque', 'pastèque'], 'weight' => 12],
            ['keywords' => ['yaourt', 'granola', 'verrine', 'petit-déjeuner', 'petit dejeuner'], 'weight' => 8]
        ],
        'hiver' => [
            ['keywords' => ['tisane', 'thé', 'the', 'chocolat', 'café', 'cafe', 'soupe', 'miel', 'gingembre', 'pain', 'épice', 'epice', 'noix', 'châtaigne', 'chataigne', 'cannelle'], 'weight' => 12],
            ['keywords' => ['confiture', 'gâteau', 'gateau', 'dessert', 'lait', 'céréale', 'cereale'], 'weight' => 7]
        ],
        'printemps' => [
            ['keywords' => ['frais', 'légume', 'legume', 'fruit', 'salade', 'herbe', 'asperge', 'yaourt', 'printemps', 'citron', 'asperges'], 'weight' => 11],
            ['keywords' => ['smoothie', 'jus', 'yaourt', 'fromage blanc', 'smoothies'], 'weight' => 7]
        ],
        'automne' => [
            ['keywords' => ['noix', 'céréale', 'cereale', 'potiron', 'gingembre', 'compote', 'pomme', 'poire', 'réconfort', 'reconfort', 'pain', 'soupe', 'caramel', 'citrouille'], 'weight' => 11],
            ['keywords' => ['miel', 'châtaigne', 'chataigne', 'purée', 'puree', 'pâte', 'pate'], 'weight' => 7]
        ]
    ];

    if (isset($seasonRules[$season])) {
        $rules = array_merge($rules, $seasonRules[$season]);
    }

    if ($weather['temp'] >= 25) {
        $rules[] = ['keywords' => ['smoothie', 'jus', 'boisson', 'eau', 'glace', 'salade', 'fruits', 'frais'], 'weight' => 10];
    }
    if ($weather['temp'] <= 12) {
        $rules[] = ['keywords' => ['tisane', 'thé', 'the', 'chocolat', 'café', 'cafe', 'soupe', 'miel', 'pain', 'épice', 'epice', 'noix'], 'weight' => 10];
    }
    if (preg_match('/pluie|orage|nuage|couvert|snow|neige|rain|storm/i', $weather['description'])) {
        $rules[] = ['keywords' => ['thé', 'chocolat', 'café', 'cafe', 'soupe', 'miel', 'pain'], 'weight' => 9];
    }
    if (preg_match('/ensoleillé|soleil|chaud|clear|sun/i', $weather['description'])) {
        $rules[] = ['keywords' => ['smoothie', 'jus', 'boisson', 'salade', 'fruit', 'glace', 'frais'], 'weight' => 9];
    }

    return $rules;
}

function scoreProduct(array $product, array $rules, array $weather): int {
    $haystack = normalizeText($product['nom'] . ' ' . ($product['description'] ?? '') . ' ' . ($product['categorie_nom'] ?? ''));
    $score = 0;
    $matchedRules = 0;

    foreach ($rules as $rule) {
        if (containsAny($haystack, $rule['keywords'])) {
            $score += $rule['weight'];
            $matchedRules++;
        }
    }
    if ($matchedRules === 0) {
        // Evite les produits hors contexte (météo/saison) même s'ils sont populaires.
        $score -= 8;
    } else {
        $score += min(10, $matchedRules * 2);
    }

    $productSeason = normalizeText((string)($product['saison'] ?? ''));
    $currentSeason = getSeasonFromDate();
    if ($productSeason !== '') {
        if (containsAny($productSeason, [$currentSeason])) {
            $score += 10;
        } else {
            $score -= 6;
        }
    }
    $isHotWeather = intval($weather['temp'] ?? 0) >= 25;
    $isColdWeather = intval($weather['temp'] ?? 0) <= 12;
    $isSunny = preg_match('/ensoleille|ensoleillé|soleil|clear|sun/i', (string)($weather['description'] ?? '')) === 1;
    $isRainy = preg_match('/pluie|orage|nuage|couvert|snow|neige|rain|storm/i', (string)($weather['description'] ?? '')) === 1;

    $hotDrinkKeywords = ['tisane', 'the', 'thé', 'cafe', 'café', 'chocolat chaud', 'soupe'];
    $freshKeywords = ['jus', 'smoothie', 'boisson', 'eau', 'glace', 'salade', 'fruit'];

    if (($isHotWeather || $isSunny) && containsAny($haystack, $hotDrinkKeywords)) {
        $score -= 18;
    }
    if (($isColdWeather || $isRainy) && containsAny($haystack, $freshKeywords)) {
        $score -= 8;
    }
    if (($isHotWeather || $isSunny) && containsAny($haystack, $freshKeywords)) {
        $score += 6;
    }
    if (($isColdWeather || $isRainy) && containsAny($haystack, $hotDrinkKeywords)) {
        $score += 7;
    }

    $ventes = intval($product['ventes'] ?? 0);
    $score += min(24, intval($ventes / 6));

    $stock = intval($product['stock'] ?? 0);
    if ($stock <= 0) {
        $score -= 100;
    } else {
        $score += min(10, $stock);
    }

    $nutri = strtoupper(trim((string)($product['nutriscore'] ?? '')));
    if (in_array($nutri, ['A', 'B'], true)) {
        $score += 8;
    } elseif ($nutri === 'C') {
        $score += 4;
    }

    if (!empty($product['is_promo']) && floatval($product['prix_promo'] ?? 0) > 0) {
        $score += 3;
    }

    return $score;
}

function findRecommendations(array $products, array $rules, array $weather, int $limit = 6): array {
    $scored = [];
    foreach ($products as $product) {
        $scored[] = ['product' => $product, 'score' => scoreProduct($product, $rules, $weather)];
    }

    usort($scored, static function ($a, $b) {
        if ($b['score'] === $a['score']) {
            return strcmp($a['product']['nom'], $b['product']['nom']);
        }
        return $b['score'] <=> $a['score'];
    });

    $recommendations = [];
    $categoryCount = [];
    foreach ($scored as $item) {
        if (count($recommendations) >= $limit) {
            break;
        }
        $category = trim((string)($item['product']['categorie_nom'] ?? 'Divers'));
        $category = $category !== '' ? $category : 'Divers';
        $count = $categoryCount[$category] ?? 0;
        if ($count >= 2) {
            continue;
        }
        $recommendations[] = $item['product'];
        $categoryCount[$category] = $count + 1;
    }

    if (count($recommendations) < $limit) {
        $existingIds = array_map(static fn($p) => intval($p['id'] ?? 0), $recommendations);
        foreach ($scored as $item) {
            if (count($recommendations) >= $limit) {
                break;
            }
            $id = intval($item['product']['id'] ?? 0);
            if (in_array($id, $existingIds, true)) {
                continue;
            }
            $recommendations[] = $item['product'];
            $existingIds[] = $id;
        }
    }

    return $recommendations;
}

function buildSeasonMessage(string $season, array $weather): string {
    $temperature = intval($weather['temp']);
    $min = intval($weather['temp_min'] ?? $temperature);
    $max = intval($weather['temp_max'] ?? $temperature);
    switch ($season) {
        case 'ete':
            return "Il fait chaud ({$temperature}°C, min {$min}°C / max {$max}°C), découvrez des smoothies, jus et boissons fraîches !";
        case 'hiver':
            return "Il fait froid ({$temperature}°C, min {$min}°C / max {$max}°C), optez pour des tisanes, chocolats chauds et plats réconfortants !";
        case 'printemps':
            return "Le printemps est arrivé ({$temperature}°C), profitez de produits légers, fruits frais et recettes vitaminées !";
        case 'automne':
            return "L'automne s'installe ({$temperature}°C), découvrez des saveurs chaudes et réconfortantes comme noix, potiron et épices !";
        default:
            return "Voici des recommandations saisonnières basées sur la météo actuelle et vos produits disponibles.";
    }
}

function getSeasonalRecommendations(string $location = 'Tunis'): array {
    $location = sanitizeTunisianCity($location);
    $season = getSeasonFromDate();
    $weather = getWeather($location);

    $db = getDbConnection();
    if (!$db) {
        return [
            'season' => $season,
            'weather' => $weather,
            'message' => 'Impossible de se connecter à la base de données.',
            'recommendations' => []
        ];
    }

    $products = getProducts($db);
    if (empty($products)) {
        return [
            'season' => $season,
            'weather' => $weather,
            'message' => 'Aucun produit trouvé dans la base.',
            'recommendations' => []
        ];
    }

    $rules = buildRecommendationRules($season, $weather);
    $recommendations = findRecommendations($products, $rules, $weather, 6);
    $message = buildSeasonMessage($season, $weather);

    return [
        'season' => $season,
        'location' => $weather['location'] ?? $location,
        'weather' => $weather,
        'message' => $message,
        'recommendations' => $recommendations
    ];
}

if (!defined('SEASONAL_API_LOCAL_INCLUDE')) {
    $location = isset($_GET['location']) ? trim($_GET['location']) : 'Tunis';
    echo json_encode(getSeasonalRecommendations($location), JSON_UNESCAPED_UNICODE);
    exit;
}

?>
