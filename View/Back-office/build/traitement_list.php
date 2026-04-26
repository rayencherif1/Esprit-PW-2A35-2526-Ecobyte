<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/traitement.php';
require_once __DIR__ . '/../../../Controller/traitement.Controller.php';

$controller = new TraitementC();

// Récupérer l'ID de l'allergie pour filtrer (optionnel)
$id_allergie = $_GET['id_allergie'] ?? null;

if ($id_allergie) {
    $traitements = $controller->listTraitementByAllergie($id_allergie);
} else {
    $traitements = $controller->listTraitement();
}

// S'assurer que c'est un tableau
if (!is_array($traitements)) {
    $traitements = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Traitements</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">

    <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-blue-600">💊 Traitements</h2>

            </div>

            <a href="traitement_add.php<?= $id_allergie ? '?id_allergie=' . urlencode($id_allergie) : '' ?>"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                ➕ Ajouter un traitement
            </a>
        </div>

        <!-- Message succès -->
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">
                <?php 
                    if ($_GET['success'] === 'added') echo '✔ Traitement ajouté avec succès';
                    if ($_GET['success'] === 'updated') echo '✔ Traitement modifié avec succès';
                    if ($_GET['success'] === 'deleted') echo '✔ Traitement supprimé avec succès';
                ?>
            </div>
        <?php endif; ?>

        <!-- Message erreur -->
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
                ❌ Une erreur est survenue
            </div>
        <?php endif; ?>

        <!-- Tableau -->
        <div class="overflow-x-auto">
            <table class="w-full border text-sm">

                <thead class="bg-gray-200 text-left">
                    <tr>
                        <th class="p-3">Nom du traitement</th>
                        <th class="p-3">Conseils</th>
                        <th class="p-3">Interdictions</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($traitements) > 0): ?>
                        <?php foreach ($traitements as $t): ?>
                            <tr class="border-t hover:bg-gray-50">
                                
                                <!-- Nom traitement -->
                                <td class="p-3 font-semibold text-gray-800">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">💊</span>
                                        <?= htmlspecialchars($t['nom_traitement']) ?>
                                    </div>
                                </td>

                                <!-- Conseils -->
                                <td class="p-3 text-gray-600 max-w-xs">
                                    <?= htmlspecialchars(substr($t['conseils'] ?? '', 0, 80)) ?>
                                    <?= isset($t['conseils']) && strlen($t['conseils']) > 80 ? '...' : '' ?>
                                </td>

                                <!-- Interdictions -->
                                <td class="p-3 text-gray-600 max-w-xs">
                                    <?= htmlspecialchars(substr($t['interdiction'] ?? '', 0, 80)) ?>
                                    <?= isset($t['interdiction']) && strlen($t['interdiction']) > 80 ? '...' : '' ?>
                                </td>

                                <!-- Actions -->
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="flex gap-2 justify-center">
                                        <a href="traitement_edit.php?id=<?= $t['id_traitement'] ?><?= $id_allergie ? '&id_allergie=' . urlencode($id_allergie) : '' ?>"
                                            class="bg-yellow-400 text-gray-800 px-3 py-1 rounded hover:bg-yellow-500 transition inline-block"
                                            title="Modifier">
                                            ✏️ Modifier
                                        </a>

                                        <a href="traitement_delete.php?id=<?= $t['id_traitement'] ?><?= $id_allergie ? '&id_allergie=' . urlencode($id_allergie) : '' ?>"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce traitement ?')"
                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition inline-block"
                                            title="Supprimer">
                                            🗑️ Supprimer
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center p-6 text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-4xl">📭</span>
                                    <p>Aucun traitement trouvé</p>
                                    <a href="traitement_add.php<?= $id_allergie ? '?id_allergie=' . urlencode($id_allergie) : '' ?>" 
                                        class="text-blue-600 hover:underline text-sm">
                                        Ajouter votre premier traitement
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>

        <!-- Pied de page avec statistiques -->
        <?php if (count($traitements) > 0): ?>
            <div class="mt-4 text-sm text-gray-500 border-t pt-3">
                Total : <span class="font-semibold"><?= count($traitements) ?></span> traitement(s)
            </div>
        <?php endif; ?>

    </div>

</body>
</html>