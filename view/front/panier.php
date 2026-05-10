<?php
session_start();

// Récupérer le panier depuis la session
$panier = $_SESSION['panier'] ?? [];
$produits = [];
$total = 0;

foreach ($panier as $id => $item) {
    $produits[] = [
        'id' => $id,
        'nom' => $item['nom'],
        'prix' => $item['prix'],
        'quantite' => $item['quantite'],
        'sous_total' => $item['prix'] * $item['quantite']
    ];
    $total += $item['prix'] * $item['quantite'];
}

$tunisianCities = [
    'Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan', 'Bizerte',
    'Beja', 'Jendouba', 'Le Kef', 'Siliana', 'Sousse', 'Monastir', 'Mahdia',
    'Sfax', 'Kairouan', 'Kasserine', 'Sidi Bouzid', 'Gabes', 'Medenine',
    'Tataouine', 'Gafsa', 'Tozeur', 'Kebili'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon panier - EcoBite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-table th, .cart-table td { vertical-align: middle; text-align: center; }
        .btn-remove { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 5px; text-decoration: none; }
        .btn-remove:hover { background: #c82333; color: white; }
        .logo-text { font-size: 1.8rem; font-weight: bold; color: #2c5e2e; text-decoration: none; }
        .logo-text:hover { color: #1e4a1e; }
        .error-message { color: red; font-size: 12px; margin-top: 5px; display: none; }
        .error-border { border-color: red !important; }
        .form-step { display: none; }
        .form-step.active { display: block; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 10px; background: #f0f0f0; border-radius: 30px; margin: 0 5px; }
        .step.active { background: #2c5e2e; color: white; }
        .step.completed { background: #28a745; color: white; }
        .order-summary { background: #f8f9fa; padding: 20px; border-radius: 15px; position: sticky; top: 20px; }
        .delivery-option, .payment-option { border: 2px solid #ddd; border-radius: 10px; padding: 15px; margin-bottom: 10px; cursor: pointer; transition: all 0.3s; }
        .delivery-option:hover, .payment-option:hover { border-color: #2c5e2e; background: #f0fff0; }
        .delivery-option.selected, .payment-option.selected { border-color: #28a745; background: #e8f5e9; }
        .price-badge { float: right; font-weight: bold; color: #2c5e2e; }
        .form-section { background: white; padding: 20px; border-radius: 15px; margin-bottom: 20px; border: 1px solid #eee; }
        .form-section h5 { color: #2c5e2e; margin-bottom: 20px; border-bottom: 2px solid #2c5e2e; padding-bottom: 10px; }
        .required-star { color: red; }
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
        <div class="row">
            <!-- Colonne panier -->
            <div class="col-lg-7">
                <div class="table-responsive">
                    <table class="table cart-table">
                        <thead class="table-light">
                            <tr><th>Produit</th><th>Prix unitaire</th><th>Quantité</th><th>Total</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($produits as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nom']) ?></td>
                                <td><?= number_format($p['prix'], 2) ?> DT</td>
                                <td><?= $p['quantite'] ?></td>
                                <td><?= number_format($p['sous_total'], 2) ?> DT</td>
                                <td><a href="/marketplace/index.php?controller=commande&action=removeFromPanier&id=<?= $p['id'] ?>" class="btn-remove" onclick="return confirm('Retirer ce produit ?')">Retirer</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Sous-total :</td>
                                <td colspan="2" id="subtotal"><?= number_format($total, 2) ?> DT</td>
                            </tr>
                            <tr class="fw-bold" id="deliveryFeeRow" style="display: none;">
                                <td colspan="3" class="text-end">Frais de livraison :</td>
                                <td colspan="2" id="deliveryFee">0.00 DT</td>
                            </tr>
                            <tr class="fw-bold bg-success text-white">
                                <td colspan="3" class="text-end">Total TTC :</td>
                                <td colspan="2" id="grandTotal"><?= number_format($total, 2) ?> DT</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Colonne formulaire -->
            <div class="col-lg-5">
                <div class="order-summary">
                    <h4 class="mb-3">📦 Finaliser ma commande</h4>
                    
                    <!-- Indicateur d'étapes -->
                    <div class="step-indicator">
                        <div class="step active" id="step1-ind">1. Identité & Adresse</div>
                        <div class="step" id="step2-ind">2. Livraison & Validation</div>
                    </div>
                    
                    <div id="globalError" class="alert alert-danger" style="display: none;"></div>
                    
                    <form id="checkoutForm" method="POST" action="/marketplace/index.php?controller=commande&action=checkout" onsubmit="return validateAndSubmit()">
                        
                        <!-- ÉTAPE 1 : Informations personnelles et adresse -->
                        <div id="step1" class="form-step active">
                            <div class="form-section">
                                <h5><i class="fas fa-user"></i> Informations personnelles</h5>
                                <div class="mb-3">
                                    <label class="form-label">Civilité *</label>
                                    <select name="civilite" id="civilite" class="form-control">
                                        <option value="M.">M.</option>
                                        <option value="Mme">Mme</option>
                                        <option value="Mlle">Mlle</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nom *</label>
                                    <input type="text" name="client_nom" id="client_nom" class="form-control">
                                    <div class="invalid-feedback">Le nom est obligatoire</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Prénom *</label>
                                    <input type="text" name="client_prenom" id="client_prenom" class="form-control">
                                    <div class="invalid-feedback">Le prénom est obligatoire</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="client_email" id="client_email" class="form-control">
                                    <div class="invalid-feedback">Email valide obligatoire</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Téléphone *</label>
                                    <input type="tel" name="client_telephone" id="client_telephone" class="form-control" placeholder="12 345 678">
                                    <div class="invalid-feedback">Téléphone valide obligatoire (8 chiffres)</div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h5><i class="fas fa-home"></i> Adresse de livraison</h5>
                                <div class="mb-3">
                                    <label class="form-label">Adresse *</label>
                                    <input type="text" name="adresse" id="adresse" class="form-control" placeholder="Numéro et rue">
                                    <div class="invalid-feedback">L'adresse est obligatoire</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Complément d'adresse</label>
                                    <input type="text" name="adresse_complement" id="adresse_complement" class="form-control" placeholder="Appartement, étage, etc.">
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Code postal *</label>
                                        <input type="text" name="code_postal" id="code_postal" class="form-control" placeholder="75001">
                                        <div class="invalid-feedback">Code postal obligatoire</div>
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Ville *</label>
                                        <select name="ville" id="ville" class="form-control">
                                            <option value="">Sélectionner une ville tunisienne</option>
                                            <?php foreach($tunisianCities as $city): ?>
                                                <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">La ville est obligatoire</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pays *</label>
                                    <select name="pays" id="pays" class="form-control">
                                        <option value="Tunisie" selected>🇹🇳 Tunisie</option>
                                    </select>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-primary w-100" onclick="goToStep2()">Continuer →</button>
                        </div>

                        <!-- ÉTAPE 2 : Livraison + Paiement + Validation -->
                        <div id="step2" class="form-step">
                            <div class="form-section">
                                <h5><i class="fas fa-shipping-fast"></i> Mode de livraison</h5>
                                <div id="deliveryOptions">
                                    <div class="delivery-option selected" data-price="0" data-method="standard" onclick="selectDelivery(this, 'standard', 0)">
                                        <strong>📦 Livraison standard</strong>
                                        <span class="price-badge">Gratuite</span>
                                        <small class="d-block text-muted">Sous 3-5 jours ouvrés</small>
                                    </div>
                                    <div class="delivery-option" data-price="5.90" data-method="express" onclick="selectDelivery(this, 'express', 5.90)">
                                        <strong>⚡ Livraison express</strong>
                                        <span class="price-badge">+5,90 DT</span>
                                        <small class="d-block text-muted">Sous 24h-48h</small>
                                    </div>
                                    <div class="delivery-option" data-price="0" data-method="pickup" onclick="selectDelivery(this, 'pickup', 0)">
                                        <strong>🏪 Retrait en magasin</strong>
                                        <span class="price-badge">Gratuit</span>
                                        <small class="d-block text-muted">À retirer dans notre boutique</small>
                                    </div>
                                </div>
                                <input type="hidden" name="mode_livraison" id="mode_livraison" value="standard">
                                <input type="hidden" name="frais_livraison" id="frais_livraison" value="0">
                            </div>
                            
                            <div class="form-section">
                                <h5><i class="fas fa-credit-card"></i> Mode de paiement</h5>
                                <div id="paymentOptions">
                                    <div class="payment-option selected" data-method="carte" onclick="selectPayment(this, 'carte')">
                                        <strong>💳 Carte bancaire</strong>
                                        <small class="d-block text-muted">Paiement sécurisé CB/Visa/Mastercard</small>
                                    </div>
                                    <div class="payment-option" data-method="paypal" onclick="selectPayment(this, 'paypal')">
                                        <strong>📱 PayPal</strong>
                                        <small class="d-block text-muted">Paiement via votre compte PayPal</small>
                                    </div>
                                    <div class="payment-option" data-method="virement" onclick="selectPayment(this, 'virement')">
                                        <strong>🏦 Virement bancaire</strong>
                                        <small class="d-block text-muted">Virement à réception de la facture</small>
                                    </div>
                                    <div class="payment-option" data-method="livraison" onclick="selectPayment(this, 'livraison')">
                                        <strong>💵 Paiement à la livraison</strong>
                                        <small class="d-block text-muted">Espèces ou carte à la réception</small>
                                    </div>
                                </div>
                                <input type="hidden" name="mode_paiement" id="mode_paiement" value="carte">
                            </div>
                            
                            <div class="form-section">
                                <h5><i class="fas fa-tag"></i> Code promo</h5>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="code_promo" name="code_promo" placeholder="Votre code promo">
                                    <button class="btn btn-outline-success" type="button" onclick="applyPromo()">Appliquer</button>
                                </div>
                                <div id="promoMessage" class="small"></div>
                            </div>
                            
                            <div class="form-section">
                                <h5><i class="fas fa-notes-medical"></i> Informations complémentaires</h5>
                                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Allergies, régime alimentaire, créneau horaire de livraison souhaité..."></textarea>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="cgv" required>
                                <label class="form-check-label">
                                    J'accepte les <a href="#">conditions générales de vente</a> *
                                </label>
                            </div>
                            
                            <div class="d-flex gap-2 mt-4">
                                <button type="button" class="btn btn-secondary" onclick="goToStep1()">← Retour</button>
                                <button type="submit" class="btn btn-success flex-grow-1">✅ Valider ma commande</button>
                            </div>
                        </div>
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
    let deliveryPrice = 0;
    let subtotal = <?= $total ?>;
    let promoDiscount = 0;
    
    // Navigation entre étapes
    function goToStep2() {
        if (validateStep1()) {
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.add('active');
            document.getElementById('step1-ind').classList.remove('active');
            document.getElementById('step1-ind').classList.add('completed');
            document.getElementById('step2-ind').classList.add('active');
            updateTotals();
        }
    }
    
    function goToStep1() {
        document.getElementById('step2').classList.remove('active');
        document.getElementById('step1').classList.add('active');
        document.getElementById('step2-ind').classList.remove('active');
        document.getElementById('step1-ind').classList.add('active');
        document.getElementById('step1-ind').classList.remove('completed');
    }
    
    // Validation étape 1
    function validateStep1() {
        let isValid = true;
        let errors = [];
        
        let nom = document.getElementById('client_nom').value.trim();
        let prenom = document.getElementById('client_prenom').value.trim();
        let email = document.getElementById('client_email').value.trim();
        let tel = document.getElementById('client_telephone').value.trim();
        let adresse = document.getElementById('adresse').value.trim();
        let cp = document.getElementById('code_postal').value.trim();
        let ville = document.getElementById('ville').value.trim();
        
        // Nom
        if (nom === '') {
            showError('client_nom', 'Le nom est obligatoire');
            isValid = false;
        } else { clearError('client_nom'); }
        
        // Prénom
        if (prenom === '') {
            showError('client_prenom', 'Le prénom est obligatoire');
            isValid = false;
        } else { clearError('client_prenom'); }
        
        // Email
        if (email === '') {
            showError('client_email', 'L\'email est obligatoire');
            isValid = false;
        } else if (!email.match(/^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/)) {
            showError('client_email', 'Email invalide');
            isValid = false;
        } else { clearError('client_email'); }
        
        // Téléphone (8 chiffres pour Tunisie)
        if (tel === '') {
            showError('client_telephone', 'Le téléphone est obligatoire');
            isValid = false;
        } else if (!tel.match(/^[0-9]{8}$/)) {
            showError('client_telephone', 'Téléphone invalide (8 chiffres)');
            isValid = false;
        } else { clearError('client_telephone'); }
        
        // Adresse
        if (adresse === '') {
            showError('adresse', 'L\'adresse est obligatoire');
            isValid = false;
        } else { clearError('adresse'); }
        
        // Code postal
        if (cp === '') {
            showError('code_postal', 'Le code postal est obligatoire');
            isValid = false;
        } else { clearError('code_postal'); }
        
        // Ville
        if (ville === '') {
            showError('ville', 'La ville est obligatoire');
            isValid = false;
        } else { clearError('ville'); }
        
        return isValid;
    }
    
    function showError(fieldId, message) {
        let field = document.getElementById(fieldId);
        field.classList.add('error-border');
        let feedback = field.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.style.display = 'block';
            feedback.innerHTML = message;
        }
    }
    
    function clearError(fieldId) {
        let field = document.getElementById(fieldId);
        field.classList.remove('error-border');
        let feedback = field.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.style.display = 'none';
        }
    }
    
    // Sélection livraison
    function selectDelivery(element, method, price) {
        document.querySelectorAll('.delivery-option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        document.getElementById('mode_livraison').value = method;
        document.getElementById('frais_livraison').value = price;
        deliveryPrice = price;
        updateTotals();
    }
    
    // Sélection paiement
    function selectPayment(element, method) {
        document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        document.getElementById('mode_paiement').value = method;
    }
    
    // Mise à jour des totaux
    function updateTotals() {
        let total = subtotal + deliveryPrice - promoDiscount;
        let deliveryFeeRow = document.getElementById('deliveryFeeRow');
        let deliveryFeeSpan = document.getElementById('deliveryFee');
        let grandTotalSpan = document.getElementById('grandTotal');
        
        if (deliveryPrice > 0) {
            deliveryFeeRow.style.display = 'table-row';
            deliveryFeeSpan.innerHTML = deliveryPrice.toFixed(2) + ' DT';
        } else {
            deliveryFeeRow.style.display = 'none';
        }
        
        grandTotalSpan.innerHTML = total.toFixed(2) + ' DT';
    }
    
    // Application code promo
    function applyPromo() {
        let code = document.getElementById('code_promo').value.trim().toUpperCase();
        let promoMessage = document.getElementById('promoMessage');
        
        if (code === 'ECOBITE10') {
            promoDiscount = subtotal * 0.10;
            promoMessage.innerHTML = '<span class="text-success">✓ Code promo appliqué : -10%</span>';
        } else if (code === 'BIENVENUE20') {
            promoDiscount = subtotal * 0.20;
            promoMessage.innerHTML = '<span class="text-success">✓ Code promo appliqué : -20%</span>';
        } else if (code !== '') {
            promoMessage.innerHTML = '<span class="text-danger">✗ Code promo invalide</span>';
            promoDiscount = 0;
        } else {
            promoDiscount = 0;
            promoMessage.innerHTML = '';
        }
        
        updateTotals();
    }
    
    // Validation finale avant soumission
    function validateAndSubmit() {
        let globalError = document.getElementById('globalError');
        globalError.style.display = 'none';
        globalError.innerHTML = '';
        
        let errors = [];
        
        // Valider étape 1
        if (!validateStep1()) {
            errors.push('Veuillez remplir correctement vos informations personnelles et adresse');
            goToStep1();
        }
        
        // Vérifier CGV
        let cgv = document.getElementById('cgv').checked;
        if (!cgv) {
            errors.push('Vous devez accepter les conditions générales de vente');
        }
        
        if (errors.length > 0) {
            globalError.innerHTML = errors.join('<br>');
            globalError.style.display = 'block';
            
            // Si erreur CGV, rester sur étape 2
            if (!cgv) {
                document.getElementById('step2').classList.add('active');
                document.getElementById('step1').classList.remove('active');
            }
            
            return false;
        }
        
        return true;
    }
</script>

</body>
</html>