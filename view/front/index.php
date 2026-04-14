<?php
require_once __DIR__ . '/../../controller/ProduitController.php';
$produitController = new ProduitController();
$produits = $produitController->getAllProduits();
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
        <a href="/marketplace/view/front/index.php">
        <img src="/marketplace/view/front/images/logo-ecobite.jpg" alt="EcoBite" class="logo-img">
        </a>
      </div>
      <div class="col-sm-6 offset-sm-2 offset-md-0 col-lg-5 d-none d-lg-block">
        <div class="search-bar row bg-light p-2 my-2 rounded-4">
          <div class="col-11">
            <input type="text" class="form-control border-0 bg-transparent" placeholder="Rechercher un produit...">
          </div>
          <div class="col-1"><svg width="24" height="24"><use xlink:href="#search"></use></svg></div>
        </div>
      </div>
      <div class="col-sm-8 col-lg-4 d-flex justify-content-end gap-3 align-items-center">
        <div class="support-box text-end d-none d-xl-block">
          <span class="fs-6 text-muted">Service client</span>
          <h5 class="mb-0">+216 98 765 432</h5>
        </div>
        <!-- LIEN PANIER -->
        <a href="/marketplace/index.php?controller=commande&action=panier" class="btn btn-outline-success rounded-pill">
          <svg width="20" height="20"><use xlink:href="#cart"></use></svg> Panier
        </a>
        <!-- LIEN ADMINISTRATION (direct vers Back Office) -->
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
                  <div class="categories my-3">100% naturel</div>
                  <h3 class="display-4">Jus frais & smoothies</h3>
                  <p>Des produits frais, locaux et de saison pour une alimentation saine.</p>
                  <a href="#" class="btn btn-outline-dark btn-lg text-uppercase fs-6 rounded-1 px-4 py-3 mt-3">Découvrir</a>
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
            <div class="categories sale mb-3 pb-3">-20%</div>
            <h3 class="banner-title">Fruits & Légumes</h3>
            <a href="#" class="d-flex align-items-center nav-link">Voir la collection <svg width="24" height="24"><use xlink:href="#arrow-right"></use></svg></a>
          </div>
        </div>
      </div>
      <div class="banner-ad bg-danger block-3" style="background:url('images/ad-image-2.png') no-repeat; background-position: right bottom">
        <div class="row banner-content p-5">
          <div class="content-wrapper col-md-7">
            <div class="categories sale mb-3 pb-3">-15%</div>
            <h3 class="item-title">Produits du terroir</h3>
            <a href="#" class="d-flex align-items-center nav-link">Voir la collection <svg width="24" height="24"><use xlink:href="#arrow-right"></use></svg></a>
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
        <a href="#" class="nav-link category-item swiper-slide"><img src="images/icon-vegetables-broccoli.png"><h3 class="category-title">Fruits & Légumes</h3></a>
        <a href="#" class="nav-link category-item swiper-slide"><img src="images/icon-bread-baguette.png"><h3 class="category-title">Boulangerie bio</h3></a>
        <a href="#" class="nav-link category-item swiper-slide"><img src="images/icon-soft-drinks-bottle.png"><h3 class="category-title">Jus & smoothies</h3></a>
        <a href="#" class="nav-link category-item swiper-slide"><img src="images/icon-wine-glass-bottle.png"><h3 class="category-title">Épicerie fine</h3></a>
        <a href="#" class="nav-link category-item swiper-slide"><img src="images/icon-animal-products-drumsticks.png"><h3 class="category-title">Produits frais</h3></a>
        <a href="#" class="nav-link category-item swiper-slide"><img src="images/icon-bread-herb-flour.png"><h3 class="category-title">Pains & viennoiseries</h3></a>
      </div>
    </div>
  </div>
</section>

<!-- Trending Products -->
<section class="py-5">
  <div class="container-fluid">
    <div class="bootstrap-tabs product-tabs">
      <div class="tabs-header d-flex justify-content-between border-bottom my-5">
        <h3>Produits tendances</h3>
        <nav><div class="nav nav-tabs"><a href="#" class="nav-link text-uppercase fs-6 active">Tous</a></div></nav>
      </div>
      <div class="tab-content">
        <div class="tab-pane fade show active">
          <div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
            <?php foreach(array_slice($produits, 0, 8) as $produit): ?>
            <div class="col">
              <div class="product-item">
                <a href="#" class="btn-wishlist"><svg width="24" height="24"><use xlink:href="#heart"></use></svg></a>
                <figure><a href="#"><img src="images/thumb-bananas.png" class="tab-image"></a></figure>
                <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                <span class="qty"><?= $produit['stock'] ?> unités</span>
                <span class="rating"><svg width="24" height="24"><use xlink:href="#star-solid"></use></svg> 4.5</span>
                <span class="price"><?= number_format($produit['prix'],2) ?> €</span>
                <div class="d-flex align-items-center justify-content-between">
                  <div class="input-group product-qty">
                    <span class="input-group-btn"><button class="quantity-left-minus btn btn-danger btn-number" data-type="minus"><svg width="16" height="16"><use xlink:href="#minus"></use></svg></button></span>
                    <input type="text" name="quantity" class="form-control input-number" value="1">
                    <span class="input-group-btn"><button class="quantity-right-plus btn btn-success btn-number" data-type="plus"><svg width="16" height="16"><use xlink:href="#plus"></use></svg></button></span>
                  </div>
                  <a href="/marketplace/index.php?controller=commande&action=addToPanier&id=<?= $produit['id'] ?>" class="nav-link">Ajouter <svg width="20" height="20"><use xlink:href="#cart"></use></svg></a>
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
        <div class="product-item swiper-slide">
          <a href="#" class="btn-wishlist"><svg width="24" height="24"><use xlink:href="#heart"></use></svg></a>
          <figure><a href="#"><img src="images/thumb-tomatoes.png" class="tab-image"></a></figure>
          <h3><?= htmlspecialchars($produit['nom']) ?></h3>
          <span class="qty"><?= $produit['stock'] ?> unités</span>
          <span class="rating"><svg width="24" height="24"><use xlink:href="#star-solid"></use></svg> 4.5</span>
          <span class="price"><?= number_format($produit['prix'],2) ?> €</span>
          <div class="d-flex align-items-center justify-content-between">
            <div class="input-group product-qty">
              <span class="input-group-btn"><button class="quantity-left-minus btn btn-danger btn-number" data-type="minus"><svg width="16" height="16"><use xlink:href="#minus"></use></svg></button></span>
              <input type="text" name="quantity" class="form-control input-number" value="1">
              <span class="input-group-btn"><button class="quantity-right-plus btn btn-success btn-number" data-type="plus"><svg width="16" height="16"><use xlink:href="#plus"></use></svg></button></span>
            </div>
            <a href="/marketplace/index.php?controller=commande&action=addToPanier&id=<?= $produit['id'] ?>" class="nav-link">Ajouter <svg width="20" height="20"><use xlink:href="#cart"></use></svg></a>
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
</body>
</html>