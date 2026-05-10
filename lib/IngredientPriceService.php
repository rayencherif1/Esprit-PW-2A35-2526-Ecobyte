<?php
declare(strict_types=1);

/**
 * Service de prix "temps reel" (API publique) + fallback local.
 *
 * API utilisee:
 * - DummyJSON products search: https://dummyjson.com/products/search?q=...
 */
final class IngredientPriceService
{
    /** @var array<string,float>|null */
    private static ?array $tokenPriceCache = null;
    private static int $apiCalls = 0;
    private const MAX_API_CALLS_PER_REQUEST = 40;
    /** @var array<string,bool> */
    private const VEGETABLE_TOKEN_SET = [
        'tomate' => true,
        'tomates' => true,
        'oignon' => true,
        'oignons' => true,
        'ail' => true,
        'carotte' => true,
        'carottes' => true,
        'courgette' => true,
        'courgettes' => true,
        'poivron' => true,
        'poivrons' => true,
        'pomme' => true,
        'pommes' => true,
        'pomme-de-terre' => true,
        'pommes-de-terre' => true,
        'pdt' => true,
        'concombre' => true,
        'concombres' => true,
        'salade' => true,
        'epinard' => true,
        'epinards' => true,
        'épinard' => true,
        'épinards' => true,
        'brocoli' => true,
        'haricot' => true,
        'haricots' => true,
        'champignon' => true,
        'champignons' => true,
        'citron' => true,
        'citrons' => true,
    ];

    /**
     * Estime le prix total en TND a partir d'une liste d'ingredients texte.
     */
    public static function estimateRecettePriceFromIngredients(string $ingredientsText): float
    {
        $tokens = self::extractTokens($ingredientsText);
        if (count($tokens) === 0) {
            return 0.0;
        }

        $sum = 0.0;
        $used = 0;
        foreach ($tokens as $token) {
            $price = self::priceForToken($token);
            if ($price <= 0.0) {
                continue;
            }
            $sum += $price;
            $used++;
        }

        if ($used === 0) {
            return 0.0;
        }

        return round($sum, 2);
    }

    public static function formatPrice(float $price): string
    {
        if ($price <= 0.0) {
            return 'Indisponible';
        }
        return number_format($price, 2, ',', ' ') . ' TND';
    }

    /**
     * Somme des prix des legumes via API uniquement (sans fallback).
     * Retourne null si rien n'a ete trouve cote API.
     */
    public static function estimateVegetablesPriceApiOnly(string $ingredientsText): ?float
    {
        self::$apiCalls = 0;
        $tokens = self::extractTokens($ingredientsText);
        if (count($tokens) === 0) {
            return null;
        }

        $sum = 0.0;
        $hits = 0;
        $filtered = array_values(array_filter($tokens, static fn (string $t): bool => isset(self::VEGETABLE_TOKEN_SET[$t])));
        // Si aucun legume n'est reconnu (ex. boisson citron, ingredient atypique), on tente quand meme l'API
        // avec les tokens bruts pour eviter "Non trouvé API" trop souvent.
        if (count($filtered) === 0) {
            $filtered = $tokens;
        }

        foreach ($filtered as $token) {
            $apiPrice = self::fetchApiPrice($token);
            if ($apiPrice <= 0.0) {
                continue;
            }
            $sum += $apiPrice;
            $hits++;
        }

        if ($hits === 0) {
            return null;
        }

        return round($sum, 2);
    }

    public static function formatApiOnlyPrice(?float $price): string
    {
        if ($price === null || $price <= 0.0) {
            return 'Non trouvé API';
        }
        return number_format($price, 2, ',', ' ') . ' TND';
    }

    /**
     * @return string[]
     */
    private static function extractTokens(string $raw): array
    {
        $text = trim($raw);
        if ($text === '') {
            return [];
        }

        if (function_exists('mb_strtolower')) {
            $text = mb_strtolower($text, 'UTF-8');
        } else {
            $text = strtolower($text);
        }

        $parts = preg_split('/[\s,;|•·\r\n]+/u', $text) ?: [];
        $tokens = [];
        foreach ($parts as $part) {
            $token = trim((string) preg_replace('/[^a-z0-9àâäéèêëïîôùûüç-]/ui', '', (string) $part));
            if ($token === '' || strlen($token) < 3) {
                continue;
            }
            $tokens[$token] = true;
            if (count($tokens) >= 6) {
                break;
            }
        }

        return array_keys($tokens);
    }

    private static function priceForToken(string $token): float
    {
        if (self::$tokenPriceCache === null) {
            self::$tokenPriceCache = [];
        }
        if (array_key_exists($token, self::$tokenPriceCache)) {
            return self::$tokenPriceCache[$token];
        }

        $apiPrice = self::fetchApiPrice($token);
        if ($apiPrice > 0.0) {
            self::$tokenPriceCache[$token] = $apiPrice;
            return $apiPrice;
        }

        $fallbackPrice = self::fallbackPrice($token);
        self::$tokenPriceCache[$token] = $fallbackPrice;
        return $fallbackPrice;
    }

    private static function fetchApiPrice(string $token): float
    {
        if (self::$apiCalls >= self::MAX_API_CALLS_PER_REQUEST) {
            return 0.0;
        }
        $queries = self::apiQueryCandidates($token);
        foreach ($queries as $query) {
            if (self::$apiCalls >= self::MAX_API_CALLS_PER_REQUEST) {
                return 0.0;
            }
            self::$apiCalls++;

            $url = 'https://dummyjson.com/products/search?q=' . rawurlencode($query) . '&limit=3';
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 2,
                    'header' => "User-Agent: recette-app/1.0\r\nAccept: application/json\r\n",
                ],
            ]);
            $raw = @file_get_contents($url, false, $context);
            if ($raw === false || trim($raw) === '') {
                continue;
            }

            $data = json_decode($raw, true);
            if (!is_array($data) || !isset($data['products']) || !is_array($data['products'])) {
                continue;
            }

            foreach ($data['products'] as $product) {
                if (!is_array($product) || !isset($product['price'])) {
                    continue;
                }
                $usd = (float) $product['price'];
                if ($usd <= 0.0) {
                    continue;
                }
                // Conversion demo: 1 USD ~= 3.10 TND
                return round($usd * 3.10, 2);
            }
        }

        return 0.0;
    }

    /**
     * @return string[]
     */
    private static function apiQueryCandidates(string $token): array
    {
        $map = [
            'tomate' => ['tomato'],
            'tomates' => ['tomato'],
            'oignon' => ['onion'],
            'oignons' => ['onion'],
            'ail' => ['garlic'],
            'carotte' => ['carrot'],
            'carottes' => ['carrot'],
            'courgette' => ['zucchini'],
            'courgettes' => ['zucchini'],
            'poivron' => ['pepper'],
            'poivrons' => ['pepper'],
            'pomme' => ['potato', 'apple'],
            'pommes' => ['potato', 'apple'],
            'pomme-de-terre' => ['potato'],
            'pommes-de-terre' => ['potato'],
            'concombre' => ['cucumber'],
            'concombres' => ['cucumber'],
            'salade' => ['lettuce'],
            'epinard' => ['spinach'],
            'epinards' => ['spinach'],
            'épinard' => ['spinach'],
            'épinards' => ['spinach'],
            'brocoli' => ['broccoli'],
            'haricot' => ['beans'],
            'haricots' => ['beans'],
            'champignon' => ['mushroom'],
            'champignons' => ['mushroom'],
            'citron' => ['lemon'],
            'citrons' => ['lemon'],
            'soupe' => ['soup', 'vegetable soup'],
        ];

        if (isset($map[$token])) {
            return array_merge([$token], $map[$token], ['vegetable', 'groceries']);
        }
        return [$token, 'vegetable', 'groceries'];
    }

    private static function fallbackPrice(string $token): float
    {
        $map = [
            'tomate' => 1.20,
            'tomates' => 1.20,
            'oignon' => 0.90,
            'ail' => 0.60,
            'poulet' => 9.50,
            'boeuf' => 18.00,
            'riz' => 3.20,
            'pates' => 2.40,
            'pâtes' => 2.40,
            'huile' => 4.80,
            'lait' => 1.90,
            'oeuf' => 0.70,
            'oeufs' => 0.70,
            'fromage' => 6.50,
            'thon' => 5.80,
            'citron' => 1.10,
        ];

        return $map[$token] ?? 2.50;
    }
}

