<?php
// Cette page sera appelée par FavorisController
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes favoris - EcoBite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h1>❤️ Mes produits favoris</h1>
    <div class="row">
        <?php foreach($favoris as $produit): ?>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5><?= htmlspecialchars($produit['nom']) ?></h5>
                    <p><?= number_format($produit['prix'], 2) ?> €</p>
                    <a href="/marketplace/index.php?controller=favoris&action=remove&id=<?= $produit['id'] ?>" class="btn btn-danger btn-sm">Retirer</a>
                    <a href="/marketplace/index.php?controller=commande&action=addToPanier&id=<?= $produit['id'] ?>" class="btn btn-success btn-sm">Ajouter au panier</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>