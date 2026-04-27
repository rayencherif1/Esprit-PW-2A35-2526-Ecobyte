<?php
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../controller/FavorisController.php';
require_once __DIR__ . '/../../controller/CategorieController.php';
require_once __DIR__ . '/../../model/Produit.php';

$categorieController = new CategorieController();
$categories = $categorieController->getAllCategories();

$produitController = new ProduitController();
$selected_category_name = 'Produits tendances';

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
    $produits = $produitController->getAllProduits();
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
    if (strpos($nomLower, 'boisson') !== false || strpos($nomLower, 'jus') !== false || strpos($nomLower, 'smoothie') !== false || strpos($nomLower, 'guarana') !== false) {
        return 'images/product-thumb-1.png';
    } elseif (strpos($nomLower, 'dattes') !== false || strpos($nomLower, 'pâte') !== false || strpos($nomLower, 'barre') !== false) {
        return 'images/thumb-biscuits.png';
    } elseif (strpos($nomLower, 'banane') !== false) {
        return 'images/thumb-bananas.png';
    } elseif (strpos($nomLower, 'tomate') !== false) {
        return 'images/thumb-tomatoes.png';
    } elseif (strpos($nomLower, 'lait') !== false || strpos($nomLower, 'milk') !== false) {
        return 'images/thumb-milk.png';
    } elseif (strpos($nomLower, 'poudre') !== false || strpos($nomLower, 'collagène') !== false || strpos($nomLower, 'glutamine') !== false || strpos($nomLower, 'électrolytes') !== false || strpos($nomLower, 'kreatine') !== false) {
        // Une image par défaut pour les poudres/suppléments (on utilise une image de pot ou générique si possible)
        // Faute de pot de poudre, on utilise l'image du lait ou avocat comme fallback "santé"
        return 'images/thumb-milk.png'; 
    } else {
        // Image par défaut générique
        return 'images/product-thumb-11.jpg'; // Essai d'une image générique
    }
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

// Récupérer les 3 premières catégories pour les bannières publicitaires
$catPromo1 = $categories[0] ?? ['nom' => 'Alimentation saine', 'description' => 'Des produits frais, locaux et de saison pour une alimentation saine.'];
$catPromo2 = $categories[1] ?? ['nom' => 'Produits Bio', 'description' => ''];
$catPromo3 = $categories[2] ?? ['nom' => 'Suppléments', 'description' => ''];
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
        .btn-wishlist {
            cursor: pointer;
        }
        .product-img-wrapper {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .product-img-wrapper img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .hover-bg-light:hover {
            background-color: #f8f9fa !important;
            transition: background-color 0.2s;
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
          <div class="col-4 col-md-3 border-end p-0">
            <select name="search_categorie" class="form-select border-0 bg-transparent shadow-none w-100 text-truncate" style="cursor:pointer; font-size: 14px;">
              <option value="">Toutes catégories</option>
              <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= (isset($_GET['search_categorie']) && $_GET['search_categorie'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-7 col-md-8">
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
          <h5 class="mb-0">+216 98 765 432</h5>
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
            <div class="categories sale mb-3 pb-3">À Découvrir</div>
            <h3 class="item-title">Nouveautés</h3>
            <a href="?filter=nouveautes#produits-section" class="d-flex align-items-center nav-link">Voir la collection <svg width="24" height="24"><use xlink:href="#arrow-right"></use></svg></a>
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

<!-- Trending Products -->
<section id="produits-section" class="py-5">
  <div class="container-fluid">
    <div class="bootstrap-tabs product-tabs">
      <div class="tabs-header d-flex justify-content-between border-bottom my-5">
        <h3><?= $selected_category_name ?></h3>
        <nav>
          <div class="nav nav-tabs">
            <a href="?#produits-section" class="nav-link text-uppercase fs-6 <?= (!isset($_GET['filter']) && !isset($_GET['categorie_id']) && !isset($_GET['search'])) ? 'active' : '' ?>">Tous</a>
            <a href="?filter=nouveautes#produits-section" class="nav-link text-uppercase fs-6 <?= (isset($_GET['filter']) && $_GET['filter'] == 'nouveautes') ? 'active' : '' ?>">Nouveautés</a>
            <a href="?filter=tendances#produits-section" class="nav-link text-uppercase fs-6 <?= (isset($_GET['filter']) && $_GET['filter'] == 'tendances') ? 'active' : '' ?>">Tendances</a>
            <a href="?filter=promo#produits-section" class="nav-link text-uppercase fs-6 <?= (isset($_GET['filter']) && $_GET['filter'] == 'promo') ? 'active' : '' ?>">En Promo</a>
          </div>
        </nav>
      </div>
      <div class="tab-content">
        <div class="tab-pane fade show active">
          <div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
            <?php foreach(array_slice($produits, 0, 8) as $produit): ?>
            <?php $isFavori = in_array($produit['id'], $favorisIds); ?>
            <div class="col">
              <div class="product-item">
                <a href="javascript:void(0)" class="btn-wishlist <?= $isFavori ? 'active' : '' ?>" onclick="addToFavoris(<?= $produit['id'] ?>, this)">
                  <svg width="24" height="24" viewBox="0 0 24 24" <?= $isFavori ? 'fill="red"' : 'fill="none"' ?> stroke="currentColor" stroke-width="2">
                    <path d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Z"/>
                  </svg>
                </a>
                <figure class="product-img-wrapper">
                    <a href="#"><img src="<?= getProductImage($produit['nom']) ?>" class="tab-image"></a>
                </figure>
                <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                <span class="qty"><?= $produit['stock'] ?> unités</span>
                <span class="rating"><svg width="24" height="24"><use xlink:href="#star-solid"></use></svg> 4.5</span>
                <span class="price"><?= number_format($produit['prix'],2) ?> DT</span>
                <div class="d-flex align-items-center justify-content-between mt-3">
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
        <?php foreach($produits as $produit): ?>
        <?php $isFavori = in_array($produit['id'], $favorisIds); ?>
        <div class="product-item swiper-slide">
          <a href="javascript:void(0)" class="btn-wishlist <?= $isFavori ? 'active' : '' ?>" onclick="addToFavoris(<?= $produit['id'] ?>, this)">
            <svg width="24" height="24" viewBox="0 0 24 24" <?= $isFavori ? 'fill="red"' : 'fill="none"' ?> stroke="currentColor" stroke-width="2">
              <path d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Z"/>
            </svg>
          </a>
          <figure class="product-img-wrapper">
            <a href="#"><img src="<?= getProductImage($produit['nom']) ?>" class="tab-image"></a>
          </figure>
          <h3><?= htmlspecialchars($produit['nom']) ?></h3>
          <span class="qty"><?= $produit['stock'] ?> unités</span>
          <span class="rating"><svg width="24" height="24"><use xlink:href="#star-solid"></use></svg> 4.5</span>
          <span class="price"><?= number_format($produit['prix'],2) ?> DT</span>
          <div class="d-flex align-items-center justify-content-between mt-3">
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
    fetch(`/marketplace/index.php?controller=favoris&action=add&id=${produitId}`)
        .then(function(response) {
            return response.text();
        })
        .then(function() {
            // Toggle l'état du cœur
            if (element.classList.contains('active')) {
                element.classList.remove('active');
                element.querySelector('svg').setAttribute('fill', 'none');
            } else {
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