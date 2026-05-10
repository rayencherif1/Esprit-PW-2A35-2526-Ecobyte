<?php
require_once __DIR__ . '/../controller/RecetteController.php';
require_once __DIR__ . '/../controller/InstructionController.php';
require_once __DIR__ . '/../lib/IngredientPriceService.php';

function normalizeTypeForBadge(string $type): string
{
    if (function_exists('mb_strtolower')) {
        $value = trim(mb_strtolower($type, 'UTF-8'));
    } else {
        $value = trim(strtolower($type));
    }
    $value = strtr($value, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ù' => 'u', 'û' => 'u']);
    return preg_replace('/\s+/', ' ', $value) ?? $value;
}

/** Icône affichée à côté du type (comme sur la maquette). */
function typeBadgeEmoji(string $type): string
{
    $n = normalizeTypeForBadge($type);
    if ($n === 'petit dejeuner') {
        return '☀';
    }
    if ($n === 'dejeuner') {
        return '☀';
    }
    if ($n === 'diner') {
        return '🌙';
    }
    return '🍽';
}

/**
 * Calcul d'empreinte carbone très simplifié à partir du texte des ingrédients.
 * On retourne une chaîne du type "≈ 2.3 kg CO₂e" ou "Non calculé" si impossible.
 */
function computeCarboneFromIngredients(?string $ingredientsText): string
{
    if ($ingredientsText === null) {
        return 'Non calculé';
    }
    $txt = trim($ingredientsText);
    if ($txt === '' || isInstructionPlaceholderText($txt)) {
        return 'Non calculé';
    }

    $lower = mb_strtolower($txt, 'UTF-8');
    $map = [
        'boeuf' => 27.0,
        'steak' => 25.0,
        'agneau' => 24.0,
        'veau' => 22.0,
        'porc' => 12.0,
        'poulet' => 6.0,
        'dinde' => 6.0,
        'poisson' => 5.0,
        'saumon' => 8.0,
        'thon' => 8.0,
        'fromage' => 13.0,
        'lait' => 3.0,
        'oeuf' => 4.5,
        'oeufs' => 4.5,
        'riz' => 4.0,
        'pates' => 2.5,
        'pâtes' => 2.5,
        'pain' => 1.0,
        'tomate' => 1.4,
        'tomates' => 1.4,
        'pomme de terre' => 0.5,
        'pommes de terre' => 0.5,
        'legume' => 0.8,
        'legumes' => 0.8,
        'légume' => 0.8,
        'légumes' => 0.8,
        'fruit' => 0.8,
        'fruits' => 0.8,
        'huile' => 3.0,
    ];

    $totalKg = 0.0;
    foreach ($map as $needle => $kgCo2e) {
        if (mb_strpos($lower, $needle, 0, 'UTF-8') !== false) {
            $totalKg += $kgCo2e;
        }
    }

    if ($totalKg <= 0.0) {
        return 'Non calculé';
    }

    $rounded = round($totalKg, 1);
    return '≈ ' . number_format($rounded, 1, ',', ' ') . ' kg CO₂e';
}

/**
 * Appel HTTP JSON simple (API externe) avec timeout court.
 *
 * @return array<string, mixed>|null
 */
function fetchJsonFromUrl(string $url): ?array
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 2,
            'header' => "User-Agent: recette-app/1.0\r\nAccept: application/json\r\n",
        ],
    ]);
    $raw = @file_get_contents($url, false, $context);
    if ($raw === false || trim($raw) === '') {
        return null;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

/**
 * Trial API: estimation via OpenFoodFacts (ecoscore).
 * Si l'API ne renvoie rien, la fonction retourne null.
 */
function fetchCarboneFromOpenFoodFacts(string $ingredientsText): ?string
{
    if (!function_exists('mb_strtolower')) {
        return null;
    }
    $tokens = preg_split('/[\s,;|•·\r\n]+/u', mb_strtolower($ingredientsText, 'UTF-8')) ?: [];
    $tokens = array_values(array_filter(array_map(
        static fn (string $t): string => trim(preg_replace('/[^a-z0-9àâäéèêëïîôùûüç-]/ui', '', $t) ?? ''),
        $tokens
    ), static fn (string $t): bool => mb_strlen($t, 'UTF-8') >= 3));
    $tokens = array_slice(array_unique($tokens), 0, 4);
    if (count($tokens) === 0) {
        return null;
    }

    $gradeToKg = ['a' => 0.8, 'b' => 1.6, 'c' => 3.0, 'd' => 5.0, 'e' => 8.0];
    $sum = 0.0;
    $hits = 0;
    foreach ($tokens as $token) {
        $url = 'https://world.openfoodfacts.org/cgi/search.pl?search_terms=' . rawurlencode($token) . '&search_simple=1&json=1&page_size=1';
        $data = fetchJsonFromUrl($url);
        if ($data === null || !isset($data['products'][0]) || !is_array($data['products'][0])) {
            continue;
        }
        $product = $data['products'][0];
        $grade = '';
        if (isset($product['ecoscore_grade']) && is_string($product['ecoscore_grade'])) {
            $grade = strtolower(trim($product['ecoscore_grade']));
        }
        if ($grade !== '' && isset($gradeToKg[$grade])) {
            $sum += $gradeToKg[$grade];
            $hits++;
        }
    }

    if ($hits === 0) {
        return null;
    }
    $avg = $sum / $hits;
    return '≈ ' . number_format($avg, 1, ',', ' ') . ' kg CO₂e';
}

/**
 * Affichage difficulté dans la barre de stats : étoiles ou texte.
 */
function renderDifficultyStatsHtml(string $difficulteRaw): string
{
    $raw = trim($difficulteRaw);
    if ($raw === '') {
        return '<span class="ri-stat-strong">—</span>';
    }
    $filled = substr_count($raw, '★') + substr_count($raw, '⭐');
    if ($filled === 0 && preg_match('/^[1-5]$/', $raw) === 1) {
        $filled = (int) $raw;
    }
    if ($filled > 0) {
        $filled = max(1, min(5, $filled));
        $html = '<span class="ri-stars" aria-hidden="true">';
        for ($i = 1; $i <= 5; $i++) {
            $on = $i <= $filled ? ' ri-star--on' : '';
            $html .= '<span class="ri-star' . $on . '">★</span>';
        }
        $html .= '</span>';
        return $html;
    }
    return '<span class="ri-stat-strong">' . htmlspecialchars($raw, ENT_QUOTES, 'UTF-8') . '</span>';
}

function isInstructionPlaceholderText(string $text): bool
{
    $value = trim($text);
    if ($value === '') {
        return true;
    }
    if (str_starts_with($value, 'À compléter')) {
        return true;
    }
    return $value === '1-' || $value === '1- ';
}

$recetteId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$recetteCtl = new RecetteController();
$instructionCtl = new InstructionController();

$recette = $recetteId > 0 ? $recetteCtl->getRecetteById($recetteId) : null;
$instruction = null;

if ($recette !== null) {
    $instruction = $instructionCtl->getByRecetteId($recetteId);
    if ($instruction === null) {
        $instructionCtl->syncFromRecette($recette);
        $instruction = $instructionCtl->getByRecetteId($recetteId);
    }
}

$templatePath = __DIR__ . '/../assets/FoodMart-1.0.0/index.html';
$template = file_get_contents($templatePath);
if ($template === false) {
    http_response_code(500);
    echo 'Impossible de charger le template FoodMart.';
    exit;
}

if ($recette === null) {
    http_response_code(404);
    $docTitle = 'FoodMart - Recette introuvable';
    $instructionsMain = '
<main id="recette-instructions">
  <section class="py-5" id="accessories">
    <div class="container-fluid">
      <div class="section-header d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h1 class="section-title">Recette introuvable</h1>
        <a href="/recette/view/front.php" class="nav-link text-decoration-none">← Mes recettes</a>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-4">
          <div class="product-item h-100">
            <figure>
              <a href="/recette/view/front.php" title="Retour">
                <img src="/recette/public/image/salade.jpg" class="tab-image" alt="">
              </a>
            </figure>
            <h3>Cette recette n\'existe pas</h3>
            <span class="qty">Elle a peut-être été supprimée.</span>
            <span class="price"> </span>
            <div class="d-flex align-items-center justify-content-between">
              <small></small>
              <a href="/recette/view/front.php" class="nav-link text-decoration-none">Mes recettes</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>';
} else {
    $type = (string) ($recette['type'] ?? '');
    $labelType = htmlspecialchars($type !== '' ? $type : 'Recette', ENT_QUOTES, 'UTF-8');
    $nomPlain = (string) ($recette['nom'] ?? '');
    $nom = htmlspecialchars($nomPlain, ENT_QUOTES, 'UTF-8');
    $img = htmlspecialchars($recette['image'] ?? '/recette/public/image/salade.jpg', ENT_QUOTES, 'UTF-8');
    $calories = htmlspecialchars((string) ($recette['calories'] ?? '0'), ENT_QUOTES, 'UTF-8');
    $temps = htmlspecialchars((string) ($recette['tempsPreparation'] ?? '0'), ENT_QUOTES, 'UTF-8');
    $difficulte = htmlspecialchars($recette['difficulte'] ?? '', ENT_QUOTES, 'UTF-8');
    $impactCarboneCalc = 'Non calculé';
    $vegetablesPriceApi = 'Non trouvé API';

    $ingredientsBody = '';
    $preparationBody = '';
    $metaEtapesBlock = '';
    $ne = 0;
    $tm = 0;

    if ($instruction) {
        $rawIngredients = (string) ($instruction['ingredients'] ?? '');
        $rawPreparation = (string) ($instruction['preparation'] ?? '');
        $ingredientsReady = !isInstructionPlaceholderText($rawIngredients);
        $preparationReady = !isInstructionPlaceholderText($rawPreparation);

        $ingredientsBody = $ingredientsReady
            ? '<div class="recipe-instructions-prose ri-prose">' . nl2br(htmlspecialchars($rawIngredients, ENT_QUOTES, 'UTF-8')) . '</div>'
            : '<div class="ri-info-banner" role="status"><span class="ri-info-icon" aria-hidden="true">i</span><span>À compléter : liste les ingrédients dans la fiche Instructions.</span></div>';

        $preparationBody = $preparationReady
            ? '<div class="recipe-instructions-prose ri-prose">' . nl2br(htmlspecialchars($rawPreparation, ENT_QUOTES, 'UTF-8')) . '</div>'
            : '<div class="ri-info-banner" role="status"><span class="ri-info-icon" aria-hidden="true">i</span><span>À compléter : décris les étapes dans la fiche Instructions (liée à cette recette).</span></div>';

        $ne = (int) ($instruction['nombreEtapes'] ?? 0);
        $tm = (int) ($instruction['temps'] ?? 0);
        $impactCarboneCalc = computeCarboneFromIngredients($rawIngredients);
        $impactFromApi = fetchCarboneFromOpenFoodFacts($rawIngredients);
        if ($impactFromApi !== null) {
            $impactCarboneCalc = $impactFromApi;
        }
        $vegetablesPriceApi = IngredientPriceService::formatApiOnlyPrice(
            IngredientPriceService::estimateVegetablesPriceApiOnly($rawIngredients)
        );
        if ($ne > 0 || $tm > 0) {
            $metaLine = $ne . ' étapes • ' . $tm . ' min (fiche)';
            $metaUpper = function_exists('mb_strtoupper') ? mb_strtoupper($metaLine, 'UTF-8') : strtoupper($metaLine);
            $metaEtapesBlock = '<div class="ri-meta-footer">' . htmlspecialchars($metaUpper, ENT_QUOTES, 'UTF-8') . '</div>';
        }
    } else {
        $ingredientsBody = '<div class="ri-info-banner" role="status"><span class="ri-info-icon" aria-hidden="true">i</span><span>Aucune fiche instruction pour le moment.</span></div>';
        $preparationBody = $ingredientsBody;
    }

    $typeEmoji = htmlspecialchars(typeBadgeEmoji($type), ENT_QUOTES, 'UTF-8');
    $difficultyStatsHtml = renderDifficultyStatsHtml((string) ($recette['difficulte'] ?? ''));
    $cardMetaLine = 'TEMPS: ' . $temps . ' MIN | DIFFICULTÉ: ' . $difficulte;

    $instructionsMain = '
<main id="recette-instructions">
  <section class="py-4 pb-5 ri-instructions-wrap" id="accessories">
    <div class="container-fluid px-lg-5">
      <header class="ri-page-header d-flex flex-wrap justify-content-between align-items-center">
        <h1 class="ri-page-title mb-0">Instructions</h1>
        <a href="/recette/view/front.php" class="ri-back-link text-decoration-none">← Mes recettes</a>
      </header>

      <section class="recette-instructions-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
          <div>
            <h2 class="ri-hero-title mb-2">' . $nom . '</h2>
            <span class="ri-type-pill"><span class="ri-type-pill__ico" aria-hidden="true">' . $typeEmoji . '</span> ' . $labelType . '</span>
          </div>
        </div>

        <div class="ri-stats-bar mb-4">
          <div class="ri-stat">
            <span class="ri-stat-ico" aria-hidden="true">🕐</span>
            <div class="ri-stat-text">
              <span class="ri-stat-label">Temps</span>
              <strong class="ri-stat-strong">' . $temps . ' min</strong>
            </div>
          </div>
          <div class="ri-stat-divider" aria-hidden="true"></div>
          <div class="ri-stat ri-stat--diff">
            <span class="ri-stat-ico" aria-hidden="true">★</span>
            <div class="ri-stat-text">
              <span class="ri-stat-label">Difficulté</span>
              <div class="ri-stat-diff">' . $difficultyStatsHtml . '</div>
            </div>
          </div>
          <div class="ri-stat-divider" aria-hidden="true"></div>
          <div class="ri-stat">
            <span class="ri-stat-ico" aria-hidden="true">🔥</span>
            <div class="ri-stat-text">
              <span class="ri-stat-label">Calories</span>
              <strong class="ri-stat-strong">' . $calories . ' Kcal</strong>
            </div>
          </div>
          <div class="ri-stat-divider" aria-hidden="true"></div>
          <div class="ri-stat">
            <span class="ri-stat-ico" aria-hidden="true">🌍</span>
            <div class="ri-stat-text">
              <span class="ri-stat-label">Empreinte estimée</span>
              <strong class="ri-stat-strong">' . htmlspecialchars($impactCarboneCalc, ENT_QUOTES, 'UTF-8') . '</strong>
            </div>
          </div>
          <div class="ri-stat-divider" aria-hidden="true"></div>
          <div class="ri-stat">
            <span class="ri-stat-ico" aria-hidden="true">💰</span>
            <div class="ri-stat-text">
              <span class="ri-stat-label">Prix légumes</span>
              <strong class="ri-stat-strong">' . htmlspecialchars($vegetablesPriceApi, ENT_QUOTES, 'UTF-8') . '</strong>
            </div>
          </div>
        </div>

        <div class="row g-4 align-items-start">
          <div class="col-md-6 col-lg-4">
            <div class="ri-recipe-card product-item recipe-instructions-card">
              <a href="#" class="btn-wishlist" title="Recette"><svg width="24" height="24"><use xlink:href="#heart"></use></svg></a>
              <div class="ri-card-img-wrap">
                <figure class="mb-0">
                  <img src="' . $img . '" class="tab-image" alt="' . $nom . '">
                </figure>
              </div>
              <div class="ri-card-body">
                <h3 class="ri-card-title">' . $nom . '</h3>
                <p class="ri-card-meta">' . $cardMetaLine . '</p>
                <p class="ri-card-kcal">' . $calories . ' Kcal</p>
                <div class="ri-card-foot d-flex align-items-center justify-content-between">
                  <small>Empreinte: ' . htmlspecialchars($impactCarboneCalc, ENT_QUOTES, 'UTF-8') . ' | Prix légumes: ' . htmlspecialchars($vegetablesPriceApi, ENT_QUOTES, 'UTF-8') . '</small>
                  <span class="ri-fiche-pill"><span aria-hidden="true">📄</span> Fiche</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-8">
            <h3 class="ri-section-head"><span class="ri-section-emoji" aria-hidden="true">🥗</span> Ingrédients</h3>
            <div class="ri-content-box recipe-instructions-text mb-0">
              ' . $ingredientsBody . '
            </div>
          </div>
        </div>

        <div class="row g-4 align-items-start mt-2 mt-lg-3">
          <div class="col-12">
            <h3 class="ri-section-head mt-2"><span class="ri-section-emoji" aria-hidden="true">👨‍🍳</span> Préparation</h3>
            <div class="ri-content-box recipe-instructions-text recipe-instructions-prep">
              ' . $preparationBody . '
              ' . $metaEtapesBlock . '
            </div>
          </div>
        </div>
      </section>
    </div>
  </section>
</main>';
    $docTitle = 'FoodMart - Instructions — ' . $nomPlain;
}

$customFooter = '
<footer class="py-5" id="about-us" style="background-color: #f7c948;">
  <div class="container">
       <div class="text-center mb-4 pb-3 border-bottom border-dark border-opacity-25">
      <h2 class="section-title mb-0 text-dark fw-bold">About Us</h2>
    </div>
    <div class="row g-4 text-dark">
      <div class="col-md-3">
        <h4 class="fw-bold mb-3">Address</h4>
        <p class="mb-0 opacity-75">
          It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.
        </p>
      </div>
      <div class="col-md-3">
        <h4 class="fw-bold mb-3">Links</h4>
        <ul class="list-unstyled mb-0">
          <li class="mb-2">Home</li>
          <li class="mb-2">About</li>
          <li class="mb-2">Car</li>
          <li class="mb-2">Booking</li>
          <li>Contact Us</li>
        </ul>
      </div>
      <div class="col-md-3">
        <h4 class="fw-bold mb-3">Follow Us</h4>
        <ul class="list-unstyled mb-0">
          <li class="mb-2">Facebook</li>
          <li class="mb-2">Twitter</li>
          <li class="mb-2">Linkedin</li>
          <li class="mb-2">Youtube</li>
          <li>Instagram</li>
        </ul>
      </div>
      <div class="col-md-3">
        <h4 class="fw-bold mb-3">Newsletter</h4>
        <input type="email" class="form-control mb-3" placeholder="Enter Your Email">
        <button class="btn text-white px-4" style="background-color: #d23c78;">SUBSCRIBE</button>
      </div>
    </div>
  </div>
</footer>';

$template = preg_replace('~</header>~i', '</header>' . $instructionsMain, $template, 1) ?? $template;
$template = preg_replace('~</main>\s*.*?<footer~is', '</main><footer', $template, 1) ?? $template;
$template = preg_replace('~<footer[^>]*>.*?</footer>~is', $customFooter, $template, 1) ?? $template;
$template = str_replace('<html>', '<html lang="fr">', $template);
$recetteInstructionsCss = '<style id="recette-instructions-layout">
#recette-instructions{background:#fafefa;min-height:40vh}
#recette-instructions .ri-instructions-wrap{background:#fafefa}
#recette-instructions section#accessories{overflow:visible;padding-bottom:3rem}
#recette-instructions .ri-page-header{border-bottom:1px solid #d8f0e0;padding-bottom:1rem;margin-bottom:1.5rem}
#recette-instructions .ri-page-title{font-size:1.75rem;font-weight:800;color:#14532d;letter-spacing:-0.02em}
#recette-instructions .ri-back-link{font-weight:600;color:#166534}
#recette-instructions .ri-back-link:hover{color:#15803d}
#recette-instructions .ri-hero-title{font-size:1.85rem;font-weight:800;color:#14532d;letter-spacing:-0.02em}
#recette-instructions .ri-type-pill{display:inline-flex;align-items:center;gap:0.35rem;padding:0.35rem 0.85rem;border-radius:999px;background:#d1fae5;color:#065f46;font-size:0.9rem;font-weight:600}
#recette-instructions .ri-type-pill__ico{font-size:1rem;line-height:1}
#recette-instructions .ri-stats-bar{display:flex;flex-wrap:wrap;align-items:stretch;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:16px;padding:0.65rem 0;margin-bottom:0.5rem;box-shadow:0 2px 12px rgba(20,83,45,0.06)}
#recette-instructions .ri-stat{flex:1 1 120px;display:flex;align-items:center;gap:0.65rem;padding:0.5rem 0.85rem;min-width:0}
#recette-instructions .ri-stat--diff{flex:1.15 1 140px}
#recette-instructions .ri-stat-ico{font-size:1.25rem;line-height:1;opacity:0.9}
#recette-instructions .ri-stat-text{min-width:0}
#recette-instructions .ri-stat-label{display:block;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;margin-bottom:0.15rem}
#recette-instructions .ri-stat-strong{font-size:1rem;font-weight:800;color:#14532d}
#recette-instructions .ri-stat-diff{font-size:0.95rem}
#recette-instructions .ri-stars{letter-spacing:0.05em;white-space:nowrap}
#recette-instructions .ri-star{color:#cbd5e1;font-size:1rem}
#recette-instructions .ri-star--on{color:#166534}
#recette-instructions .ri-stat-divider{width:1px;background:#bbf7d0;align-self:stretch;margin:0.35rem 0}
@media (max-width:767px){#recette-instructions .ri-stat-divider{display:none}}
#recette-instructions .ri-recipe-card{position:relative;border-radius:20px;overflow:hidden;border:1px solid #e2e8f0;background:#fff;box-shadow:0 8px 28px rgba(20,83,45,0.1);margin-bottom:0!important;height:auto!important}
#recette-instructions .ri-recipe-card .btn-wishlist{z-index:2}
#recette-instructions .ri-card-img-wrap .tab-image{width:100%;max-height:220px;object-fit:cover;display:block;border-radius:0}
#recette-instructions .ri-card-body{padding:1.1rem 1.15rem 1.25rem}
#recette-instructions .ri-card-title{font-size:1.15rem;font-weight:800;color:#14532d;margin-bottom:0.35rem}
#recette-instructions .ri-card-meta{font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;margin-bottom:0.35rem;line-height:1.35}
#recette-instructions .ri-card-kcal{font-size:1.35rem;font-weight:800;color:#14532d;margin-bottom:0.75rem}
#recette-instructions .ri-card-foot small{color:#64748b;font-size:0.8rem}
#recette-instructions .ri-fiche-pill{font-size:0.8rem;font-weight:600;color:#166534;display:inline-flex;align-items:center;gap:0.25rem}
#recette-instructions .ri-section-head{display:flex;align-items:center;gap:0.5rem;font-size:1.1rem;font-weight:800;color:#14532d;margin-bottom:0.65rem}
#recette-instructions .ri-section-emoji{font-size:1.2rem;line-height:1}
#recette-instructions .ri-content-box{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:1.15rem 1.25rem;box-shadow:0 2px 14px rgba(15,23,42,0.04);height:auto!important;min-height:0!important;max-height:none!important;margin-bottom:0!important}
#recette-instructions .ri-content-box.recipe-instructions-prep{margin-bottom:3rem!important}
#recette-instructions .ri-info-banner{display:flex;gap:0.65rem;align-items:flex-start;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:12px;padding:0.85rem 1rem;color:#065f46;font-size:0.95rem;line-height:1.5}
#recette-instructions .ri-info-icon{flex-shrink:0;width:22px;height:22px;border-radius:50%;background:#6ee7b7;color:#064e3b;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:0.7rem;line-height:1}
#recette-instructions .ri-prose{white-space:pre-wrap;word-break:break-word;line-height:1.65;color:#1e293b;margin:0}
#recette-instructions .ri-meta-footer{margin-top:1rem;padding-top:1rem;border-top:1px solid #e2e8f0;font-size:0.72rem;font-weight:800;letter-spacing:0.07em;color:#64748b}
#recette-instructions .recipe-instructions-card .product-item{border:none!important;box-shadow:none!important}
#recette-instructions .row.align-items-start{align-items:flex-start!important}
</style>';
$template = preg_replace('~</head>~i', $recetteInstructionsCss . '</head>', $template, 1) ?? $template;
$template = str_replace('<title>Foodmart - Free eCommerce Grocery Store HTML Website Template</title>', '<title>' . htmlspecialchars($docTitle, ENT_QUOTES, 'UTF-8') . '</title>', $template);
$template = str_replace('href="css/', 'href="/recette/assets/FoodMart-1.0.0/css/', $template);
$template = str_replace('href="style.css"', 'href="/recette/assets/FoodMart-1.0.0/style.css"', $template);
$template = str_replace('src="images/', 'src="/recette/assets/FoodMart-1.0.0/images/', $template);
$template = str_replace('url(\'images/', 'url(\'/recette/assets/FoodMart-1.0.0/images/', $template);
$template = str_replace('/recette/assets/FoodMart-1.0.0/images/logo.png', '/recette/public/image/logo.png', $template);
$template = str_replace('src="js/', 'src="/recette/assets/FoodMart-1.0.0/js/', $template);
$template = str_replace('href="index.html"', 'href="/recette/"', $template);

echo $template;
