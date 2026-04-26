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
        $recetteId = (int) ($recette['id'] ?? 0);
        $instructionsUrl = '/recette/view/recette-instructions.php?id=' . $recetteId;

        $cards .= '
        <div class="col-md-6 col-lg-4">
        <div class="product-item h-100">
          <span class="badge ' . $badgeClass . ' position-absolute m-3">' . htmlspecialchars($label) . '</span>
          <a href="#" class="btn-wishlist" title="Recette"><svg width="24" height="24"><use xlink:href="#heart"></use></svg></a>
          <figure>
            <a href="' . htmlspecialchars($instructionsUrl) . '" title="' . $nom . ' — voir les instructions">
              <img src="' . $image . '" class="tab-image" alt="' . $nom . '">
            </a>
          </figure>
          <h3><a href="' . htmlspecialchars($instructionsUrl) . '" class="text-decoration-none text-dark">' . $nom . '</a></h3>
          <span class="qty">Temps: ' . $temps . ' min | Difficulte: ' . $difficulte . '</span>
          <span class="price">' . $calories . ' kcal</span>
          <div class="d-flex align-items-center justify-content-between">
            <small>Impact carbone: ' . $impact . '</small>
            <a href="' . htmlspecialchars($instructionsUrl) . '" class="nav-link text-decoration-none fw-semibold" title="Voir les instructions" aria-label="Voir les instructions">→</a>
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

$replaceCount = 0;
$updatedTemplate = preg_replace('~<main[^>]*>.*?</main>~s', $recipesMain, $template, 1, $replaceCount);
if (is_string($updatedTemplate)) {
    $template = $updatedTemplate;
}
if ($replaceCount === 0) {
    $template = preg_replace('~</header>~i', '</header>' . $recipesMain, $template, 1) ?? $template;
}
$template = preg_replace('~</main>\s*.*?<footer~is', '</main><footer', $template, 1) ?? $template;
$template = preg_replace('~<footer[^>]*>.*?</footer>~is', $customFooter, $template, 1) ?? $template;
$template = str_replace('<html>', '<html lang="fr">', $template);
$template = str_replace('<title>Foodmart - Free eCommerce Grocery Store HTML Website Template</title>', '<title>FoodMart - Mes Recettes</title>', $template);
$template = str_replace('href="css/', 'href="/recette/assets/FoodMart-1.0.0/css/', $template);
$template = str_replace('href="style.css"', 'href="/recette/assets/FoodMart-1.0.0/style.css"', $template);
$template = str_replace('src="images/', 'src="/recette/assets/FoodMart-1.0.0/images/', $template);
$template = str_replace('url(\'images/', 'url(\'/recette/assets/FoodMart-1.0.0/images/', $template);
$template = str_replace('/recette/assets/FoodMart-1.0.0/images/logo.png', '/recette/public/image/logo.png', $template);
$template = str_replace('src="js/', 'src="/recette/assets/FoodMart-1.0.0/js/', $template);
$template = str_replace('href="index.html"', 'href="/recette/"', $template);

echo $template;
