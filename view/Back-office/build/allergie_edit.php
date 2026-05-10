<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/allergie.php';
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';

$controller = new AllergieC();
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { 
    header('Location: allergies_list.php'); 
    exit(); 
}

$allergieData = $controller->getAllergieById($id);
if (!$allergieData) { 
    header('Location: allergies_list.php'); 
    exit(); 
}

$old = [
    'nom' => $allergieData['nom_allergie'] ?? $allergieData['nom'] ?? '',
    'description' => $allergieData['description'] ?? '',
    'gravite' => $allergieData['gravite'] ?? '',
    'symptomes' => $allergieData['symptomes'] ?? ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $gravite = trim($_POST['gravite'] ?? '');
    $symptomes = trim($_POST['symptomes'] ?? '');

    // Sauvegarder les anciennes valeurs
    $old = compact('nom', 'description', 'gravite', 'symptomes');

    // Validation
    if (empty($nom)) {
        $errors['nom'] = "Le nom est obligatoire.";
    } elseif (mb_strlen($nom) < 3) {
        $errors['nom'] = "Le nom doit contenir au moins 3 caractères.";
    }

    if (empty($description)) {
        $errors['description'] = "La description est obligatoire.";
    }

    $gravitesValides = ['faible', 'moyenne', 'grave'];
    if (empty($gravite)) {
        $errors['gravite'] = "La gravité est obligatoire.";
    } elseif (!in_array($gravite, $gravitesValides, true)) {
        $errors['gravite'] = "Valeur de gravité invalide.";
    }

    if (empty($symptomes)) {
        $errors['symptomes'] = "Les symptômes sont obligatoires.";
    }

    if (empty($errors)) {
        try {
            $allergie = new Allergie($id, $nom, $description, $gravite, $symptomes);
            $result = $controller->updateAllergie($allergie, $id);
            
            if ($result) {
                header('Location: allergies_list.php?success=updated');
                exit();
            } else {
                $errors['general'] = "Erreur lors de la modification de l'allergie.";
            }
        } catch (Exception $e) {
            $errors['general'] = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier allergie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .err { color: #dc2626; font-size: 0.85rem; margin-top: 4px; }
        .alert { background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #991b1b; }
        .required::after { content: " *"; color: #dc2626; }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

<div class="bg-white p-6 rounded-xl shadow w-full max-w-2xl">

    <h2 class="text-xl font-bold text-blue-600 mb-4 flex items-center gap-2">
        ✏️ Modifier l'allergie
    </h2>

    <!-- Message d'erreur général -->
    <?php if (isset($errors['general'])): ?>
        <div class="alert">
            ❌ <?= htmlspecialchars($errors['general']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">

        <!-- ID (affichage seulement) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ID Allergie</label>
            <input type="text" 
                   value="<?= htmlspecialchars($id) ?>" 
                   disabled
                   class="w-full border p-2 rounded bg-gray-100">
        </div>

        <!-- Nom -->
        <div>
            <label for="nom" class="required block text-sm font-medium text-gray-700 mb-1">Nom</label>
            <input type="text" 
                   id="nom" 
                   name="nom"
                   value="<?= htmlspecialchars($old['nom']) ?>"
                   placeholder="Ex: Arachides, Lactose, Pollen de bouleau..."
                   class="w-full border p-2 rounded <?= isset($errors['nom']) ? 'border-red-500' : '' ?>"
                   required>
            <?php if (isset($errors['nom'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['nom']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="required block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" 
                      name="description" 
                      rows="4"
                      placeholder="Décrivez l'allergie en détail..."
                      class="w-full border p-2 rounded <?= isset($errors['description']) ? 'border-red-500' : '' ?>"
                      required><?= htmlspecialchars($old['description']) ?></textarea>
            <?php if (isset($errors['description'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['description']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Gravité -->
        <div>
            <label for="gravite" class="required block text-sm font-medium text-gray-700 mb-1">Gravité</label>
            <select id="gravite" 
                    name="gravite" 
                    class="w-full border p-2 rounded <?= isset($errors['gravite']) ? 'border-red-500' : '' ?>"
                    required>
                <option value="">-- Choisir la gravité --</option>
                <option value="faible" <?= ($old['gravite'] == "faible") ? "selected" : "" ?>>🟢 Faible</option>
                <option value="moyenne" <?= ($old['gravite'] == "moyenne") ? "selected" : "" ?>>🟠 Moyenne</option>
                <option value="grave" <?= ($old['gravite'] == "grave") ? "selected" : "" ?>>🔴 Grave</option>
            </select>
            <?php if (isset($errors['gravite'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['gravite']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Symptômes -->
        <div>
            <label for="symptomes" class="required block text-sm font-medium text-gray-700 mb-1">Symptômes</label>
            <textarea id="symptomes" 
                      name="symptomes" 
                      rows="4"
                      placeholder="Ex: Urticaire, gonflement, difficultés respiratoires, nausées..."
                      class="w-full border p-2 rounded <?= isset($errors['symptomes']) ? 'border-red-500' : '' ?>"
                      required><?= htmlspecialchars($old['symptomes']) ?></textarea>
            <div class="info-text text-xs text-gray-500 mt-1">💡 Séparez les symptômes par des virgules pour une meilleure lisibilité</div>
            <?php if (isset($errors['symptomes'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['symptomes']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Boutons -->
        <div class="flex gap-3 pt-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex-1">
                💾 Enregistrer les modifications
            </button>

            <a href="allergies_list.php" class="border border-gray-300 px-6 py-2 rounded-lg text-center hover:bg-gray-50 transition flex-1">
                ❌ Annuler
            </a>
        </div>

    </form>

    <!-- Informations supplémentaires -->
    <div class="mt-6 pt-4 border-t text-xs text-gray-500">
        <p>ID Allergie : <?= htmlspecialchars($id) ?></p>
        <?php if (isset($allergieData['created_at'])): ?>
            <p>Créé le : <?= htmlspecialchars($allergieData['created_at']) ?></p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>