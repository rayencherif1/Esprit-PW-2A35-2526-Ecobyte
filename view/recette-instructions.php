<?php
require_once __DIR__ . '/../controller/RecetteController.php';
require_once __DIR__ . '/../controller/InstructionController.php';

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

function badgeClassForType(string $type): string
{
    $n = normalizeTypeForBadge($type);
    if ($n === 'petit dejeuner') {
        return 'bg-warning';
    }
    if ($n === 'dejeuner') {
        return 'bg-success';
    }
    if ($n === 'diner') {
        return 'bg-primary';
    }
    return 'bg-secondary';
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
    $badgeClass = badgeClassForType($type);
    $labelType = htmlspecialchars($type !== '' ? $type : 'Recette', ENT_QUOTES, 'UTF-8');
    $nomPlain = (string) ($recette['nom'] ?? '');
    $nom = htmlspecialchars($nomPlain, ENT_QUOTES, 'UTF-8');
    $img = htmlspecialchars($recette['image'] ?? '/recette/public/image/salade.jpg', ENT_QUOTES, 'UTF-8');
    $calories = htmlspecialchars((string) ($recette['calories'] ?? '0'), ENT_QUOTES, 'UTF-8');
    $temps = htmlspecialchars((string) ($recette['tempsPreparation'] ?? '0'), ENT_QUOTES, 'UTF-8');
    $difficulte = htmlspecialchars($recette['difficulte'] ?? '', ENT_QUOTES, 'UTF-8');
    $impact = htmlspecialchars($recette['impactCarbone'] ?? '', ENT_QUOTES, 'UTF-8');

    $ingredientsHtml = '';
    $preparationHtml = '';
    $metaEtapes = '';

    if ($instruction) {
        $ingredientsHtml = nl2br(htmlspecialchars($instruction['ingredients'] ?? '', ENT_QUOTES, 'UTF-8'));
        $preparationHtml = nl2br(htmlspecialchars($instruction['preparation'] ?? '', ENT_QUOTES, 'UTF-8'));
        $ne = (int) ($instruction['nombreEtapes'] ?? 0);
        $tm = (int) ($instruction['temps'] ?? 0);
        $metaEtapes = '<span class="qty">' . $ne . ' étapes · ' . $tm . ' min (fiche)</span>';
    } else {
        $ingredientsHtml = '—';
        $preparationHtml = 'Aucune fiche instruction pour le moment.';
    }

    $instructionsMain = '
<main id="recette-instructions">
  <section class="py-5 pb-5" id="accessories">
    <div class="container-fluid px-lg-5">
      <div class="section-header d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h1 class="section-title">Instructions</h1>
        <a href="/recette/view/front.php" class="nav-link text-decoration-none">← Mes recettes</a>
      </div>

      <section class="py-2 recette-instructions-body">
        <div class="section-header mb-4">
          <h2 class="section-title">' . $nom . '</h2>
        </div>

        <div class="row g-4 align-items-start">
          <div class="col-md-6 col-lg-4">
            <div class="product-item recipe-instructions-card">
              <span class="badge ' . $badgeClass . ' position-absolute m-3">' . $labelType . '</span>
              <a href="#" class="btn-wishlist" title="Recette"><svg width="24" height="24"><use xlink:href="#heart"></use></svg></a>
              <figure>
                <a href="#" title="' . $nom . '">
                  <img src="' . $img . '" class="tab-image" alt="' . $nom . '">
                </a>
              </figure>
              <h3>' . $nom . '</h3>
              <span class="qty">Temps: ' . $temps . ' min | Difficulte: ' . $difficulte . '</span>
              <span class="price">' . $calories . ' kcal</span>
              <div class="d-flex align-items-center justify-content-between">
                <small>Impact carbone: ' . $impact . '</small>
                <span class="nav-link text-muted" style="pointer-events: none;">Fiche</span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-8">
            <div class="section-header mb-3">
              <h2 class="section-title h5 mb-0">Ingrédients</h2>
            </div>
            <div class="product-item recipe-instructions-text p-4 mb-0">
              <p class="mb-0 recipe-instructions-prose">' . $ingredientsHtml . '</p>
            </div>
          </div>
        </div>

        <div class="row g-4 align-items-start mt-2 mt-lg-1">
          <div class="col-12">
            <div class="section-header mb-3 mt-4">
              <h2 class="section-title h5 mb-0">Préparation</h2>
            </div>
            <div class="product-item recipe-instructions-text recipe-instructions-prep p-4">
              <p class="mb-0 recipe-instructions-prose">' . $preparationHtml . '</p>
              ' . ($metaEtapes !== '' ? '<div class="mt-4 pt-3 border-top border-secondary border-opacity-10">' . $metaEtapes . '</div>' : '') . '
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
#recette-instructions{background:#fff;min-height:40vh}
#recette-instructions section#accessories{overflow:visible;padding-bottom:3rem}
#recette-instructions .recipe-instructions-card{margin-bottom:0;height:auto!important}
#recette-instructions .recipe-instructions-text{height:auto!important;min-height:0!important;max-height:none!important}
#recette-instructions .recipe-instructions-text.product-item{margin-bottom:1.25rem!important}
#recette-instructions .recipe-instructions-prep{margin-bottom:4rem!important}
#recette-instructions .recipe-instructions-prose{white-space:pre-wrap;word-break:break-word;line-height:1.65}
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
