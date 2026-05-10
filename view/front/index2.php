<?php
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../controller/FavorisController.php';
require_once __DIR__ . '/../../controller/CategorieController.php';
require_once __DIR__ . '/../../model/Produit.php';
define('SEASONAL_API_LOCAL_INCLUDE', true);
require_once __DIR__ . '/../../external/seasonal_recommendation.php';

$categorieController = new CategorieController();
$categories = $categorieController->getAllCategories();

$produitController = new ProduitController();
$selected_category_name = 'Produits tendances';
$bestSellers = $produitController->getProduitsTendances();
$promoProduits = $produitController->getProduitsEnPromo();

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $produits = $produitController->searchProduits(trim($_GET['search']));
    $selected_category_name = 'Résultats pour : "' . htmlspecialchars(trim($_GET['search'])) . '"';
} elseif (isset($_GET['filter']) && !empty($_GET['filter'])) {
    if ($_GET['filter'] == 'promo') {
        $produits = $produitController->getProduitsEnPromo();
        $selected_category_name = 'Produits en promotion';
    } elseif ($_GET['filter'] == 'tendances') {
        $produits = $produitController->getProduitsTendances();
        $selected_category_name = 'Produits tendances';
    } elseif ($_GET['filter'] == 'nouveautes') {
        $produits = $produitController->getNouveauxProduits();
        $selected_category_name = 'Nouveautés';
    } elseif ($_GET['filter'] == 'all') {
        $produits = $produitController->getAllProduits();
        $selected_category_name = 'Tous les produits';
    } else {
        $produits = $produitController->getAllProduits();
    }
} elseif (isset($_GET['categorie_id']) && !empty($_GET['categorie_id'])) {
    $produits = $produitController->getProduitsByCategorie($_GET['categorie_id']);
    
    // Trouver le nom de la catégorie pour l'affichage
    foreach ($categories as $cat) {
        if ($cat['id'] == $_GET['categorie_id']) {
            $selected_category_name = 'Produits : ' . htmlspecialchars($cat['nom']);
            break;
        }
    }
} else {
    // Par défaut, on affiche tous les produits
    $produits = $produitController->getAllProduits();
    $selected_category_name = 'Tous les produits';
}

$tunisianCities = getTunisianCities();
$selected_location = sanitizeTunisianCity($_GET['location'] ?? 'Tunis');
$locationQuery = '&location=' . urlencode($selected_location);

// Filtre Nutri‑Score (A-E)
$nutriFilter = isset($_GET['nutriscore']) ? strtoupper(trim($_GET['nutriscore'])) : '';
if ($nutriFilter !== '' && preg_match('/^[A-E]$/', $nutriFilter)) {
    $produits = array_values(array_filter($produits, function($p) use ($nutriFilter) {
        return isset($p['nutriscore']) && strtoupper(trim((string)$p['nutriscore'])) === $nutriFilter;
    }));
    $selected_category_name .= ' — Nutri‑Score ' . htmlspecialchars($nutriFilter);
}

// Fonction pour déterminer l'icône de la catégorie
function getCategoryIcon($nom) {
    $nomLower = strtolower(trim($nom));
    if (strpos($nomLower, 'fruit') !== false || strpos($nomLower, 'légume') !== false || strpos($nomLower, 'legume') !== false) {
        return 'images/icon-vegetables-broccoli.png';
    } elseif (strpos($nomLower, 'boulangerie') !== false || strpos($nomLower, 'pain') !== false || strpos($nomLower, 'viennoiserie') !== false) {
        return 'images/icon-bread-baguette.png';
    } elseif (strpos($nomLower, 'jus') !== false || strpos($nomLower, 'smoothie') !== false || strpos($nomLower, 'boisson') !== false) {
        return 'images/icon-soft-drinks-bottle.png';
    } elseif (strpos($nomLower, 'épicerie') !== false || strpos($nomLower, 'epicerie') !== false || strpos($nomLower, 'fine') !== false) {
        return 'images/icon-wine-glass-bottle.png';
    } elseif (strpos($nomLower, 'frais') !== false || strpos($nomLower, 'viande') !== false || strpos($nomLower, 'fromage') !== false) {
        return 'images/icon-animal-products-drumsticks.png';
    } else {
        return '';
    }
}

// Fonction pour déterminer une image cohérente pour les produits
function getProductImage($nom) {
    $nomLower = strtolower(trim($nom));
    if (strpos($nomLower, 'créatine') !== false || strpos($nomLower, 'creatine') !== false || strpos($nomLower, 'monohydrate') !== false) {
        return 'images/thumb-creatine.svg';
    } elseif (strpos($nomLower, 'whey') !== false || strpos($nomLower, 'protéine') !== false || strpos($nomLower, 'proteine') !== false || strpos($nomLower, 'protein') !== false) {
        return 'images/thumb-whey.svg';
    } elseif (strpos($nomLower, 'collagène') !== false || strpos($nomLower, 'collagene') !== false) {
        return 'images/thumb-collagene.svg';
    } elseif (strpos($nomLower, 'vitamine') !== false || strpos($nomLower, 'vitamines') !== false) {
        return 'images/thumb-vitamines.svg';
    } elseif (strpos($nomLower, 'poudre') !== false || strpos($nomLower, 'supplément') !== false || strpos($nomLower, 'supplement') !== false || strpos($nomLower, 'glutamine') !== false || strpos($nomLower, 'électrolytes') !== false || strpos($nomLower, 'electrolytes') !== false) {
        return 'images/thumb-supplement.svg';
    } elseif (strpos($nomLower, 'boisson') !== false || strpos($nomLower, 'jus') !== false || strpos($nomLower, 'smoothie') !== false || strpos($nomLower, 'guarana') !== false) {
        return 'images/product-thumb-1.png';
    } elseif (strpos($nomLower, 'dattes') !== false || strpos($nomLower, 'pâte') !== false || strpos($nomLower, 'barre') !== false) {
        return 'images/thumb-biscuits.png';
    } elseif (strpos($nomLower, 'banane') !== false) {
        return 'images/thumb-bananas.png';
    } elseif (strpos($nomLower, 'tomate') !== false) {
        return 'images/thumb-tomatoes.png';
    } elseif (strpos($nomLower, 'lait') !== false || strpos($nomLower, 'milk') !== false) {
        return 'images/thumb-milk.png';
    }
    // Aucune image spécifique trouvée : on enlève l'image pour que seul le nom/prix reste visible.
    return '';
}

// Récupérer les IDs des favoris en une seule requête
session_start();
$user_id = $_SESSION['user_id'] ?? 1;

// Connexion directe à la base de données (sans passer par FavorisController)
try {
    $db = new PDO('mysql:host=localhost;dbname=marketplace;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $favorisIds = [];
    $db = null;
}

$favorisIds = [];
if ($db) {
    $sql = "SELECT produit_id FROM favoris WHERE user_id = :user_id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $favorisIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

session_write_close(); // Fermer la session

// Récupérer les recommandations saisonnières
$seasonalData = getSeasonalRecommendations($selected_location);

// Déterminer si afficher tous les produits ou seulement 8
$showAll = ($selected_category_name == 'Tous les produits');
$displayProduits = $showAll ? $produits : array_slice($produits, 0, 8);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>EcoBite – Alimentation saine & naturelle</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/vendor.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .admin-icon {
            background-color: #ffc107;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .admin-icon:hover {
            background-color: #ffcd38;
            transform: scale(1.05);
        }
        .logo-img {
            height: 50px;
            width: auto;
        }
        .btn-wishlist.active svg {
            fill: red;
            color: red;
        }
        .product-qty {
            width: 110px;
            flex-wrap: nowrap;
        }
        .product-qty .form-control {
            width: 40px !important;
            text-align: center;
            padding: 0;
        }
        .product-qty .btn {
            padding: 4px 8px;
        }
        .add-to-cart-btn {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .add-to-cart-btn:hover {
            background: #1e7e34;
            color: white;
        }
        .seasonal-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #ff9800;
            color: white;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 0.2px;
        }
        .btn-wishlist {
            cursor: pointer;
        }
        .product-img-wrapper {
            height: 155px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            overflow: hidden;
            border-radius: 12px;
            background: #f9f9f9;
        }
        .product-img-wrapper img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .product-img-wrapper.empty-product-icon {
            background: rgba(0, 0, 0, 0.04);
            border: 1px dashed rgba(0, 0, 0, 0.12);
        }
        .hover-bg-light:hover {
            background-color: #f8f9fa !important;
            transition: background-color 0.2s;
        }
        .nutriscore-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #fff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
            border: 2px solid rgba(255,255,255,0.9);
            letter-spacing: 0.5px;
            user-select: none;
        }
        .nutriscore-A { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .nutriscore-B { background: linear-gradient(135deg, #8bc34a, #689f38); }
        .nutriscore-C { background: linear-gradient(135deg, #f1c40f, #f39c12); }
        .nutriscore-D { background: linear-gradient(135deg, #ff9800, #f57c00); }
        .nutriscore-E { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .product-item {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 100%;
            padding-top: 14px;
        }
        .product-item .btn-wishlist {
            width: 38px;
            height: 38px;
            top: 12px;
            right: 12px;
            z-index: 12;
        }
        .product-item .btn-wishlist svg {
            width: 18px;
            height: 18px;
        }
        .product-title {
            min-height: 50px;
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-item .price {
            font-size: 22px;
            line-height: 1.2;
            min-height: 34px;
        }
        .product-actions {
            margin-top: auto !important;
        }
        .product-qty .btn-number {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            padding: 0;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .seasonal-collapsible {
            display: none;
        }
        .seasonal-collapsible.is-open {
            display: block;
            animation: seasonal-fade-in 0.35s ease;
        }
        @keyframes seasonal-fade-in {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .nutriscore-skeleton {
            position: absolute;
            top: 12px;
            left: 12px;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(90deg, rgba(0,0,0,0.06), rgba(0,0,0,0.02), rgba(0,0,0,0.06));
            background-size: 200% 100%;
            animation: ns-shimmer 1.2s infinite;
            border: 2px solid rgba(255,255,255,0.9);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        @keyframes ns-shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body>

<!-- SVG Symbols -->
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
  <defs>
    <symbol id="heart" viewBox="0 0 24 24"><path fill="currentColor" d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Zm-1.41 7.46L12 18.81l-6.75-6.74a4.28 4.28 0 0 1 3-7.3a4.25 4.25 0 0 1 3 1.25a1 1 0 0 0 1.42 0a4.27 4.27 0 0 1 6 6.05Z"/></symbol>
    <symbol id="cart" viewBox="0 0 24 24"><path fill="currentColor" d="M8.5 19a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 8.5 19ZM19 16H7a1 1 0 0 1 0-2h8.491a3.013 3.013 0 0 0 2.885-2.176l1.585-5.55A1 1 0 0 0 19 5H6.74a3.007 3.007 0 0 0-2.82-2H3a1 1 0 0 0 0 2h.921a1.005 1.005 0 0 1 .962.725l.155.545v.005l1.641 5.742A3 3 0 0 0 7 18h12a1 1 0 0 0 0-2Zm-1.326-9l-1.22 4.274a1.005 1.005 0 0 1-.963.726H8.754l-.255-.892L7.326 7ZM16.5 19a1.5 1.5 0 1 0 1.5 1.5a1.5 1.5 0 0 0-1.5-1.5Z"/></symbol>
    <symbol id="star-solid" viewBox="0 0 15 15"><path fill="currentColor" d="M7.953 3.788a.5.5 0 0 0-.906 0L6.08 5.85l-2.154.33a.5.5 0 0 0-.283.843l1.574 1.613l-.373 2.284a.5.5 0 0 0 .736.518l1.92-1.063l1.921 1.063a.5.5 0 0 0 .736-.519l-.373-2.283l1.574-1.613a.5.5 0 0 0-.283-.844L8.921 5.85l-.968-2.062Z"/></symbol>
    <symbol id="search" viewBox="0 0 24 24"><path fill="currentColor" d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7a7 7 0 0 1-7 7Z"/></symbol>
    <symbol id="user" viewBox="0 0 24 24"><path fill="currentColor" d="M15.71 12.71a6 6 0 1 0-7.42 0a10 10 0 0 0-6.22 8.18a1 1 0 0 0 2 .22a8 8 0 0 1 15.9 0a1 1 0 0 0 1 .89h.11a1 1 0 0 0 .88-1.1a10 10 0 0 0-6.25-8.19ZM12 12a4 4 0 1 1 4-4a4 4 0 0 1-4 4Z"/></symbol>
    <symbol id="arrow-right" viewBox="0 0 24 24"><path fill="currentColor" d="M17.92 11.62a1 1 0 0 0-.21-.33l-5-5a1 1 0 0 0-1.42 1.42l3.3 3.29H7a1 1 0 0 0 0 2h7.59l-3.3 3.29a1 1 0 0 0 0 1.42a1 1 0 0 0 1.42 0l5-5a1 1 0 0 0 .21-.33a1 1 0 0 0 0-.76Z"/></symbol>
    <symbol id="category" viewBox="0 0 24 24"><path fill="currentColor" d="M19 5.5h-6.28l-.32-1a3 3 0 0 0-2.84-2H5a3 3 0 0 0-3 3v13a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-10a3 3 0 0 0-3-3Zm1 13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-13a1 1 0 0 1 1-1h4.56a1 1 0 0 1 .95.68l.54 1.64a1 1 0 0 0 .95.68h7a1 1 0 0 1 1 1Z"/></symbol>
    <symbol id="calendar" viewBox="0 0 24 24"><path fill="currentColor" d="M19 4h-2V3a1 1 0 0 0-2 0v1H9V3a1 1 0 0 0-2 0v1H5a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3Zm1 15a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-7h16Zm0-9H4V7a1 1 0 0 1 1-1h2v1a1 1 0 0 0 2 0V6h6v1a1 1 0 0 0 2 0V6h2a1 1 0 0 1 1 1Z"/></symbol>
    <symbol id="plus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11h-6V5a1 1 0 0 0-2 0v6H5a1 1 0 0 0 0 2h6v6a1 1 0 0 0 2 0v-6h6a1 1 0 0 0 0-2Z"/></symbol>
    <symbol id="minus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11H5a1 1 0 0 0 0 2h14a1 1 0 0 0 0-2Z"/></symbol>
    <symbol id="lock" viewBox="0 0 24 24"><path fill="currentColor" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></symbol>
  </defs>
</svg>

<!-- Header -->
<header>
  <div class="container-fluid">
    <div class="row py-3 border-bottom align-items-center">
      <div class="col-sm-4 col-lg-3">
        <a href="/marketplace/view/front/index2.php">
          <img src="/marketplace/view/front/images/logo-ecobite.jpg" alt="EcoBite" class="logo-img">
        </a>
      </div>
      <div class="col-sm-6 offset-sm-2 offset-md-0 col-lg-5 d-none d-lg-block">
        <form action="index2.php" method="GET" class="search-bar row bg-light p-2 my-2 rounded-4 m-0 w-100 position-relative">
          <div class="col-2 col-md-2 border-end p-0">
            <select name="search_categorie" class="form-select border-0 bg-transparent shadow-none w-100 text-truncate" style="cursor:pointer; font-size: 14px;">
              <option value="">Toutes catégories</option>
              <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= (isset($_GET['search_categorie']) && $_GET['search_categorie'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-2 col-md-2 border-end p-0">
            <select name="nutriscore" class="form-select border-0 bg-transparent shadow-none w-100 text-truncate" style="cursor:pointer; font-size: 14px;">
              <option value="">Nutri‑Score (Tous)</option>
              <?php foreach(['A','B','C','D','E'] as $g): ?>
                <option value="<?= $g ?>" <?= (isset($_GET['nutriscore']) && strtoupper($_GET['nutriscore']) === $g) ? 'selected' : '' ?>><?= $g ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-4 col-md-3 border-end p-0">
            <select name="location" class="form-select border-0 bg-transparent shadow-none w-100 text-truncate" style="cursor:pointer; font-size: 14px;">
              <?php foreach($tunisianCities as $city): ?>
                <option value="<?= htmlspecialchars($city) ?>" <?= ($selected_location === $city) ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-3 col-md-4">
            <input type="text" name="search" id="live_search_input" class="form-control border-0 bg-transparent shadow-none" placeholder="Rechercher un produit..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" autocomplete="off">
          </div>
          <div class="col-1 p-0 text-center"><button type="submit" class="btn p-0 border-0 bg-transparent"><svg width="24" height="24"><use xlink:href="#search"></use></svg></button></div>
          
          <!-- Live Search Results -->
          <div id="live_search_results" class="position-absolute w-100 bg-white shadow-lg rounded-3" style="top: 100%; left: 0; z-index: 1050; display: none; max-height: 350px; overflow-y: auto;"></div>
        </form>
      </div>
      <div class="col-sm-8 col-lg-4 d-flex justify-content-end gap-3 align-items-center">
        <div class="support-box text-end d-none d-xl-block">
          <span class="fs-6 text-muted">Service client</span>
          <h5 class="mb-0">+216 20 190 091</h5>
        </div>
        <a href="/marketplace/index.php?controller=commande&action=panier" class="btn btn-outline-success rounded-pill">
          <svg width="20" height="20"><use xlink:href="#cart"></use></svg> Panier
        </a>
        <a href="/marketplace/index.php?controller=favoris&action=index" class="btn btn-outline-danger rounded-pill" title="Mes favoris">
          ❤️ Favoris
        </a>
        <a href="/marketplace/view/back/pages/marketplace.php" class="admin-icon" title="Administration">
          <svg width="20" height="20"><use xlink:href="#lock"></use></svg>
        </a>
      </div>
    </div>
  </div>
</header>

<!-- Hero + Offres -->
<section class="py-3" style="background-image: url('images/background-pattern.jpg'); background-repeat: no-repeat; background-size: cover;">
  <div class="container-fluid">
    <div class="banner-blocks">
      <div class="banner-ad large bg-info block-1">
        <div class="swiper main-swiper">
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <div class="row banner-content p-5">
                <div class="content-wrapper col-md-7">
                  <div class="categories my-3">Notre Sélection</div>
                  <h3 class="display-4">Produits Tendances</h3>
                  <p>Découvrez les produits les plus appréciés et les plus vendus de notre boutique.</p>
                  <a href="?filter=tendances#produits-section" class="btn btn-outline-dark btn-lg text-uppercase fs-6 rounded-1 px-4 py-3 mt-3">Découvrir</a>
                </div>
                <div class="img-wrapper col-md-5"><img src="images/product-thumb-1.png" class="img-fluid"></div>
              </div>
            </div>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>
      <div class="banner-ad bg-success-subtle block-2" style="background:url('images/ad-image-1.png') no-repeat; background-position: right bottom">
        <div class="row banner-content p-5">
          <div class="content-wrapper col-md-7">
            <div class="categories sale mb-3 pb-3">En Promo</div>
            <h3 class="banner-title">Produits Soldés</h3>
            <a href="?filter=promo#produits-section" class="d-flex align-items-center nav-link">Voir les promos <svg width="24" height="24"><use xlink:href="#arrow-right"></use></svg></a>
          </div>
        </div>
      </div>
      <div class="banner-ad bg-danger block-3" style="background:url('images/ad-image-2.png') no-repeat; background-position: right bottom">
        <div class="row banner-content p-5">
          <div class="content-wrapper col-md-7">
            <div class="categories sale mb-3 pb-3">Saison</div>
            <h3 class="item-title">Recommandations Saisonnières</h3>
            <p class="mb-4">Produits conseillés selon la météo actuelle et la saison. Cliquez pour voir les recommandations réelles.</p>
            <a href="#" id="open-seasonal-recommendations" class="d-flex align-items-center nav-link">Voir les recommandations <svg width="24" height="24"><use xlink:href="#arrow-right"></use></svg></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Category Carousel -->
<section class="py-5 overflow-hidden">
  <div class="container-fluid">
    <div class="section-header d-flex flex-wrap justify-content-between mb-5">
      <h2 class="section-title">Catégories</h2>
      <div class="d-flex align-items-center">
        <a href="#" class="btn-link text-decoration-none">Toutes les catégories →</a>
        <div class="swiper-buttons">
          <button class="swiper-prev category-carousel-prev btn btn-yellow">❮</button>
          <button class="swiper-next category-carousel-next btn btn-yellow">❯</button>
        </div>
      </div>
    </div>
    <div class="category-carousel swiper">
      <div class="swiper-wrapper">
        <?php foreach($categories as $categorie): ?>
        <a href="?categorie_id=<?= $categorie['id'] ?>#produits-section" class="nav-link category-item swiper-slide">
            <?php $icon = getCategoryIcon($categorie['nom']); if (!empty($icon)): ?>
                <img src="<?= $icon ?>" alt="<?= htmlspecialchars($categorie['nom']) ?>">
            <?php endif; ?>
            <h3 class="category-title"><?= htmlspecialchars($categorie['nom']) ?></h3>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Seasonal Recommendations -->
<section id="seasonal-section" class="py-5 overflow-hidden seasonal-collapsible" style="background: linear-gradient(135deg, #e8f5e8 0%, #f1f8e9 100%);">
  <div class="container-fluid">
    <div class="section-header d-flex flex-wrap justify-content-between mb-5">
      <div>
        <h2 class="section-title">🌤️ Recommandations Saisonnières</h2>
        <p class="text-muted mt-2"><?= htmlspecialchars($seasonalData['message']) ?></p>
      </div>
      <div class="d-flex align-items-center">
        <div class="weather-info bg-white p-3 rounded shadow-sm">
          <small class="text-muted">Météo actuelle</small><br>
          <strong><?= htmlspecialchars($seasonalData['weather']['temp']) ?>°C - <?= htmlspecialchars($seasonalData['weather']['description']) ?></strong>
          <div class="small mt-1">Min/Max: <?= htmlspecialchars($seasonalData['weather']['temp_min'] ?? $seasonalData['weather']['temp']) ?>°C / <?= htmlspecialchars($seasonalData['weather']['temp_max'] ?? $seasonalData['weather']['temp']) ?>°C</div>
          <div class="small mt-1">Jour: <?= htmlspecialchars($seasonalData['weather']['day_description'] ?? $seasonalData['weather']['description']) ?></div>
          <div class="small mt-1">Nuit: <?= htmlspecialchars($seasonalData['weather']['night_description'] ?? $seasonalData['weather']['description']) ?></div>
          <?php if (!empty($seasonalData['weather']['sunrise']) || !empty($seasonalData['weather']['sunset'])): ?>
            <div class="small mt-1">Soleil: <?= htmlspecialchars($seasonalData['weather']['sunrise'] ?? '--') ?> / <?= htmlspecialchars($seasonalData['weather']['sunset'] ?? '--') ?></div>
          <?php endif; ?>
          <?php if (!empty($seasonalData['location'])): ?>
            <div class="text-muted small mt-1">Localisation : <?= htmlspecialchars($seasonalData['location']) ?></div>
          <?php endif; ?>
          <?php if (!empty($seasonalData['weather']['source']) && $seasonalData['weather']['source'] === 'live'): ?>
            <div class="text-success small mt-1">Donnée météo en direct</div>
          <?php else: ?>
            <div class="text-warning small mt-1">Météo de secours (fallback saisonnier)</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="products-carousel swiper">
      <div class="swiper-wrapper">
        <?php if (!empty($seasonalData['recommendations'])): ?>
          <?php foreach($seasonalData['recommendations'] as $rec): ?>
          <?php $isFavori = in_array($rec['id'], $favorisIds); ?>
          <div class="product-item swiper-slide" data-product-id="<?= intval($rec['id']) ?>">
            <div class="seasonal-badge">⭐ Recommandé</div>
            <a href="javascript:void(0)" class="btn-wishlist <?= $isFavori ? 'active' : '' ?>" onclick="addToFavoris(<?= $rec['id'] ?>, this)">
              <svg width="24" height="24" viewBox="0 0 24 24" <?= $isFavori ? 'fill="red"' : 'fill="none"' ?> stroke="currentColor" stroke-width="2">
                <path d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Z"/>
              </svg>
            </a>
          <?php $imagePath = getProductImage($rec['nom']); ?>
          <figure class="product-img-wrapper <?= empty($imagePath) ? 'empty-product-icon' : '' ?>">
            <?php if (!empty($imagePath)): ?>
              <a href="#"><img src="<?= $imagePath ?>" class="tab-image"></a>
            <?php else: ?>
              <span class="text-muted small">Aperçu</span>
            <?php endif; ?>
          </figure>
          <h3 class="product-title"><?= htmlspecialchars($rec['nom']) ?></h3>
            <?php
              $isPromo = !empty($rec['is_promo']);
              $prixPromo = isset($rec['prix_promo']) && $rec['prix_promo'] !== '' && $rec['prix_promo'] !== null ? floatval($rec['prix_promo']) : null;
            ?>
            <?php if ($isPromo && $prixPromo !== null && $prixPromo > 0 && $prixPromo < floatval($rec['prix'])): ?>
              <span class="price">
                <span style="text-decoration: line-through; color:#999; margin-right:6px;"><?= number_format($rec['prix'],2) ?> DT</span>
                <span style="color:#d32f2f; font-weight:800;"><?= number_format($prixPromo,2) ?> DT</span>
              </span>
            <?php else: ?>
              <span class="price"><?= number_format($rec['prix'],2) ?> DT</span>
            <?php endif; ?>
            <div class="d-flex align-items-center justify-content-between mt-3 product-actions">
              <div class="input-group product-qty">
                <span class="input-group-btn">
                  <button class="quantity-left-minus btn btn-danger btn-number" data-type="minus" data-id="<?= $rec['id'] ?>">
                    <svg width="14" height="14"><use xlink:href="#minus"></use></svg>
                  </button>
                </span>
                <input type="text" name="quantity" class="form-control input-number text-center" value="1" data-id="<?= $rec['id'] ?>" style="width: 40px;">
                <span class="input-group-btn">
                  <button class="quantity-right-plus btn btn-success btn-number" data-type="plus" data-id="<?= $rec['id'] ?>">
                    <svg width="14" height="14"><use xlink:href="#plus"></use></svg>
                  </button>
                </span>
              </div>
              <button class="add-to-cart-btn" data-id="<?= $rec['id'] ?>">
                Ajouter <svg width="16" height="16"><use xlink:href="#cart"></use></svg>
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info mb-0">Aucune recommandation disponible pour le moment. Revenez plus tard ou actualisez la page.</div>
          </div>
        <?php endif; ?>
      </div>
      <?php if (!empty($seasonalData['recommendations'])): ?>
      <div class="swiper-buttons">
        <button class="swiper-prev seasonal-carousel-prev btn btn-primary">❮</button>
        <button class="swiper-next seasonal-carousel-next btn btn-primary">❯</button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Trending Products -->
<section id="produits-section" class="py-5">
  <div class="container-fluid">
    <div class="bootstrap-tabs product-tabs">
      <div class="tabs-header d-flex justify-content-between border-bottom my-5">
        <h3><?= $selected_category_name ?></h3>
        <nav>
          <div class="nav nav-tabs">
            <a href="?filter=all<?= $locationQuery ?>#produits-section" class="nav-link text-uppercase fs-6 <?= ((!isset($_GET['filter']) && !isset($_GET['categorie_id']) && !isset($_GET['search'])) || (isset($_GET['filter']) && $_GET['filter'] == 'all')) ? 'active' : '' ?>">Tous</a>
            <a href="?filter=nouveautes<?= $locationQuery ?>#produits-section" class="nav-link text-uppercase fs-6 <?= (isset($_GET['filter']) && $_GET['filter'] == 'nouveautes') ? 'active' : '' ?>">Nouveautés</a>
            <a href="?filter=tendances<?= $locationQuery ?>#produits-section" class="nav-link text-uppercase fs-6 <?= (isset($_GET['filter']) && $_GET['filter'] == 'tendances') ? 'active' : '' ?>">Tendances</a>
            <a href="?filter=promo<?= $locationQuery ?>#produits-section" class="nav-link text-uppercase fs-6 <?= (isset($_GET['filter']) && $_GET['filter'] == 'promo') ? 'active' : '' ?>">En Promo</a>
          </div>
        </nav>
      </div>
      <div class="tab-content">
        <div class="tab-pane fade show active">
          <div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
            <?php foreach($displayProduits as $produit): ?>
            <?php $isFavori = in_array($produit['id'], $favorisIds); ?>
            <div class="col">
              <div class="product-item" data-product-id="<?= intval($produit['id']) ?>">
                <?php $ns = strtoupper(trim($produit['nutriscore'] ?? '')); ?>
                <?php if (preg_match('/^[A-E]$/', $ns)): ?>
                  <div class="nutriscore-badge nutriscore-<?= $ns ?>" title="Nutri‑Score <?= $ns ?>"><?= $ns ?></div>
                <?php else: ?>
                  <div class="nutriscore-skeleton" aria-hidden="true"></div>
                <?php endif; ?>
                <a href="javascript:void(0)" class="btn-wishlist <?= $isFavori ? 'active' : '' ?>" onclick="addToFavoris(<?= $produit['id'] ?>, this)">
                  <svg width="24" height="24" viewBox="0 0 24 24" <?= $isFavori ? 'fill="red"' : 'fill="none"' ?> stroke="currentColor" stroke-width="2">
                    <path d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Z"/>
                  </svg>
                </a>
                <?php $imagePath = getProductImage($produit['nom']); ?>
                <figure class="product-img-wrapper <?= empty($imagePath) ? 'empty-product-icon' : '' ?>">
                  <?php if (!empty($imagePath)): ?>
                    <a href="#"><img src="<?= $imagePath ?>" class="tab-image"></a>
                  <?php else: ?>
                    <span class="text-muted small">Aperçu</span>
                  <?php endif; ?>
                </figure>
                <h3 class="product-title"><?= htmlspecialchars($produit['nom']) ?></h3>
                <span class="qty"><?= $produit['stock'] ?> unités</span>
                <?php
                  $isPromo = !empty($produit['is_promo']);
                  $prixPromo = isset($produit['prix_promo']) && $produit['prix_promo'] !== '' && $produit['prix_promo'] !== null ? floatval($produit['prix_promo']) : null;
                ?>
                <?php if ($isPromo && $prixPromo !== null && $prixPromo > 0 && $prixPromo < floatval($produit['prix'])): ?>
                  <span class="price">
                    <span style="text-decoration: line-through; color:#999; margin-right:6px;"><?= number_format($produit['prix'],2) ?> DT</span>
                    <span style="color:#d32f2f; font-weight:800;"><?= number_format($prixPromo,2) ?> DT</span>
                  </span>
                <?php else: ?>
                  <span class="price"><?= number_format($produit['prix'],2) ?> DT</span>
                <?php endif; ?>
                <div class="d-flex align-items-center justify-content-between mt-3 product-actions">
                  <div class="input-group product-qty">
                    <span class="input-group-btn">
                      <button class="quantity-left-minus btn btn-danger btn-number" data-type="minus" data-id="<?= $produit['id'] ?>">
                        <svg width="14" height="14"><use xlink:href="#minus"></use></svg>
                      </button>
                    </span>
                    <input type="text" name="quantity" class="form-control input-number text-center" value="1" data-id="<?= $produit['id'] ?>" style="width: 40px;">
                    <span class="input-group-btn">
                      <button class="quantity-right-plus btn btn-success btn-number" data-type="plus" data-id="<?= $produit['id'] ?>">
                        <svg width="14" height="14"><use xlink:href="#plus"></use></svg>
                      </button>
                    </span>
                  </div>
                  <button class="add-to-cart-btn" data-id="<?= $produit['id'] ?>">
                    Ajouter <svg width="16" height="16"><use xlink:href="#cart"></use></svg>
                  </button>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Best selling products -->
<section class="py-5 overflow-hidden">
  <div class="container-fluid">
    <div class="section-header d-flex flex-wrap justify-content-between my-5">
      <h2 class="section-title">Meilleures ventes</h2>
      <div class="d-flex align-items-center">
        <a href="#" class="btn-link text-decoration-none">Toutes les catégories →</a>
        <div class="swiper-buttons">
          <button class="swiper-prev products-carousel-prev btn btn-primary">❮</button>
          <button class="swiper-next products-carousel-next btn btn-primary">❯</button>
        </div>
      </div>
    </div>
    <div class="products-carousel swiper">
      <div class="swiper-wrapper">
        <?php foreach($bestSellers as $produit): ?>
        <?php $isFavori = in_array($produit['id'], $favorisIds); ?>
        <div class="product-item swiper-slide" data-product-id="<?= intval($produit['id']) ?>">
          <?php $ns = strtoupper(trim($produit['nutriscore'] ?? '')); ?>
          <?php if (preg_match('/^[A-E]$/', $ns)): ?>
            <div class="nutriscore-badge nutriscore-<?= $ns ?>" title="Nutri‑Score <?= $ns ?>"><?= $ns ?></div>
          <?php else: ?>
            <div class="nutriscore-skeleton" aria-hidden="true"></div>
          <?php endif; ?>
          <a href="javascript:void(0)" class="btn-wishlist <?= $isFavori ? 'active' : '' ?>" onclick="addToFavoris(<?= $produit['id'] ?>, this)">
            <svg width="24" height="24" viewBox="0 0 24 24" <?= $isFavori ? 'fill="red"' : 'fill="none"' ?> stroke="currentColor" stroke-width="2">
              <path d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Z"/>
            </svg>
          </a>
          <?php $imagePath = getProductImage($produit['nom']); ?>
          <figure class="product-img-wrapper <?= empty($imagePath) ? 'empty-product-icon' : '' ?>">
            <?php if (!empty($imagePath)): ?>
              <a href="#"><img src="<?= $imagePath ?>" class="tab-image"></a>
            <?php else: ?>
              <span class="text-muted small">Aperçu</span>
            <?php endif; ?>
          </figure>
          <h3 class="product-title"><?= htmlspecialchars($produit['nom']) ?></h3>
          <span class="qty"><?= $produit['stock'] ?> unités</span>
          <?php
            $isPromo = !empty($produit['is_promo']);
            $prixPromo = isset($produit['prix_promo']) && $produit['prix_promo'] !== '' && $produit['prix_promo'] !== null ? floatval($produit['prix_promo']) : null;
          ?>
          <?php if ($isPromo && $prixPromo !== null && $prixPromo > 0 && $prixPromo < floatval($produit['prix'])): ?>
            <span class="price">
              <span style="text-decoration: line-through; color:#999; margin-right:6px;"><?= number_format($produit['prix'],2) ?> DT</span>
              <span style="color:#d32f2f; font-weight:800;"><?= number_format($prixPromo,2) ?> DT</span>
            </span>
          <?php else: ?>
            <span class="price"><?= number_format($produit['prix'],2) ?> DT</span>
          <?php endif; ?>
          <div class="d-flex align-items-center justify-content-between mt-3 product-actions">
            <div class="input-group product-qty">
              <span class="input-group-btn">
                <button class="quantity-left-minus btn btn-danger btn-number" data-type="minus" data-id="<?= $produit['id'] ?>">
                  <svg width="14" height="14"><use xlink:href="#minus"></use></svg>
                </button>
              </span>
              <input type="text" name="quantity" class="form-control input-number text-center" value="1" data-id="<?= $produit['id'] ?>" style="width: 40px;">
              <span class="input-group-btn">
                <button class="quantity-right-plus btn btn-success btn-number" data-type="plus" data-id="<?= $produit['id'] ?>">
                  <svg width="14" height="14"><use xlink:href="#plus"></use></svg>
                </button>
              </span>
            </div>
            <button class="add-to-cart-btn" data-id="<?= $produit['id'] ?>">
              Ajouter <svg width="16" height="16"><use xlink:href="#cart"></use></svg>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Blog -->
<section id="latest-blog" class="py-5">
  <div class="container-fluid">
    <div class="section-header d-flex align-items-center justify-content-between my-5">
      <h2 class="section-title">Notre blog</h2>
      <div class="btn-wrap"><a href="#" class="d-flex align-items-center nav-link">Tous les articles <svg width="24" height="24"><use xlink:href="#arrow-right"></use></svg></a></div>
    </div>
    <div class="row">
      <div class="col-md-4"><article class="post-item card border-0 shadow-sm p-3"><div class="image-holder zoom-effect"><a href="#"><img src="images/post-thumb-1.jpg" class="card-img-top"></a></div><div class="card-body"><div class="post-meta d-flex text-uppercase gap-3 my-2"><div class="meta-date"><svg width="16" height="16"><use xlink:href="#calendar"></use></svg> 15 Avr 2025</div><div class="meta-categories"><svg width="16" height="16"><use xlink:href="#category"></use></svg> nutrition</div></div><h3 class="post-title"><a href="#" class="text-decoration-none">Les bienfaits des fruits de saison</a></h3><p>Découvrez comment consommer local et de saison pour votre santé.</p></div></article></div>
      <div class="col-md-4"><article class="post-item card border-0 shadow-sm p-3"><div class="image-holder zoom-effect"><a href="#"><img src="images/post-thumb-2.jpg" class="card-img-top"></a></div><div class="card-body"><div class="post-meta d-flex text-uppercase gap-3 my-2"><div class="meta-date"><svg width="16" height="16"><use xlink:href="#calendar"></use></svg> 10 Avr 2025</div><div class="meta-categories"><svg width="16" height="16"><use xlink:href="#category"></use></svg> recettes</div></div><h3 class="post-title"><a href="#" class="text-decoration-none">3 smoothies detox à essayer</a></h3><p>Des recettes simples et rapides pour un coup de boost.</p></div></article></div>
      <div class="col-md-4"><article class="post-item card border-0 shadow-sm p-3"><div class="image-holder zoom-effect"><a href="#"><img src="images/post-thumb-3.jpg" class="card-img-top"></a></div><div class="card-body"><div class="post-meta d-flex text-uppercase gap-3 my-2"><div class="meta-date"><svg width="16" height="16"><use xlink:href="#calendar"></use></svg> 5 Avr 2025</div><div class="meta-categories"><svg width="16" height="16"><use xlink:href="#category"></use></svg> astuces</div></div><h3 class="post-title"><a href="#" class="text-decoration-none">Comment bien conserver ses légumes</a></h3><p>Prolongez la fraîcheur de vos aliments avec ces astuces simples.</p></div></article></div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="py-5">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-3 col-md-6">
        <div class="footer-menu">
          <img src="/marketplace/view/front/images/logo-ecobite.jpg" alt="EcoBite" style="height: 40px; margin-bottom: 15px;">
          <p>Manger mieux, vivre mieux.</p>
        </div>
      </div>
      <div class="col-md-2"><div class="footer-menu"><h5>À propos</h5><ul class="list-unstyled"><li><a href="#" class="text-decoration-none">Notre histoire</a></li><li><a href="#" class="text-decoration-none">Contact</a></li></ul></div></div>
      <div class="col-lg-3"><div class="footer-menu"><h5>Newsletter</h5><form class="d-flex mt-3 gap-0"><input class="form-control rounded-start rounded-0 bg-light" type="email" placeholder="Email"><button class="btn btn-dark rounded-end rounded-0" type="submit">S'abonner</button></form></div></div>
    </div>
  </div>
</footer>
<div id="footer-bottom"><div class="container-fluid"><div class="row"><div class="col-md-6 copyright"><p>© 2025 EcoBite - Votre marketplace nutrition</p></div></div></div></div>

<script src="js/jquery-1.11.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/plugins.js"></script>
<script src="js/script.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const openSeasonalBtn = document.getElementById('open-seasonal-recommendations');
    const seasonalSection = document.getElementById('seasonal-section');
    if (!openSeasonalBtn || !seasonalSection) {
        return;
    }

    openSeasonalBtn.addEventListener('click', function(e) {
        e.preventDefault();
        seasonalSection.classList.add('is-open');
        seasonalSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>

<script>
// Gestion des quantités
document.addEventListener('DOMContentLoaded', function() {
    // Boutons +
    document.querySelectorAll('.quantity-right-plus').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            let input = this.closest('.product-qty').querySelector('.input-number');
            let currentValue = parseInt(input.value);
            if (!isNaN(currentValue)) {
                input.value = currentValue + 1;
            } else {
                input.value = 1;
            }
        });
    });
    
    // Boutons -
    document.querySelectorAll('.quantity-left-minus').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            let input = this.closest('.product-qty').querySelector('.input-number');
            let currentValue = parseInt(input.value);
            if (!isNaN(currentValue) && currentValue > 1) {
                input.value = currentValue - 1;
            } else {
                input.value = 1;
            }
        });
    });
    
    // Boutons Ajouter au panier
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            let productId = this.dataset.id;
            let qtyInput = this.closest('.product-item').querySelector('.input-number');
            let quantity = qtyInput ? qtyInput.value : 1;
            window.location.href = `/marketplace/index.php?controller=commande&action=addToPanier&id=${productId}&quantite=${quantity}`;
        });
    });
});

// Fonction pour ajouter/retirer des favoris via AJAX
function addToFavoris(produitId, element) {
    fetch(`/marketplace/index.php?controller=favoris&action=add&id=${produitId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (!data || data.ok !== true) {
                throw new Error('Echec favoris');
            }
            if (data.action === 'removed') {
                element.classList.remove('active');
                element.querySelector('svg').setAttribute('fill', 'none');
            } else if (data.action === 'added') {
                element.classList.add('active');
                element.querySelector('svg').setAttribute('fill', 'red');
            }
        })
        .catch(function(error) {
            console.error('Erreur:', error);
        });
}
</script>

<script>
// Nutri‑Score (API externe OpenFoodFacts) — chargé automatiquement côté client
document.addEventListener('DOMContentLoaded', function() {
  const cards = document.querySelectorAll('.product-item[data-product-id]');
  const ids = [];

  cards.forEach(card => {
    const hasNutri = card.querySelector('.nutriscore-badge');
    const skeleton = card.querySelector('.nutriscore-skeleton');
    const id = card.getAttribute('data-product-id');
    if (!hasNutri && skeleton && id) ids.push(id);
  });

  // Limite: éviter de spammer l'API si beaucoup de produits
  ids.slice(0, 12).forEach(id => {
    fetch(`/marketplace/index.php?controller=api&action=nutriscore&id=${encodeURIComponent(id)}`)
      .then(r => r.json())
      .then(data => {
        const grade = (data && data.nutriscore) ? String(data.nutriscore).toUpperCase() : '';
        const card = document.querySelector(`.product-item[data-product-id="${id}"]`);
        if (!card) return;

        // Si l'API ne trouve rien: enlever le skeleton pour éviter un "loading" infini
        if (!grade.match(/^[A-E]$/)) {
          const sk = card.querySelector('.nutriscore-skeleton');
          if (sk) sk.remove();
          // Afficher un badge neutre si pas de score
          if (!card.querySelector('.nutriscore-badge')) {
            const div = document.createElement('div');
            div.className = 'nutriscore-badge';
            div.style.background = 'linear-gradient(135deg, #9e9e9e, #757575)';
            div.title = 'Nutri‑Score indisponible';
            div.textContent = '?';
            card.prepend(div);
          }
          return;
        }

        const sk = card.querySelector('.nutriscore-skeleton');
        if (sk) sk.remove();
        if (!card.querySelector('.nutriscore-badge')) {
          const div = document.createElement('div');
          div.className = `nutriscore-badge nutriscore-${grade}`;
          div.title = `Nutri‑Score ${grade}`;
          div.textContent = grade;
          card.prepend(div);
        }
      })
      .catch(() => {});
  });
});
</script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('live_search_input');
        const searchResults = document.getElementById('live_search_results');
        const searchCat = document.querySelector('select[name="search_categorie"]');

        let timeoutId = null;

        searchInput.addEventListener('keyup', function() {
            clearTimeout(timeoutId);
            const query = this.value.trim();
            const catId = searchCat.value;

            if (query.length > 0) {
                timeoutId = setTimeout(() => {
                    fetch(`ajax_search.php?query=${encodeURIComponent(query)}&cat_id=${catId}`)
                        .then(response => response.json())
                        .then(data => {
                            searchResults.innerHTML = '';
                            if (data.length > 0) {
                                data.forEach(item => {
                                    const div = document.createElement('a');
                                    div.href = item.url;
                                    div.className = 'd-block p-3 border-bottom text-decoration-none text-dark hover-bg-light';
                                    div.innerHTML = `
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${item.nom}</strong>
                                                <div class="text-muted small">${item.categorie}</div>
                                            </div>
                                            <span class="text-success fw-bold">${item.prix} DT</span>
                                        </div>`;
                                    searchResults.appendChild(div);
                                });
                                searchResults.style.display = 'block';
                            } else {
                                searchResults.innerHTML = '<div class="p-3 text-muted text-center">Aucun produit trouvé.</div>';
                                searchResults.style.display = 'block';
                            }
                        })
                        .catch(err => console.error("Erreur de recherche", err));
                }, 300);
            } else {
                searchResults.style.display = 'none';
            }
        });

        // Cacher les résultats si on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target) && !searchCat.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
        
        // Mettre à jour la recherche si on change de catégorie
        searchCat.addEventListener('change', function() {
            if(searchInput.value.trim().length > 0) {
                searchInput.dispatchEvent(new Event('keyup'));
            }
        });
    });
  </script>
</body>
</html>