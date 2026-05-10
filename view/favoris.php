<?php
declare(strict_types=1);

require_once __DIR__ . '/../controller/RecetteController.php';

$controller = new RecetteController();
$recettes = $controller->afficherRecettes();
$allRecettesJson = json_encode($recettes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

$templatePath = __DIR__ . '/../assets/FoodMart-1.0.0/index.html';
$template = file_get_contents($templatePath);

if ($template === false) {
    http_response_code(500);
    echo 'Impossible de charger le template FoodMart.';
    exit;
}

$favorisMain = '
<main id="favoris-main">
  <section class="py-5" id="favoris-section">
    <div class="container-fluid">
      <div class="section-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-5">
        <h1 class="section-title mb-0">Mes favoris</h1>
        <a href="/recette/view/front.php" class="btn btn-outline-secondary rounded-pill">Toutes les recettes</a>
      </div>
      <p id="favoris-empty" class="text-center py-5 text-muted border rounded-3 bg-light" hidden>Aucune recette en favori pour le moment. Clique sur le cœur d&apos;une recette pour l&apos;ajouter ici.</p>
      <div class="row g-4" id="favoris-row"></div>
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
$updatedTemplate = preg_replace('~<main[^>]*>.*?</main>~s', $favorisMain, $template, 1, $replaceCount);
if (is_string($updatedTemplate)) {
    $template = $updatedTemplate;
}
if ($replaceCount === 0) {
    $template = preg_replace('~</header>~i', '</header>' . $favorisMain, $template, 1) ?? $template;
}
$template = preg_replace('~</main>\s*.*?<footer~is', '</main><footer', $template, 1) ?? $template;
$template = preg_replace('~<footer[^>]*>.*?</footer>~is', $customFooter, $template, 1) ?? $template;
$template = str_replace('<html>', '<html lang="fr">', $template);
$template = str_replace('<title>Foodmart - Free eCommerce Grocery Store HTML Website Template</title>', '<title>FoodMart - Mes favoris</title>', $template);
$template = str_replace('href="css/', 'href="/recette/assets/FoodMart-1.0.0/css/', $template);
$template = str_replace('href="style.css"', 'href="/recette/assets/FoodMart-1.0.0/style.css"', $template);
$template = str_replace('src="images/', 'src="/recette/assets/FoodMart-1.0.0/images/', $template);
$template = str_replace('url(\'images/', 'url(\'/recette/assets/FoodMart-1.0.0/images/', $template);
$template = str_replace('/recette/assets/FoodMart-1.0.0/images/logo.png', '/recette/public/image/logo.png', $template);
$template = str_replace('src="js/', 'src="/recette/assets/FoodMart-1.0.0/js/', $template);
$template = str_replace('href="index.html"', 'href="/recette/"', $template);

$headerFavoris = '              <li>
                <a href="/recette/view/favoris.php" class="rounded-circle bg-light p-2 mx-1 position-relative text-decoration-none text-dark" title="Mes favoris" id="header-favoris-link">
                  <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#heart"></use></svg>
                  <span id="likes-header-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" hidden></span>
                </a>
              </li>';
$template = str_replace(
    '              <li>
                <a href="#" class="rounded-circle bg-light p-2 mx-1">
                  <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#heart"></use></svg>
                </a>
              </li>',
    $headerFavoris,
    $template
);

$favorisScripts = '<script>window.__ALL_RECETTES__ = ' . $allRecettesJson . ';</script>'
    . '<script src="/recette/public/js/recette-likes.js" defer></script>'
    . '<script defer>document.addEventListener("DOMContentLoaded",function(){if(window.initRecetteFavorisPage)initRecetteFavorisPage();});</script>';

$template = preg_replace('/<\/body>/i', $favorisScripts . "\n</body>", $template, 1);

echo $template;
