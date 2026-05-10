<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/traitement.php';
require_once __DIR__ . '/../../../Controller/traitement.Controller.php';

$controller = new TraitementC();
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: traitement_list.php');
    exit();
}

$traitementData = $controller->getTraitementById($id);
if (!$traitementData) {
    header('Location: traitement_list.php');
    exit();
}

$id_allergie = $_GET['id_allergie'] ?? $traitementData['id_allergie'] ?? null;

// Stocker les valeurs actuelles
$old = [
    'nom_traitement' => $traitementData['nom_traitement'],
    'conseils' => $traitementData['conseils'],
    'interdiction' => $traitementData['interdiction'],
    'id_allergie' => $traitementData['id_allergie']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nom_traitement = trim($_POST['nom_traitement'] ?? '');
    $conseils = trim($_POST['conseils'] ?? '');
    $interdiction = trim($_POST['interdiction'] ?? '');
    $new_id_allergie = trim($_POST['id_allergie'] ?? $traitementData['id_allergie']);

    // Mettre à jour les valeurs pour l'affichage
    $old = compact('nom_traitement', 'conseils', 'interdiction', 'new_id_allergie');

    // Validation Nom
    if (empty($nom_traitement)) {
        $errors['nom_traitement'] = "Le nom du traitement est obligatoire.";
    } elseif (mb_strlen($nom_traitement) < 3) {
        $errors['nom_traitement'] = "Le nom doit contenir au moins 3 caractères.";
    }

    // Validation Conseils
    if (empty($conseils)) {
        $errors['conseils'] = "Les conseils sont obligatoires.";
    }

    // Validation Interdictions
    if (empty($interdiction)) {
        $errors['interdiction'] = "Les interdictions sont obligatoires.";
    }

    // Validation ID Allergie
    if (!empty($new_id_allergie) && !is_numeric($new_id_allergie)) {
        $errors['id_allergie'] = "L'ID de l'allergie doit être un nombre valide.";
    }

    if (empty($errors)) {
        try {
            $traitement = new Traitement($id, $nom_traitement, $conseils, $interdiction, $new_id_allergie);
            $result = $controller->updateTraitement($traitement, $id);
            
            if ($result) {
                $redirect = 'traitement_list.php?success=updated';
                if ($id_allergie) {
                    $redirect .= '&id_allergie=' . urlencode($id_allergie);
                }
                header('Location: ' . $redirect);
                exit();
            } else {
                $errors['general'] = "Erreur lors de la modification du traitement.";
            }
        } catch (Exception $e) {
            $errors['general'] = "Une erreur est survenue : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Traitement</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .err { color: #dc2626; font-size: 0.85rem; margin-top: 4px; }
        .alert { background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #991b1b; }
        .required::after { content: " *"; color: #dc2626; }
        label { font-weight: 600; display: block; margin-bottom: 5px; }
    </style>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">

    <h2 class="text-xl font-bold text-blue-600 mb-4 flex items-center gap-2">
        ✏️ Modifier le traitement
    </h2>

    <?php if (isset($errors['general'])): ?>
        <div class="alert">
            ❌ <?= htmlspecialchars($errors['general']) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        
        <!-- ID Traitement (lecture seule) -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">ID Traitement</label>
            <input type="text" 
                   value="<?= htmlspecialchars($id) ?>" 
                   disabled
                   class="w-full border p-2 rounded bg-gray-100">
        </div>

        <!-- Nom traitement -->
        <div class="mb-4">
            <label for="nom_traitement" class="required block text-sm font-medium text-gray-700 mb-1">Nom du traitement</label>
            <input type="text" 
                   id="nom_traitement"
                   name="nom_traitement" 
                   value="<?= htmlspecialchars($old['nom_traitement'] ?? '') ?>"
                   placeholder="Ex: Antihistaminique H1"
                   class="w-full border p-2 rounded <?= isset($errors['nom_traitement']) ? 'border-red-500' : '' ?>">
            <?php if (isset($errors['nom_traitement'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['nom_traitement']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Conseils -->
        <div class="mb-4">
            <label for="conseils" class="required block text-sm font-medium text-gray-700 mb-1">Conseils</label>
            <textarea id="conseils"
                      name="conseils" 
                      rows="4"
                      placeholder="Conseils pour le patient..."
                      class="w-full border p-2 rounded <?= isset($errors['conseils']) ? 'border-red-500' : '' ?>"><?= htmlspecialchars($old['conseils'] ?? '') ?></textarea>
            <?php if (isset($errors['conseils'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['conseils']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Interdictions -->
        <div class="mb-4">
            <label for="interdiction" class="required block text-sm font-medium text-gray-700 mb-1">Interdictions</label>
            <textarea id="interdiction"
                      name="interdiction" 
                      rows="4"
                      placeholder="Ce qu'il faut éviter..."
                      class="w-full border p-2 rounded <?= isset($errors['interdiction']) ? 'border-red-500' : '' ?>"><?= htmlspecialchars($old['interdiction'] ?? '') ?></textarea>
            <?php if (isset($errors['interdiction'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['interdiction']) ?></div>
            <?php endif; ?>
        </div>

        <!-- ID Allergie (optionnel) -->
        <div class="mb-4">
            <label for="id_allergie" class="block text-sm font-medium text-gray-700 mb-1">ID Allergie associée</label>
            <input type="number" 
                   id="id_allergie"
                   name="id_allergie" 
                   value="<?= htmlspecialchars($old['id_allergie'] ?? '') ?>"
                   placeholder="ID de l'allergie associée"
                   class="w-full border p-2 rounded <?= isset($errors['id_allergie']) ? 'border-red-500' : '' ?>">
            <p class="text-xs text-gray-500 mt-1">💡 Laissez l'ID actuel ou modifiez-le pour associer à une autre allergie</p>
            <?php if (isset($errors['id_allergie'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['id_allergie']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Boutons -->
        <div class="flex gap-3 mt-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex-1">
                💾 Enregistrer les modifications
            </button>

            <a href="traitement_list.php<?= $id_allergie ? '?id_allergie=' . urlencode($id_allergie) : '' ?>" 
               class="bg-gray-300 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-400 transition text-center flex-1">
                ❌ Annuler
            </a>
        </div>

    </form>

    <!-- Informations supplémentaires -->
    <div class="mt-6 pt-4 border-t text-xs text-gray-500">
        <p>Traitement ID: <?= htmlspecialchars($id) ?></p>
        <p>Allergie actuelle ID: <?= htmlspecialchars($traitementData['id_allergie']) ?></p>
    </div>

</div>

</body>
</html>