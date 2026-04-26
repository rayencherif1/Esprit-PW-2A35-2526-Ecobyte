<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/commande.php';
require_once __DIR__ . '/../../Controller/commandes.Controller.php';

$errors = [];
$old = [
    'nom' => '',
    'prenom' => '',
    'telephone' => '',
    'traitement' => '',
    'quantite' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $traitement = trim($_POST['traitement'] ?? '');
    $quantite = trim($_POST['quantite'] ?? '');

    $old = compact('nom', 'prenom', 'telephone', 'traitement', 'quantite');

    // ✅ Validation NOM
    if (empty($nom)) {
        $errors['nom'] = "Le nom est obligatoire.";
    }

    // ✅ Validation PRENOM
    if (empty($prenom)) {
        $errors['prenom'] = "Le prénom est obligatoire.";
    }

    // ✅ Validation TELEPHONE
    if (empty($telephone)) {
        $errors['telephone'] = "Le téléphone est obligatoire.";
    }

    // ✅ Validation TRAITEMENT
    if (empty($traitement)) {
        $errors['traitement'] = "Le traitement est obligatoire.";
    }

    // ✅ Validation QUANTITE
    if (empty($quantite)) {
        $errors['quantite'] = "La quantité est obligatoire.";
    } elseif (!is_numeric($quantite) || $quantite <= 0) {
        $errors['quantite'] = "Quantité invalide.";
    }

    // ✅ Si aucune erreur
    if (empty($errors)) {
        $commande = new Commande(null, $nom, $prenom, $telephone, $traitement, $quantite);
        $controller = new CommandeC();
        $controller->addCommande($commande);

        header("Location: index.html?success=1");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Commander un traitement</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f4f6f8; font-family: 'DM Sans', sans-serif; }
.form-card {
    background:#fff;
    border-radius:18px;
    padding:2rem;
    max-width:600px;
    margin:40px auto;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
}
h2 { color:#198754; font-weight:700; }
</style>
</head>

<body>

<div class="form-card">

<h2 class="text-center mb-4">💊 Commander un traitement</h2>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success text-center">
    ✅ Commande ajoutée avec succès !
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    ⚠️ Corrige les erreurs
</div>
<?php endif; ?>

<form method="POST">

<!-- NOM -->
<div class="mb-3">
<label>Nom</label>
<input type="text" name="nom"
class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
value="<?= htmlspecialchars($old['nom']) ?>">
<div class="invalid-feedback"><?= $errors['nom'] ?? '' ?></div>
</div>

<!-- PRENOM -->
<div class="mb-3">
<label>Prénom</label>
<input type="text" name="prenom"
class="form-control <?= isset($errors['prenom']) ? 'is-invalid' : '' ?>"
value="<?= htmlspecialchars($old['prenom']) ?>">
<div class="invalid-feedback"><?= $errors['prenom'] ?? '' ?></div>
</div>

<!-- TELEPHONE -->
<div class="mb-3">
<label>Téléphone</label>
<input type="text" name="telephone"
class="form-control <?= isset($errors['telephone']) ? 'is-invalid' : '' ?>"
value="<?= htmlspecialchars($old['telephone']) ?>">
<div class="invalid-feedback"><?= $errors['telephone'] ?? '' ?></div>
</div>

<!-- TRAITEMENT -->
<div class="mb-3">
<label>Traitement</label>
<input type="text" name="traitement"
class="form-control <?= isset($errors['traitement']) ? 'is-invalid' : '' ?>"
value="<?= htmlspecialchars($old['traitement']) ?>">
<div class="invalid-feedback"><?= $errors['traitement'] ?? '' ?></div>
</div>

<!-- QUANTITE -->
<div class="mb-3">
<label>Quantité</label>
<input type="number" name="quantite"
class="form-control <?= isset($errors['quantite']) ? 'is-invalid' : '' ?>"
value="<?= htmlspecialchars($old['quantite']) ?>">
<div class="invalid-feedback"><?= $errors['quantite'] ?? '' ?></div>
</div>

<button type="submit" class="btn btn-success w-100">
Commander
</button>

</form>

</div>

</body>
</html>