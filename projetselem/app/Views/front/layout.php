<?php
/**
 * Layout front-office — template FoodMart (dossier FoodMart-1.0.0/FoodMart-1.0.0).
 * Les feuilles de style et scripts pointent vers URL_FOODMART (voir config/config.php).
 * Variables attendues : $pageTitle, $slot, optionnel $footerScripts
 */
$pageTitle = $pageTitle ?? 'Nutrition & Santé';
$fm = rtrim(URL_FOODMART, '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous" />
    <link rel="stylesheet" type="text/css" href="<?= e($fm) ?>/css/vendor.css" />
    <link rel="stylesheet" type="text/css" href="<?= e($fm) ?>/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet" />
</head>
<body>

    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
      <defs>
        <symbol xmlns="http://www.w3.org/2000/svg" id="heart" viewBox="0 0 24 24">
          <path fill="currentColor" d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Zm-1.41 7.46L12 18.81l-6.75-6.74a4.28 4.28 0 0 1 3-7.3a4.25 4.25 0 0 1 3 1.25a1 1 0 0 0 1.42 0a4.27 4.27 0 0 1 6 6.05Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="cart" viewBox="0 0 24 24">
          <path fill="currentColor" d="M8.5 19a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 8.5 19ZM19 16H7a1 1 0 0 1 0-2h8.491a3.013 3.013 0 0 0 2.885-2.176l1.585-5.55A1 1 0 0 0 19 5H6.74a3.007 3.007 0 0 0-2.82-2H3a1 1 0 0 0 0 2h.921a1.005 1.005 0 0 1 .962.725l.155.545v.005l1.641 5.742A3 3 0 0 0 7 18h12a1 1 0 0 0 0-2Zm-1.326-9l-1.22 4.274a1.005 1.005 0 0 1-.963.726H8.754l-.255-.892L7.326 7ZM16.5 19a1.5 1.5 0 1 0 1.5 1.5a1.5 1.5 0 0 0-1.5-1.5Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="search" viewBox="0 0 24 24">
          <path fill="currentColor" d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7a7 7 0 0 1-7 7Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="user" viewBox="0 0 24 24">
          <path fill="currentColor" d="M15.71 12.71a6 6 0 1 0-7.42 0a10 10 0 0 0-6.22 8.18a1 1 0 0 0 2 .22a8 8 0 0 1 15.9 0a1 1 0 0 0 1 .89h.11a1 1 0 0 0 .88-1.1a10 10 0 0 0-6.25-8.19ZM12 12a4 4 0 1 1 4-4a4 4 0 0 1-4 4Z"/>
        </symbol>
      </defs>
    </svg>

    <div class="preloader-wrapper">
      <div class="preloader"></div>
    </div>

    <header>
      <div class="container-fluid">
        <div class="row py-3 border-bottom align-items-center">
          <div class="col-sm-6 col-lg-4 text-center text-sm-start">
            <div class="main-logo">
              <a href="<?= e(BASE_URL) ?>/index.php?action=home">
                <img src="<?= e($fm) ?>/images/logo.png" alt="Logo" class="img-fluid" />
              </a>
            </div>
          </div>
          <div class="col-sm-6 col-lg-8">
            <nav class="navbar navbar-expand-lg justify-content-lg-end">
              <ul class="navbar-nav flex-row gap-3 ms-auto d-none d-lg-flex">
                <li class="nav-item">
                  <a class="nav-link" href="<?= e(BASE_URL) ?>/index.php?action=home">Programmes</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= e(BASE_URL) ?>/index.php?action=recommandation_ia">Assistant parcours</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="<?= e(ADMIN_URL) ?>/index.php?action=dashboard">Admin</a>
                </li>
              </ul>
              <button class="navbar-toggler d-lg-none border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#fmNav" aria-controls="fmNav">
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="fmNav">
                <div class="offcanvas-header">
                  <span class="offcanvas-title">Menu</span>
                  <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
                </div>
                <div class="offcanvas-body">
                  <ul class="navbar-nav gap-2">
                    <li class="nav-item">
                      <a class="nav-link" href="<?= e(BASE_URL) ?>/index.php?action=home">Programmes</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="<?= e(BASE_URL) ?>/index.php?action=recommandation_ia">Assistant parcours</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="<?= e(ADMIN_URL) ?>/index.php?action=dashboard">Admin</a>
                    </li>
                  </ul>
                </div>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </header>

    <section class="py-4" style="background-image: url('<?= e($fm) ?>/images/background-pattern.jpg');background-repeat:no-repeat;background-size:cover;">
      <div class="container-fluid">
        <div class="container">
          <?= $slot ?? '' ?>
        </div>
      </div>
    </section>

    <footer class="py-5 border-top">
      <div class="container-fluid">
        <div class="container">
          <p class="text-muted small mb-0">Projet groupe — module entraînement &amp; santé. APIs : IMC, wger, conseils.</p>
        </div>
      </div>
    </footer>
    <div id="footer-bottom">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-6 copyright">
            <p class="mb-0 small">Template front : FoodMart (HTML) intégré en PHP.</p>
          </div>
          <div class="col-md-6 credit-link text-start text-md-end">
            <p class="mb-0 small">FoodMart par TemplatesJungle / ThemeWagon</p>
          </div>
        </div>
      </div>
    </div>

    <script src="<?= e($fm) ?>/js/jquery-1.11.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <script src="<?= e($fm) ?>/js/plugins.js"></script>
    <script src="<?= e($fm) ?>/js/script.js"></script>
    <?= $footerScripts ?? '' ?>
</body>
</html>
