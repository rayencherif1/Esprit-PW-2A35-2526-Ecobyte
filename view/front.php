<?php
require_once __DIR__ . '/../controller/RecetteController.php';

$controller = new RecetteController();
$recettes = $controller->afficherRecettes();
$templatePath = __DIR__ . '/../assets/FoodMart-1.0.0/index.html';
$template = file_get_contents($templatePath);

if ($template === false) {
    http_response_code(500);
    echo 'Impossible de charger le template FoodMart.';
    exit;
}

function normalizeType(string $type): string
{
    if (function_exists('mb_strtolower')) {
        $value = trim(mb_strtolower($type, 'UTF-8'));
    } else {
        $value = trim(strtolower($type));
    }
    $value = strtr($value, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ù' => 'u', 'û' => 'u']);
    return preg_replace('/\s+/', ' ', $value) ?? $value;
}

function renderCards(array $items, string $label, string $badgeClass): string
{
    if (count($items) === 0) {
        return '
        <div class="col-md-6 col-lg-4">
        <div class="product-item h-100">
          <figure>
            <a href="#" title="Recette">
              <img src="/recette/public/image/salade.jpg" class="tab-image" alt="Aucune recette">
            </a>
          </figure>
          <h3>Aucune recette</h3>
          <span class="qty">Type: ' . htmlspecialchars($label) . '</span>
          <span class="price">Aucune recette disponible</span>
        </div>
        </div>';
    }

    $cards = '';
    foreach ($items as $recette) {
        $nom = htmlspecialchars($recette['nom'] ?? '');
        $calories = htmlspecialchars((string) ($recette['calories'] ?? '0'));
        $temps = htmlspecialchars((string) ($recette['tempsPreparation'] ?? '0'));
        $difficulte = htmlspecialchars($recette['difficulte'] ?? '');
        $impact = htmlspecialchars($recette['impactCarbone'] ?? '');
        $image = htmlspecialchars($recette['image'] ?? '/recette/public/image/salade.jpg');

        $cards .= '
        <div class="col-md-6 col-lg-4">
        <div class="product-item h-100">
          <span class="badge ' . $badgeClass . ' position-absolute m-3">' . htmlspecialchars($label) . '</span>
          <a href="#" class="btn-wishlist" title="Recette"><svg width="24" height="24"><use xlink:href="#heart"></use></svg></a>
          <figure>
            <a href="#" title="' . $nom . '">
              <img src="' . $image . '" class="tab-image" alt="' . $nom . '">
            </a>
          </figure>
          <h3>' . $nom . '</h3>
          <span class="qty">Temps: ' . $temps . ' min | Difficulte: ' . $difficulte . '</span>
          <span class="price">' . $calories . ' kcal</span>
          <div class="d-flex align-items-center justify-content-between">
            <small>Impact carbone: ' . $impact . '</small>
            <span class="nav-link">Details</span>
          </div>
        </div>
        </div>';
    }

    return $cards;
}

$grouped = [
    'Petit dejeuner' => [],
    'Dejeuner' => [],
    'Diner' => [],
];

foreach ($recettes as $recette) {
    $normalized = normalizeType((string) ($recette['type'] ?? ''));
    if ($normalized === 'petit dejeuner') {
        $grouped['Petit dejeuner'][] = $recette;
    } elseif ($normalized === 'dejeuner') {
        $grouped['Dejeuner'][] = $recette;
    } elseif ($normalized === 'diner') {
        $grouped['Diner'][] = $recette;
    }
}

$petitDejeunerCards = renderCards($grouped['Petit dejeuner'], 'Petit dejeuner', 'bg-warning');
$dejeunerCards = renderCards($grouped['Dejeuner'], 'Dejeuner', 'bg-success');
$dinerCards = renderCards($grouped['Diner'], 'Diner', 'bg-primary');

$recipesMain = '
<main id="accessories">
  <section class="py-5" id="accessories">
    <div class="container-fluid">
      <div class="section-header d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h1 class="section-title">Mes Recettes</h1>
      </div>

      <section class="py-3">
        <div class="section-header mb-4">
          <h2 class="section-title">Petit dejeuner</h2>
        </div>
        <div class="row g-4">' . $petitDejeunerCards . '</div>
      </section>

      <section class="py-3">
        <div class="section-header mb-4">
          <h2 class="section-title">Dejeuner</h2>
        </div>
        <div class="row g-4">' . $dejeunerCards . '</div>
      </section>

      <section class="py-3">
        <div class="section-header mb-4">
          <h2 class="section-title">Diner</h2>
        </div>
        <div class="row g-4">' . $dinerCards . '</div>
      </section>
    </div>
  </section>
</main>';

$replaceCount = 0;
$updatedTemplate = preg_replace('~<main[^>]*>.*?</main>~s', $recipesMain, $template, 1, $replaceCount);
if (is_string($updatedTemplate)) {
    $template = $updatedTemplate;
}
if ($replaceCount === 0) {
    $template = preg_replace('~</header>~i', '</header>' . $recipesMain, $template, 1) ?? $template;
}
$template = str_replace('<html>', '<html lang="fr">', $template);
$template = str_replace('<title>Foodmart - Free eCommerce Grocery Store HTML Website Template</title>', '<title>FoodMart - Mes Recettes</title>', $template);
$template = str_replace('<a href="#accessories" class="nav-link">Accessories</a>', '<a href="/recette/view/front.php" class="nav-link">Mes Recettes</a>', $template);
$template = str_replace('<a href="#women" class="nav-link">Women</a>', '<a href="/recette/view/front.php" class="nav-link">Mes Recettes</a>', $template);
$template = str_replace('<a href="#men" class="nav-link">Men</a>', '<a href="/recette/view/front.php" class="nav-link">Mes Recettes</a>', $template);
$template = str_replace('<a href="#kids" class="nav-link">Kids</a>', '<a href="/recette/view/front.php" class="nav-link">Mes Recettes</a>', $template);
$template = str_replace('href="css/', 'href="/recette/assets/FoodMart-1.0.0/css/', $template);
$template = str_replace('href="style.css"', 'href="/recette/assets/FoodMart-1.0.0/style.css"', $template);
$template = str_replace('src="images/', 'src="/recette/assets/FoodMart-1.0.0/images/', $template);
$template = str_replace('url(\'images/', 'url(\'/recette/assets/FoodMart-1.0.0/images/', $template);
$template = str_replace('src="js/', 'src="/recette/assets/FoodMart-1.0.0/js/', $template);
$template = str_replace('href="index.html"', 'href="/recette/"', $template);

echo $template;
