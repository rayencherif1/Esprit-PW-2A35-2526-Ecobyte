<?php
<<<<<<< HEAD
// Cette page sera appelée par FavorisController
// Assurer que la session est démarrée si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getProductImage($nom) {
    $nomLower = strtolower(trim($nom));
    if (strpos($nomLower, 'créatine') !== false || strpos($nomLower, 'creatine') !== false || strpos($nomLower, 'monohydrate') !== false) {
        return '/2int/view/front/images/thumb-creatine.svg';
    } elseif (strpos($nomLower, 'whey') !== false || strpos($nomLower, 'protéine') !== false || strpos($nomLower, 'proteine') !== false || strpos($nomLower, 'protein') !== false) {
        return '/2int/view/front/images/thumb-whey.svg';
    } elseif (strpos($nomLower, 'collagène') !== false || strpos($nomLower, 'collagene') !== false) {
        return '/2int/view/front/images/thumb-collagene.svg';
    } elseif (strpos($nomLower, 'vitamine') !== false || strpos($nomLower, 'vitamines') !== false) {
        return '/2int/view/front/images/thumb-vitamines.svg';
    } elseif (strpos($nomLower, 'poudre') !== false || strpos($nomLower, 'supplément') !== false || strpos($nomLower, 'supplement') !== false || strpos($nomLower, 'glutamine') !== false || strpos($nomLower, 'électrolytes') !== false || strpos($nomLower, 'electrolytes') !== false) {
        return '/2int/view/front/images/thumb-supplement.svg';
    } elseif (strpos($nomLower, 'boisson') !== false || strpos($nomLower, 'jus') !== false || strpos($nomLower, 'smoothie') !== false || strpos($nomLower, 'guarana') !== false) {
        return '/2int/view/front/images/product-thumb-1.png';
    } elseif (strpos($nomLower, 'dattes') !== false || strpos($nomLower, 'pâte') !== false || strpos($nomLower, 'barre') !== false) {
        return '/2int/view/front/images/thumb-biscuits.png';
    } elseif (strpos($nomLower, 'banane') !== false) {
        return '/2int/view/front/images/thumb-bananas.png';
    } elseif (strpos($nomLower, 'tomate') !== false) {
        return '/2int/view/front/images/thumb-tomatoes.png';
    } elseif (strpos($nomLower, 'lait') !== false || strpos($nomLower, 'milk') !== false) {
        return '/2int/view/front/images/thumb-milk.png';
    }
    return '';
}
=======
declare(strict_types=1);
require_once __DIR__ . '/../../controller/RecetteController.php';

$controller = new RecetteController();
$allRecettes = $controller->afficherRecettes();

// Get unique types for potential navigation/filter consistency
$allTypes = array_unique(array_filter(array_column($allRecettes, 'type')));
sort($allTypes);
>>>>>>> origin/mohamed
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<<<<<<< HEAD
    <title>Mes favoris - EcoBite</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/2int/view/front/css/vendor.css">
    <link rel="stylesheet" href="/2int/view/front/style.css">
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
            transition: transform 0.2s;
        }
        .btn-wishlist:hover {
            transform: scale(1.1);
        }
        .empty-favorites {
            text-align: center;
            padding: 80px 20px;
            background: #f8f9fa;
            border-radius: 20px;
            margin: 40px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .empty-favorites svg {
            margin-bottom: 20px;
            opacity: 0.5;
        }
=======
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Favoris — EcoByte</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --eco-green: #4caf50;
            --eco-orange: #ff6b35;
            --eco-dark: #1a1a2e;
            --eco-indigo: #5e72e4;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Open Sans', sans-serif; background: #f4f6f9; min-height: 100vh; display: flex; flex-direction: column; }

        /* ═══ DARK TOPBAR ══════════════════════════════════════════════ */
        .ecobyte-topbar {
            background: var(--eco-dark); padding: 10px 32px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 2px 12px rgba(0,0,0,0.35);
        }
        .ecobyte-topbar .eco-logo {
            display: flex; align-items: center; gap: 8px;
            font-family: 'Nunito', sans-serif; font-size: 1.2rem; font-weight: 800; text-decoration: none;
        }
        .ecobyte-topbar .eco-logo .eco  { color: var(--eco-green); }
        .ecobyte-topbar .eco-logo .byte { color: var(--eco-orange); }
        .module-badge {
            background: rgba(229, 62, 62, 0.15); border: 1px solid rgba(229, 62, 62, 0.3);
            color: #fecdd3; padding: 4px 14px; border-radius: 999px; font-size: .72rem; font-weight: 700;
        }
        .topbar-right { display: flex; align-items: center; gap: 14px; }
        .hub-link {
            color: #aaa; text-decoration: none; font-size: .82rem; font-weight: 500;
            display: flex; align-items: center; gap: 5px;
        }
        .hub-link:hover { color: #fff; }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #4caf50, #2196f3); color: #fff; font-weight: 700; text-decoration: none;
        }

        /* ═══ WHITE HEADER ═══════════════════════════════════════════ */
        .site-header {
            background: #fff; border-bottom: 1px solid #e9ecef;
            padding: 16px 32px; display: flex; align-items: center; justify-content: space-between;
        }
        .site-header .brand h1 {
            font-family: 'Nunito', sans-serif; font-size: 1.5rem; font-weight: 800; margin: 0; color: #1e293b;
        }
        .header-actions { display: flex; align-items: center; gap: 14px; }
        .btn-back {
            display: flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 50px;
            font-size: .85rem; font-weight: 600; text-decoration: none;
            background: #f1f5f9; color: #1e293b; border: 1.5px solid #e2e8f0;
            transition: all .2s;
        }
        .btn-back:hover { background: #e2e8f0; }

        /* ═══ CONTENT SECTION ════════════════════════════════════════ */
        .content-section { padding: 48px 32px; flex: 1; }
        .section-header { margin-bottom: 32px; }
        .section-title { font-family: 'Nunito', sans-serif; font-size: 1.6rem; font-weight: 800; color: #1e293b; }

        /* ═══ CARD GRID ══════════════════════════════════════════════ */
        .recipes-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px;
        }
        .recipe-card {
            background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,.07);
            overflow: hidden; display: flex; flex-direction: column; transition: transform .3s;
        }
        .recipe-card:hover { transform: translateY(-5px); }
        .card-img { height: 180px; position: relative; overflow: hidden; background: #f8f9fa; }
        .card-img img { width: 100%; height: 100%; object-fit: cover; }
        .card-img .no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 4rem; }
        
        .badge-type {
            position: absolute; top: 12px; left: 12px; background: rgba(94,114,228,.9);
            color: #fff; padding: 3px 10px; border-radius: 999px; font-size: .7rem; font-weight: 700;
        }
        .card-body { padding: 18px; flex: 1; display: flex; flex-direction: column; gap: 10px; }
        .card-body h3 { font-family: 'Nunito', sans-serif; font-size: 1rem; font-weight: 800; color: #1e293b; margin: 0; }
        
        .card-footer-actions {
            margin-top: auto; display: flex; gap: 8px; padding-top: 10px; border-top: 1px solid #f1f5f9;
        }
        .btn-detail {
            flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 14px; border-radius: 50px; font-size: .82rem; font-weight: 700;
            background: linear-gradient(135deg, var(--eco-indigo), #4a5fc4); color: #fff; text-decoration: none;
        }
        .btn-remove {
            width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            background: #fff0f0; border: 1.5px solid #fecdd3; color: #e53e3e; cursor: pointer; transition: all .2s;
        }
        .btn-remove:hover { background: #e53e3e; color: #fff; border-color: #e53e3e; }

        /* ═══ EMPTY STATE ══════════════════════════════════════════════ */
        .empty-state { text-align: center; padding: 100px 20px; color: #94a3b8; }
        .empty-state .icon { font-size: 5rem; margin-bottom: 20px; }
        .empty-state h2 { color: #64748b; font-family: 'Nunito', sans-serif; margin-bottom: 16px; }

        /* ═══ FOOTER ═══════════════════════════════════════════════════ */
        .site-footer { background: var(--eco-dark); color: #64748b; text-align: center; padding: 24px; font-size: .85rem; }
>>>>>>> origin/mohamed
    </style>
</head>
<body>

<<<<<<< HEAD
<!-- SVG Symbols -->
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
  <defs>
    <symbol id="heart" viewBox="0 0 24 24"><path fill="currentColor" d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Zm-1.41 7.46L12 18.81l-6.75-6.74a4.28 4.28 0 0 1 3-7.3a4.25 4.25 0 0 1 3 1.25a1 1 0 0 0 1.42 0a4.27 4.27 0 0 1 6 6.05Z"/></symbol>
    <symbol id="cart" viewBox="0 0 24 24"><path fill="currentColor" d="M8.5 19a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 8.5 19ZM19 16H7a1 1 0 0 1 0-2h8.491a3.013 3.013 0 0 0 2.885-2.176l1.585-5.55A1 1 0 0 0 19 5H6.74a3.007 3.007 0 0 0-2.82-2H3a1 1 0 0 0 0 2h.921a1.005 1.005 0 0 1 .962.725l.155.545v.005l1.641 5.742A3 3 0 0 0 7 18h12a1 1 0 0 0 0-2Zm-1.326-9l-1.22 4.274a1.005 1.005 0 0 1-.963.726H8.754l-.255-.892L7.326 7ZM16.5 19a1.5 1.5 0 1 0 1.5 1.5a1.5 1.5 0 0 0-1.5-1.5Z"/></symbol>
    <symbol id="star-solid" viewBox="0 0 15 15"><path fill="currentColor" d="M7.953 3.788a.5.5 0 0 0-.906 0L6.08 5.85l-2.154.33a.5.5 0 0 0-.283.843l1.574 1.613l-.373 2.284a.5.5 0 0 0 .736.518l1.92-1.063l1.921 1.063a.5.5 0 0 0 .736-.519l-.373-2.283l1.574-1.613a.5.5 0 0 0-.283-.844L8.921 5.85l-.968-2.062Z"/></symbol>
    <symbol id="search" viewBox="0 0 24 24"><path fill="currentColor" d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7a7 7 0 0 1-7 7Z"/></symbol>
    <symbol id="lock" viewBox="0 0 24 24"><path fill="currentColor" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></symbol>
    <symbol id="plus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11h-6V5a1 1 0 0 0-2 0v6H5a1 1 0 0 0 0 2h6v6a1 1 0 0 0 2 0v-6h6a1 1 0 0 0 0-2Z"/></symbol>
    <symbol id="minus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11H5a1 1 0 0 0 0 2h14a1 1 0 0 0 0-2Z"/></symbol>
  </defs>
</svg>

<!-- ═══ ECOBYTE UNIFIED HEADER ══════════════════════════════════ -->
<style>
.ecobyte-topbar {
    background: #1a1a2e; padding: 10px 32px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 200;
    box-shadow: 0 2px 12px rgba(0,0,0,0.25);
    font-family: 'Poppins', 'Nunito', sans-serif;
}
.ecobyte-topbar .eco-logo { display: flex; align-items: center; gap: 8px; font-size: 1.2rem; font-weight: 800; text-decoration: none; }
.ecobyte-topbar .eco-logo .eco  { color: #4caf50; }
.ecobyte-topbar .eco-logo .byte { color: #ff6b35; }
.ecobyte-topbar .module-badge { background: rgba(59,130,246,0.2); border: 1px solid rgba(59,130,246,0.4); color: #93c5fd; padding: 4px 14px; border-radius: 999px; font-size: 0.72rem; font-weight: 600; }
.ecobyte-topbar .hub-link { color: #aaa; text-decoration: none; font-size: 0.8rem; font-weight: 500; transition: color .2s; }
.ecobyte-topbar .hub-link:hover { color: #fff; }
.ecobyte-topbar .topbar-right { display: flex; align-items: center; gap: 14px; }
.ecobyte-topbar .user-avatar { width: 36px; height: 36px; background: linear-gradient(135deg,#4caf50,#2196f3); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.9rem; text-decoration: none; transition: transform .2s; }
.ecobyte-topbar .user-avatar:hover { transform: scale(1.1); }
</style>
<nav class="ecobyte-topbar" id="ecobyte-topbar">
    <a href="/2int/index.php" class="eco-logo">
        <span style="font-size:1.4rem;">🌿</span>
        <span class="eco">ECO</span><span class="byte">BYTE</span>
    </a>
    <span class="module-badge">🛒 Boutique Bio</span>
    <div class="topbar-right">
        <a href="/2int/index.php" class="hub-link">← Hub</a>
        <a href="#" class="user-avatar" id="btn-user" title="Mon compte">U</a>
    </div>
</nav>
<!-- ═══════════════════════════════════════════════════════════════ -->

<!-- Header boutique -->
<header>
  <div class="container-fluid">
    <div class="row py-3 border-bottom align-items-center">
      <div class="col-sm-4 col-lg-3">
        <a href="/2int/view/front/index2.php">
          <img src="/2int/view/front/images/logo-ecobite.jpg" alt="EcoBite" class="logo-img">
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
          <h5 class="mb-0">+216 20 190 091</h5>
        </div>
        <a href="/2int/boutique.php?controller=commande&action=panier" class="btn btn-outline-success rounded-pill">
          <svg width="20" height="20"><use xlink:href="#cart"></use></svg> Panier
        </a>
        <!-- Favoris — même style que Panier -->
        <a href="/2int/boutique.php?controller=favoris&action=index" class="btn btn-outline-danger rounded-pill" id="btn-favoris" title="Mes favoris">
          <svg width="20" height="20"><use xlink:href="#heart"></use></svg> Favoris
        </a>
        <!-- ADMIN ACCESS — connecter via branche user (id="btn-admin-boutique") -->
        <!-- <a href="/2int/view/back/pages/marketplace.php" id="btn-admin-boutique">Admin</a> -->
      </div>
    </div>
  </div>
</header>

<div class="container-fluid py-5" style="min-height: 60vh;">
    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3">
        <h2 class="section-title mb-0">❤️ Mes produits favoris</h2>
        <a href="/2int/view/front/index2.php" class="btn btn-outline-dark rounded-pill px-4" id="btn-continuer-achats">Continuer mes achats</a>
    </div>

    <?php if (empty($favoris)): ?>
        <div class="empty-favorites">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="2" class="mb-4">
                <path d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Z"/>
            </svg>
            <h3 class="text-secondary mb-3">Votre liste de favoris est vide</h3>
            <p class="text-muted mb-4 fs-5">Découvrez nos produits frais et ajoutez-les à vos favoris en cliquant sur le cœur.</p>
            <a href="/2int/view/front/index2.php" class="btn btn-success btn-lg px-5 rounded-pill shadow-sm">Découvrir nos produits</a>
        </div>
    <?php else: ?>
        <div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4">
            <?php foreach($favoris as $produit): ?>
            <div class="col" id="favori-card-<?= $produit['id'] ?>">
              <div class="product-item h-100 position-relative shadow-sm rounded-4 border-0 p-3 bg-white">
                <a href="javascript:void(0)" class="btn-wishlist active position-absolute top-0 end-0 m-3 z-1" onclick="removeFromFavoris(<?= $produit['id'] ?>, this)" title="Retirer des favoris">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="red" stroke="red" stroke-width="2">
                    <path d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Z"/>
                  </svg>
                </a>
                <?php $imagePath = getProductImage($produit['nom'] ?? ''); ?>
                <?php if (!empty($imagePath)): ?>
                <figure class="text-center mb-3">
                    <a href="#"><img src="<?= $imagePath ?>" class="img-fluid" style="max-height: 150px; object-fit: contain;"></a>
                </figure>
                <?php endif; ?>
                <h3 class="fs-5 mb-2"><?= htmlspecialchars($produit['nom']) ?></h3>
                <span class="qty d-block text-muted mb-2"><?= $produit['stock'] ?? 'Disponible' ?> unités</span>
                <span class="rating d-flex align-items-center mb-2 text-warning">
                    <svg width="18" height="18" class="me-1"><use xlink:href="#star-solid"></use></svg> 4.5
                </span>
                <span class="price fs-4 fw-bold text-dark d-block mb-3"><?= number_format($produit['prix'],2) ?> DT</span>
                
                <div class="d-flex align-items-center justify-content-between mt-auto">
                  <div class="input-group product-qty me-2">
                    <span class="input-group-btn">
                      <button class="quantity-left-minus btn btn-danger btn-number rounded-circle p-1" data-type="minus" data-id="<?= $produit['id'] ?>" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                        <svg width="12" height="12"><use xlink:href="#minus"></use></svg>
                      </button>
                    </span>
                    <input type="text" name="quantity" class="form-control input-number border-0 bg-transparent text-center fw-bold" value="1" data-id="<?= $produit['id'] ?>" style="width: 40px;">
                    <span class="input-group-btn">
                      <button class="quantity-right-plus btn btn-success btn-number rounded-circle p-1" data-type="plus" data-id="<?= $produit['id'] ?>" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                        <svg width="12" height="12"><use xlink:href="#plus"></use></svg>
                      </button>
                    </span>
                  </div>
                  <button class="add-to-cart-btn btn btn-success rounded-pill d-flex align-items-center px-3" data-id="<?= $produit['id'] ?>">
                    Ajouter <svg width="16" height="16" class="ms-2"><use xlink:href="#cart"></use></svg>
                  </button>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="py-5 bg-white border-top">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
        <div class="footer-menu">
          <img src="/2int/view/front/images/logo-ecobite.jpg" alt="EcoBite" style="height: 40px; margin-bottom: 15px;">
          <p class="text-muted">Manger mieux, vivre mieux.</p>
        </div>
      </div>
      <div class="col-md-2 mb-4 mb-md-0">
        <div class="footer-menu">
            <h5 class="mb-3">À propos</h5>
            <ul class="list-unstyled">
                <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Notre histoire</a></li>
                <li><a href="#" class="text-decoration-none text-muted">Contact</a></li>
            </ul>
        </div>
      </div>
      <div class="col-lg-3">
        <div class="footer-menu">
            <h5 class="mb-3">Newsletter</h5>
            <form class="d-flex mt-3 gap-0">
                <input class="form-control rounded-start bg-light border-0" type="email" placeholder="Votre email">
                <button class="btn btn-dark rounded-end px-4" type="submit">S'abonner</button>
            </form>
        </div>
      </div>
    </div>
  </div>
</footer>
<div id="footer-bottom" class="bg-dark text-white py-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 copyright">
                <p class="mb-0">© 2025 EcoBite - Votre marketplace nutrition</p>
            </div>
        </div>
    </div>
</div>

<script src="/2int/view/front/js/jquery-1.11.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des quantités
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
            window.location.href = `/2int/boutique.php?controller=commande&action=addToPanier&id=${productId}&quantite=${quantity}`;
        });
    });
});

// Fonction pour retirer des favoris avec animation
function removeFromFavoris(produitId, element) {
    // Désactiver visuellement le coeur immédiatement pour le feedback utilisateur
    element.classList.remove('active');
    let svg = element.querySelector('svg');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', '#666');

    // Requête AJAX pour supprimer
    fetch(`/2int/boutique.php?controller=favoris&action=remove&id=${produitId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(function(response) {
            let card = document.getElementById('favori-card-' + produitId);
            if (card) {
                // Animation de disparition
                card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    card.remove();
                    // Si la grille est vide après suppression, recharger pour afficher le placeholder "vide"
                    if (document.querySelectorAll('.product-item').length === 0) {
                        window.location.reload();
                    }
                }, 400);
            }
        })
        .catch(function(error) {
            console.error('Erreur lors de la suppression:', error);
            // Fallback classique en cas d'erreur AJAX
            window.location.href = `/2int/boutique.php?controller=favoris&action=remove&id=${produitId}`;
        });
}
=======
<nav class="ecobyte-topbar">
    <a href="/2int/index.php" class="eco-logo">
        <span>🌿</span> <span class="eco">ECO</span><span class="byte">BYTE</span>
    </a>
    <span class="module-badge">❤️ Mes Favoris</span>
    <div class="topbar-right">
        <a href="/2int/index.php" class="hub-link">← Hub</a>
        <a href="#" class="user-avatar">U</a>
    </div>
</nav>

<header class="site-header">
    <div class="brand"><h1>Mes Recettes Favorites</h1></div>
    <div class="header-actions">
        <a href="/2int/view/front/front.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour aux recettes
        </a>
    </div>
</header>

<main class="content-section">
    <div id="favoris-container">
        <!-- JS will inject cards here -->
        <div class="empty-state" id="empty-state">
            <div class="icon">❤️</div>
            <h2>Vous n'avez pas encore de favoris</h2>
            <p>Cliquez sur le cœur d'une recette pour l'ajouter à votre liste personnelle.</p>
            <a href="/2int/view/front/front.php" class="btn btn-primary rounded-pill px-4 py-2 mt-3" style="background:var(--eco-green); border:none;">
                Découvrir des recettes
            </a>
        </div>
        <div class="recipes-grid" id="favorites-grid" style="display:none;"></div>
    </div>
</main>

<footer class="site-footer">
    <p>© 2026 EcoByte — Cuisine & Recettes</p>
</footer>

<script>
const allRecettes = <?= json_encode(array_values($allRecettes)) ?>;

function renderFavorites() {
    const grid = document.getElementById('favorites-grid');
    const empty = document.getElementById('empty-state');
    grid.innerHTML = '';
    
    // Get favorites from localStorage
    const favorites = [];
    allRecettes.forEach(r => {
        if (localStorage.getItem('fav_' + r.id)) {
            favorites.push(r);
        }
    });

    if (favorites.length === 0) {
        grid.style.display = 'none';
        empty.style.display = 'block';
    } else {
        grid.style.display = 'grid';
        empty.style.display = 'none';
        
        favorites.forEach(r => {
            const card = document.createElement('div');
            card.className = 'recipe-card';
            card.innerHTML = `
                <div class="card-img">
                    ${r.image ? `<img src="${r.image}" alt="${r.nom}">` : '<div class="no-img">🥘</div>'}
                    <span class="badge-type">${r.type || 'Recette'}</span>
                </div>
                <div class="card-body">
                    <h3>${r.nom}</h3>
                    <div class="card-footer-actions">
                        <a href="/2int/view/front/recette-instructions.php?recette_id=${r.id}" class="btn-detail">
                            <i class="fas fa-book-open"></i> Instructions
                        </a>
                        <button class="btn-remove" onclick="removeFavori(${r.id})" title="Retirer des favoris">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });
    }
}

function removeFavori(id) {
    if (confirm('Voulez-vous retirer cette recette de vos favoris ?')) {
        localStorage.removeItem('fav_' + id);
        renderFavorites();
    }
}

document.addEventListener('DOMContentLoaded', renderFavorites);
>>>>>>> origin/mohamed
</script>

</body>
</html>
<<<<<<< HEAD

=======
>>>>>>> origin/mohamed
