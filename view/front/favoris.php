<?php
// Cette page sera appelée par FavorisController
// Assurer que la session est démarrée si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Mes favoris - EcoBite</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/marketplace/view/front/css/vendor.css">
    <link rel="stylesheet" href="/marketplace/view/front/style.css">
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
    <symbol id="lock" viewBox="0 0 24 24"><path fill="currentColor" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></symbol>
    <symbol id="plus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11h-6V5a1 1 0 0 0-2 0v6H5a1 1 0 0 0 0 2h6v6a1 1 0 0 0 2 0v-6h6a1 1 0 0 0 0-2Z"/></symbol>
    <symbol id="minus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11H5a1 1 0 0 0 0 2h14a1 1 0 0 0 0-2Z"/></symbol>
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
        <a href="/marketplace/index.php?controller=commande&action=panier" class="btn btn-outline-success rounded-pill">
          <svg width="20" height="20"><use xlink:href="#cart"></use></svg> Panier
        </a>
        <a href="/marketplace/index.php?controller=favoris&action=index" class="btn btn-danger rounded-pill" title="Mes favoris">
          ❤️ Favoris
        </a>
        <a href="/marketplace/view/back/pages/marketplace.php" class="admin-icon" title="Administration">
          <svg width="20" height="20"><use xlink:href="#lock"></use></svg>
        </a>
      </div>
    </div>
  </div>
</header>

<div class="container-fluid py-5" style="min-height: 60vh;">
    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3">
        <h2 class="section-title mb-0">❤️ Mes produits favoris</h2>
        <a href="/marketplace/view/front/index2.php" class="btn btn-outline-dark rounded-pill px-4">Continuer mes achats</a>
    </div>

    <?php if (empty($favoris)): ?>
        <div class="empty-favorites">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="2" class="mb-4">
                <path d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Z"/>
            </svg>
            <h3 class="text-secondary mb-3">Votre liste de favoris est vide</h3>
            <p class="text-muted mb-4 fs-5">Découvrez nos produits frais et ajoutez-les à vos favoris en cliquant sur le cœur.</p>
            <a href="/marketplace/view/front/index2.php" class="btn btn-success btn-lg px-5 rounded-pill shadow-sm">Découvrir nos produits</a>
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
                <figure class="text-center mb-3">
                    <a href="#"><img src="/marketplace/view/front/images/thumb-tomatoes.png" class="img-fluid" style="max-height: 150px; object-fit: contain;"></a>
                </figure>
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
          <img src="/marketplace/view/front/images/logo-ecobite.jpg" alt="EcoBite" style="height: 40px; margin-bottom: 15px;">
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

<script src="/marketplace/view/front/js/jquery-1.11.0.min.js"></script>
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
            window.location.href = `/marketplace/index.php?controller=commande&action=addToPanier&id=${productId}&quantite=${quantity}`;
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
    fetch(`/marketplace/index.php?controller=favoris&action=remove&id=${produitId}`)
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
            window.location.href = `/marketplace/index.php?controller=favoris&action=remove&id=${produitId}`;
        });
}
</script>

</body>
</html>