<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commande confirmée - EcoBite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .logo-text { font-size: 1.8rem; font-weight: bold; color: #2c5e2e; text-decoration: none; }
        .confirmation-card { background: #f8f9fa; border-radius: 20px; padding: 30px; }
        .btn-continuer { background: #28a745; color: white; padding: 10px 25px; border-radius: 30px; text-decoration: none; }
        .btn-continuer:hover { background: #218838; color: white; }
    </style>
</head>
<body>

<header>
    <div class="container-fluid">
        <div class="row py-3 border-bottom align-items-center">
            <div class="col-sm-4">
                <a href="/marketplace/view/front/index.php" class="logo-text">🌿 EcoBite</a>
            </div>
        </div>
    </div>
</header>

<div class="container my-5">
    <div class="confirmation-card text-center">
        <i class="fa-solid fa-circle-check" style="font-size: 64px; color: #28a745;"></i>
        <h1 class="mt-3">✅ Merci pour votre commande !</h1>
        <p class="lead">Votre commande n° <strong><?= $commande['id'] ?? '---' ?></strong> a bien été enregistrée.</p>
        <p>Un email de confirmation a été envoyé à <strong><?= htmlspecialchars($commande['client_email'] ?? '') ?></strong></p>
        
        <hr class="my-4">
        
        <h4>Récapitulatif</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr><th>Produit</th><th>Quantité</th><th>Prix unitaire</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach($produits as $p): 
                        $sous_total = $p['prix'] * $p['quantite'];
                        $total += $sous_total;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nom']) ?></td>
                        <td><?= $p['quantite'] ?></td>
                        <td><?= number_format($p['prix'], 2) ?> €</td>
                        <td><?= number_format($sous_total, 2) ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold"><td colspan="3">Total</td><td><?= number_format($total, 2) ?> €</td></tr>
                </tfoot>
            </table>
        </div>
        
        <a href="/marketplace/view/front/index.php" class="btn-continuer mt-4 d-inline-block">← Continuer mes achats</a>
    </div>
</div>

<footer class="py-4 bg-dark text-white text-center mt-5">
    <div class="container">
        <p class="mb-0">© 2025 EcoBite - Votre marketplace nutrition</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>