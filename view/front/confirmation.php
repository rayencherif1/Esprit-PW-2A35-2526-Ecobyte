<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commande confirmée - EcoBite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .logo-text { font-size: 1.8rem; font-weight: bold; color: #2c5e2e; text-decoration: none; }
        .confirmation-card { background: #f8f9fa; border-radius: 20px; padding: 30px; }
        .btn-continuer { background: #28a745; color: white; padding: 10px 25px; border-radius: 30px; text-decoration: none; }
        .btn-continuer:hover { background: #218838; color: white; }
        .info-section { background: white; border-radius: 15px; padding: 20px; margin-top: 20px; text-align: left; }
        .info-section h5 { color: #2c5e2e; border-left: 4px solid #28a745; padding-left: 15px; margin-bottom: 20px; }
        .info-row { display: flex; padding: 8px 0; border-bottom: 1px solid #eee; }
        .info-label { width: 180px; font-weight: bold; color: #555; }
        .info-value { flex: 1; color: #333; }
        .badge-status { background: #28a745; color: white; padding: 5px 15px; border-radius: 30px; font-size: 14px; }
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
    <div class="confirmation-card">
        <div class="text-center">
            <i class="fa-regular fa-circle-check" style="font-size: 64px; color: #28a745;"></i>
            <h1 class="mt-3 text-success">✅ Merci pour votre commande !</h1>
            <p class="lead">Votre commande a bien été enregistrée et sera traitée dans les plus brefs délais.</p>
            <p class="mb-0">
                <span class="badge-status">📦 Commande n° <strong><?= str_pad($commande['id'], 6, '0', STR_PAD_LEFT) ?></strong></span>
            </p>
            <p class="mt-3">Un email de confirmation a été envoyé à <strong><?= htmlspecialchars($commande['client_email'] ?? '') ?></strong></p>
        </div>
        
        <hr class="my-4">
        
        <!-- Récapitulatif des produits -->
        <div class="info-section">
            <h5><i class="fa-solid fa-basket-shopping"></i> Récapitulatif de votre commande</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sous_total = 0;
                        foreach($produits as $p): 
                            $total_produit = $p['prix'] * $p['quantite'];
                            $sous_total += $total_produit;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nom']) ?></td>
                            <td class="text-center"><?= $p['quantite'] ?></td>
                            <td class="text-end"><?= number_format($p['prix'], 2) ?> DT</td>
                            <td class="text-end"><?= number_format($total_produit, 2) ?> DT</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Sous-total :</td>
                            <td class="text-end"><?= number_format($sous_total, 2) ?> DT</td>
                        </tr>
                        <?php if(($commande['frais_livraison'] ?? 0) > 0): ?>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Frais de livraison :</td>
                            <td class="text-end"><?= number_format($commande['frais_livraison'], 2) ?> DT</td>
                        </tr>
                        <?php endif; ?>
                        <?php if(!empty($commande['code_promo'])): ?>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Code promo (<?= htmlspecialchars($commande['code_promo']) ?>) :</td>
                            <td class="text-end text-danger">-<?= number_format($sous_total - ($commande['total'] - ($commande['frais_livraison'] ?? 0)), 2) ?> DT</td>
                        </tr>
                        <?php endif; ?>
                        <tr class="table-success fw-bold">
                            <td colspan="3" class="text-end fs-5">Total TTC :</td>
                            <td class="text-end fs-5"><?= number_format($commande['total'], 2) ?> DT</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="row">
            <!-- Informations client -->
            <div class="col-md-6">
                <div class="info-section">
                    <h5><i class="fa-solid fa-user"></i> Informations client</h5>
                    <div class="info-row">
                        <div class="info-label">Nom complet :</div>
                        <div class="info-value"><?= htmlspecialchars($commande['civilite'] ?? 'M.') ?> <?= htmlspecialchars($commande['client_prenom'] ?? '') ?> <?= htmlspecialchars($commande['client_nom'] ?? '') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email :</div>
                        <div class="info-value"><?= htmlspecialchars($commande['client_email'] ?? '') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Téléphone :</div>
                        <div class="info-value"><?= htmlspecialchars($commande['client_telephone'] ?? '') ?></div>
                    </div>
                    <?php if(!empty($commande['date_naissance'])): ?>
                    <div class="info-row">
                        <div class="info-label">Date de naissance :</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($commande['date_naissance'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Adresse de livraison -->
            <div class="col-md-6">
                <div class="info-section">
                    <h5><i class="fa-solid fa-truck"></i> Adresse de livraison</h5>
                    <div class="info-row">
                        <div class="info-label">Adresse :</div>
                        <div class="info-value"><?= htmlspecialchars($commande['adresse'] ?? '') ?></div>
                    </div>
                    <?php if(!empty($commande['adresse_complement'])): ?>
                    <div class="info-row">
                        <div class="info-label">Complément :</div>
                        <div class="info-value"><?= htmlspecialchars($commande['adresse_complement']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <div class="info-label">Code postal / Ville :</div>
                        <div class="info-value"><?= htmlspecialchars($commande['code_postal'] ?? '') ?> <?= htmlspecialchars($commande['ville'] ?? '') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Pays :</div>
                        <div class="info-value"><?= htmlspecialchars($commande['pays'] ?? 'Tunisie') ?></div>
                    </div>
                    <?php if(!empty($commande['instructions_livraison'])): ?>
                    <div class="info-row">
                        <div class="info-label">Instructions :</div>
                        <div class="info-value"><?= htmlspecialchars($commande['instructions_livraison']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Livraison & Paiement -->
            <div class="col-md-6">
                <div class="info-section">
                    <h5><i class="fa-solid fa-shipping-fast"></i> Mode de livraison</h5>
                    <div class="info-row">
                        <div class="info-label">Mode :</div>
                        <div class="info-value">
                            <?php 
                            switch($commande['mode_livraison']):
                                case 'standard': echo '📦 Livraison standard'; break;
                                case 'express': echo '⚡ Livraison express'; break;
                                case 'pickup': echo '🏪 Retrait en magasin'; break;
                                default: echo htmlspecialchars($commande['mode_livraison']);
                            endswitch;
                            ?>
                        </div>
                    </div>
                    <?php if(($commande['frais_livraison'] ?? 0) > 0): ?>
                    <div class="info-row">
                        <div class="info-label">Frais :</div>
                        <div class="info-value"><?= number_format($commande['frais_livraison'], 2) ?> DT</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-section">
                    <h5><i class="fa-solid fa-credit-card"></i> Mode de paiement</h5>
                    <div class="info-row">
                        <div class="info-label">Mode :</div>
                        <div class="info-value">
                            <?php 
                            switch($commande['mode_paiement']):
                                case 'carte': echo '💳 Carte bancaire'; break;
                                case 'paypal': echo '📱 PayPal'; break;
                                case 'virement': echo '🏦 Virement bancaire'; break;
                                case 'livraison': echo '💵 Paiement à la livraison'; break;
                                default: echo htmlspecialchars($commande['mode_paiement']);
                            endswitch;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Adresse de facturation si différente -->
        <?php if(!empty($commande['adresse_facturation'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="info-section">
                    <h5><i class="fa-solid fa-file-invoice"></i> Adresse de facturation</h5>
                    <div class="info-row">
                        <div class="info-label">Adresse :</div>
                        <div class="info-value"><?= htmlspecialchars($commande['adresse_facturation']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Code postal / Ville :</div>
                        <div class="info-value"><?= htmlspecialchars($commande['code_postal_facturation'] ?? '') ?> <?= htmlspecialchars($commande['ville_facturation'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Notes supplémentaires -->
        <?php if(!empty($commande['notes'])): ?>
        <div class="info-section">
            <h5><i class="fa-solid fa-pen"></i> Notes supplémentaires</h5>
            <p class="mb-0"><?= nl2br(htmlspecialchars($commande['notes'])) ?></p>
        </div>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <a href="/marketplace/view/front/index2.php" class="btn-continuer d-inline-block">
                <i class="fa-solid fa-arrow-left"></i> Continuer mes achats
            </a>
        </div>
    </div>
</div>

<footer class="py-4 bg-dark text-white text-center mt-5">
    <div class="container">
        <p class="mb-0">© 2025 EcoBite - Votre marketplace nutrition</p>
        <p class="small mb-0 mt-2">Un email de confirmation vous a été envoyé avec tous les détails de votre commande.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>