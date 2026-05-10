<?php
// controller/ApiController.php
require_once __DIR__ . '/../model/Produit.php';

class ApiController {
    private $db;
    private string $openFoodFactsBase = 'https://world.openfoodfacts.org';
    private string $simulatedNutriScoreServiceUrl = 'http://localhost/marketplace/external/nutriscore_service.php';

    public function __construct() {
        $produit = new Produit();
        $this->db = $produit->getDb();
    }

    private function ensureSessionStarted(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function requireAdminAuth(): void {
        $this->ensureSessionStarted();
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $this->json(['error' => 'UNAUTHORIZED'], 401);
        }
    }

    private function json(array $payload, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit();
    }

    private function getProduitNutritionById(int $id): ?array {
        $sql = "SELECT id, nom, description, calories, nutriscore FROM produits WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function listProduitsNutrition(int $limit = 50): array {
        $limit = max(1, min(200, $limit));
        $sql = "SELECT id, nom, calories, nutriscore FROM produits ORDER BY id DESC LIMIT $limit";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    private function normalizeNutriScore(?string $value): ?string {
        if ($value === null) return null;
        $value = strtoupper(trim($value));
        if ($value === '') return null;
        if (!preg_match('/^[A-E]$/', $value)) return null;
        return $value;
    }

    private function extractIngredientsFromDescription(?string $description): ?string {
        if ($description === null) return null;
        $text = trim($description);
        if ($text === '') return null;

        // Heuristique: si on trouve "ingr" on essaie d'extraire la partie ingrédients.
        // Sinon on renvoie null pour ne pas polluer la recherche OpenFoodFacts.
        if (!preg_match('/ingr(e|é)d/i', $text)) {
            return null;
        }

        // Capture après "Ingrédients:" jusqu'à fin ou point.
        if (preg_match('/ingr(?:e|é)dients?\s*:\s*([^\\n\\r\\.]{10,500})/iu', $text, $m)) {
            $ing = trim($m[1]);
            $ing = preg_replace('/\s+/', ' ', $ing);
            return $ing !== '' ? $ing : null;
        }

        // Fallback: renvoyer une version nettoyée du texte si court.
        $text = preg_replace('/\s+/', ' ', $text);
        if (mb_strlen($text) > 250) {
            $text = mb_substr($text, 0, 250);
        }
        return $text !== '' ? $text : null;
    }

    private function httpGetJson(string $url, int $timeoutSeconds = 6): ?array {
        // cURL (préféré)
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
                CURLOPT_TIMEOUT => $timeoutSeconds,
                CURLOPT_USERAGENT => 'EcoBite/1.0 (NutriScore API)'
            ]);
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($body === false || $code < 200 || $code >= 300) return null;
            $json = json_decode($body, true);
            return is_array($json) ? $json : null;
        }

        // Fallback file_get_contents
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeoutSeconds,
                'header' => "User-Agent: EcoBite/1.0 (NutriScore API)\r\n"
            ]
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) return null;
        $json = json_decode($body, true);
        return is_array($json) ? $json : null;
    }

    private function httpPostJson(string $url, array $payload, int $timeoutSeconds = 6): ?array {
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($jsonPayload === false) return null;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
                CURLOPT_TIMEOUT => $timeoutSeconds,
                CURLOPT_USERAGENT => 'EcoBite/1.0 (NutriScore API)',
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json; charset=utf-8'
                ],
                CURLOPT_POSTFIELDS => $jsonPayload
            ]);
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($body === false || $code < 200 || $code >= 300) return null;
            $json = json_decode($body, true);
            return is_array($json) ? $json : null;
        }

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => $timeoutSeconds,
                'header' => "User-Agent: EcoBite/1.0 (NutriScore API)\r\nContent-Type: application/json; charset=utf-8\r\n",
                'content' => $jsonPayload
            ]
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) return null;
        $json = json_decode($body, true);
        return is_array($json) ? $json : null;
    }

    private function fetchNutriScoreFromSimulatedService(string $productName, ?string $ingredientsHint): ?array {
        $payload = [
            'name' => $productName,
            'ingredients' => $ingredientsHint ?? ''
        ];
        $data = $this->httpPostJson($this->simulatedNutriScoreServiceUrl, $payload, 4);
        if (!$data || !is_array($data)) return null;
        $grade = $data['nutriscore'] ?? null;
        if (!is_string($grade)) return [
            'nutriscore' => null,
            'reason' => $data['reason'] ?? 'unknown'
        ];
        $grade = strtoupper(trim($grade));
        if (!preg_match('/^[A-E]$/', $grade)) return null;
        return [
            'nutriscore' => $grade,
            'simulated' => [
                'service' => $data['service'] ?? 'simulated',
                'score' => $data['score'] ?? null,
                'reason' => $data['reason'] ?? null
            ]
        ];
    }

    private function fetchNutriScoreFromOpenFoodFacts(string $productName, ?string $ingredientsHint = null): ?array {
        $name = trim($productName);
        if ($name === '') return null;

        // 1) Rechercher par nom uniquement (plus de résultats)
        $query = http_build_query([
            'search_terms' => $name,
            'search_simple' => 1,
            'action' => 'process',
            'json' => 1,
            'page_size' => 30,
            'page' => 1
        ]);
        $url = $this->openFoodFactsBase . '/cgi/search.pl?' . $query;
        $data = $this->httpGetJson($url);
        if (!$data || !isset($data['products']) || !is_array($data['products'])) return null;

        // 2) Scorer les candidats: similarité du nom + recouvrement ingrédients
        $ingredientsTokens = [];
        if ($ingredientsHint !== null) {
            $hint = mb_strtolower($ingredientsHint);
            $hint = preg_replace('/[^\\p{L}\\p{N}\\s,]/u', ' ', $hint);
            $parts = preg_split('/[\\s,]+/u', $hint, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $w) {
                $w = trim($w);
                if (mb_strlen($w) < 3) continue;
                $ingredientsTokens[$w] = true;
            }
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($data['products'] as $p) {
            $grade = $p['nutriscore_grade'] ?? null;
            if (!is_string($grade)) continue;
            $grade = strtoupper(trim($grade));
            if (!preg_match('/^[A-E]$/', $grade)) continue;

            $offName = (string)($p['product_name'] ?? '');
            $offNameNorm = mb_strtolower(trim($offName));
            if ($offNameNorm === '') continue;

            $nameSim = 0.0;
            similar_text(mb_strtolower($name), $offNameNorm, $nameSim); // 0..100
            $nameSim = $nameSim / 100.0;

            $ingScore = 0.0;
            if (!empty($ingredientsTokens)) {
                $offIng = (string)($p['ingredients_text'] ?? '');
                $offIng = mb_strtolower($offIng);
                if ($offIng !== '') {
                    $hits = 0;
                    $total = count($ingredientsTokens);
                    foreach ($ingredientsTokens as $tok => $_) {
                        if (mb_strpos($offIng, $tok) !== false) $hits++;
                    }
                    $ingScore = $total > 0 ? ($hits / $total) : 0.0; // 0..1
                }
            }

            // Pondération: le nom d'abord, ingrédients en bonus
            $score = ($nameSim * 0.75) + ($ingScore * 0.25);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $p;
            }
        }

        // Seuil: éviter un match totalement hors-sujet
        if (!$best || $bestScore < 0.35) {
            return null;
        }

        $grade = strtoupper(trim((string)($best['nutriscore_grade'] ?? '')));
        if (!preg_match('/^[A-E]$/', $grade)) return null;

        return [
            'nutriscore' => $grade,
            'match_score' => round($bestScore, 3),
            'openfoodfacts' => [
                'code' => $best['code'] ?? null,
                'product_name' => $best['product_name'] ?? null,
                'brands' => $best['brands'] ?? null,
                'ingredients_text' => $best['ingredients_text'] ?? null,
                'url' => isset($best['code']) ? ($this->openFoodFactsBase . '/product/' . $best['code']) : null
            ]
        ];
    }

    /**
     * GET  /marketplace/index.php?controller=api&action=nutriscore&id=123
     * GET  /marketplace/index.php?controller=api&action=nutriscore&id=123&force=1 (recalcule via OpenFoodFacts)
     */
    public function nutriscore(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'GET') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) {
                $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
                $rows = $this->listProduitsNutrition($limit);
                $items = [];
                foreach ($rows as $p) {
                    $stored = $this->normalizeNutriScore($p['nutriscore'] ?? null);
                    $items[] = [
                        'id' => intval($p['id']),
                        'nom' => $p['nom'] ?? null,
                        'nutriscore' => $stored,
                        'source' => $stored ? 'stored' : 'missing'
                    ];
                }

                $this->json([
                    'help' => 'Use ?id=PRODUCT_ID to compute/fetch Nutri-Score from OpenFoodFacts (and cache in DB).',
                    'endpoint' => '/marketplace/index.php?controller=api&action=nutriscore',
                    'items' => $items
                ]);
            }

            $produit = $this->getProduitNutritionById($id);
            if (!$produit) {
                $this->json(['error' => 'NOT_FOUND', 'message' => 'Produit introuvable pour id=' . $id], 404);
            }

            $stored = $this->normalizeNutriScore($produit['nutriscore'] ?? null);
            $force = isset($_GET['force']) ? intval($_GET['force']) === 1 : false;

            if ($stored && !$force) {
                $this->json([
                    'id' => intval($produit['id']),
                    'nom' => $produit['nom'] ?? null,
                    'nutriscore' => $stored,
                    'source' => 'stored'
                ]);
            }

            $ingredientsHint = $this->extractIngredientsFromDescription($produit['description'] ?? null);
            $result = $this->fetchNutriScoreFromOpenFoodFacts($produit['nom'] ?? '', $ingredientsHint);
            $source = 'openfoodfacts';

            // Fallback: service externe simulé (basé ingrédients) si OFF ne matche pas
            if (!$result) {
                $sim = $this->fetchNutriScoreFromSimulatedService($produit['nom'] ?? '', $ingredientsHint);
                if ($sim && ($sim['nutriscore'] ?? null)) {
                    $result = $sim;
                    $source = 'simulated_service';
                } else {
                    $this->json([
                        'error' => 'NOT_FOUND',
                        'message' => "Aucun Nutri‑Score trouvé. OpenFoodFacts n'a pas matché et les ingrédients sont insuffisants pour l'estimation. Ajoute une ligne 'Ingrédients: ...' (au moins 3 ingrédients).",
                        'id' => intval($produit['id']),
                        'nom' => $produit['nom'] ?? null
                    ], 404);
                }
            }

            $nutriscore = $this->normalizeNutriScore($result['nutriscore'] ?? null);
            if ($nutriscore === null) {
                $this->json(['error' => 'UPSTREAM_ERROR', 'message' => 'Nutri-Score invalide reçu'], 502);
            }

            // Cache en base
            $sql = "UPDATE produits SET nutriscore = :nutriscore WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['nutriscore' => $nutriscore, 'id' => $id]);

            $this->json([
                'id' => intval($produit['id']),
                'nom' => $produit['nom'] ?? null,
                'nutriscore' => $nutriscore,
                'source' => $source,
                'openfoodfacts' => $result['openfoodfacts'] ?? null,
                'simulated' => $result['simulated'] ?? null,
                'match_score' => $result['match_score'] ?? null
            ]);
        }

        $this->json(['error' => 'METHOD_NOT_ALLOWED'], 405);
    }
}

?>
