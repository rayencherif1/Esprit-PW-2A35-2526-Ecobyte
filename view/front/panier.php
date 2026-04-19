<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon panier - EcoBite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/vendor.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-table th, .cart-table td { vertical-align: middle; text-align: center; }
        .btn-remove { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 5px; text-decoration: none; }
        .btn-remove:hover { background: #c82333; color: white; }
        .btn-checkout { background: #28a745; color: white; padding: 10px 20px; border-radius: 30px; border: none; }
        .btn-checkout:hover { background: #218838; color: white; }
        .logo-text {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c5e2e;
            text-decoration: none;
        }
        .logo-text:hover {
            color: #1e4a1e;
        }
        .error-message { color: red; font-size: 12px; margin-top: 5px; display: none; }
        .error-border { border-color: red !important; }
    </style>
</head>
<body>

<header>
    <div class="container-fluid">
        <div class="row py-3 border-bottom align-items-center">
            <div class="col-sm-4">
                <a href="/marketplace/view/front/index2.php" class="logo-text">🌿 EcoBite</a>
            </div>
            <div class="col-sm-8 text-end">
                <a href="/marketplace/view/front/index2.php" class="btn btn-outline-secondary rounded-pill">← Continuer mes achats</a>
            </div>
        </div>
    </div>
</header>

<div class="container my-5">
    <h1 class="text-center mb-5">🛒 Mon panier</h1>

    <?php if (empty($produits)): ?>
        <div class="alert alert-info text-center">
            Votre panier est vide.
            <br><br>
            <a href="/marketplace/view/front/index2.php" class="btn btn-primary rounded-pill px-4">Découvrir nos produits</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table cart-table">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th>
                        <th>Prix unitaire</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($produits as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nom']) ?></td>
                        <td><?= number_format($p['prix'], 2) ?> €</td>
                        <td><?= $p['quantite'] ?></td>
                        <td><?= number_format($p['sous_total'], 2) ?> €</td>
                        <td>
                            <a href="/marketplace/index.php?controller=commande&action=removeFromPanier&id=<?= $p['id'] ?>" class="btn-remove" onclick="return confirm('Retirer ce produit ?')">Retirer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Total :</td>
                        <td colspan="2"><?= number_format($total, 2) ?> €</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
        <?php endif; ?>

        <div class="row mt-5">
            <div class="col-md-6 mx-auto">
                <div class="card p-4 shadow-sm">
                    <h4 class="mb-3 text-center">📦 Informations de livraison</h4>
                    <div id="checkoutError" class="error-message"></div>
                    <form id="checkoutForm" method="POST" action="/marketplace/index.php?controller=commande&action=checkout" onsubmit="return validateCheckout()">
                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="client_nom" id="client_nom" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="client_email" id="client_email" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-checkout w-100">Valider ma commande</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<footer class="py-4 bg-dark text-white text-center mt-5">
    <div class="container">
        <p class="mb-0">© 2025 EcoBite - Votre marketplace nutrition</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function validateCheckout() {
        let nom = document.getElementById('client_nom').value.trim();
        let email = document.getElementById('client_email').value.trim();
        let errorDiv = document.getElementById('checkoutError');
        
        // Réinitialiser
        errorDiv.style.display = 'none';
        errorDiv.innerHTML = '';
        
        let errors = [];
        
        if (nom === '') {
            errors.push('Le nom est obligatoire');
        }
        
        if (email === '') {
            errors.push('L\'email est obligatoire');
        } else if (!email.includes('@') || !email.includes('.')) {
            errors.push('Email invalide (doit contenir @ et .)');
        }
        
        if (errors.length > 0) {
            errorDiv.innerHTML = '❌ ' + errors.join('<br>❌ ');
            errorDiv.style.display = 'block';
            return false;
        }
        
        return true;
    }
</script>

</body>
</html>