<?php
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';
require_once __DIR__ . '/../../../Model/allergie.php';

$controller = new AllergieC();
$allergies = $controller->listAllergie();

// Récupérer l'ID pour le retour (optionnel)
$id_allergie = $_GET['id_allergie'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Allergies</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">

    <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-blue-600">🌿 Liste des Allergies</h2>
                <p class="text-sm text-gray-500 mt-1">Gestion des allergies et leurs traitements associés</p>
            </div>

            <a href="allergie_add.php"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                ➕ Nouvelle Allergie
            </a>
        </div>

        <!-- Message succès -->
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">
                <?php 
                    if ($_GET['success'] === 'added') echo '✔ Allergie ajoutée avec succès';
                    if ($_GET['success'] === 'updated') echo '✔ Allergie modifiée avec succès';
                    if ($_GET['success'] === 'deleted') echo '✔ Allergie supprimée avec succès';
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
                        <th class="p-3">Nom</th>
                        <th class="p-3">Description</th>
                        <th class="p-3">Gravité</th>
                        <th class="p-3">Symptômes</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($allergies && is_array($allergies) && count($allergies) > 0): ?>
                        <?php foreach ($allergies as $a): ?>
                            <tr class="border-t hover:bg-gray-50">
                                
                                <td class="p-3 font-semibold text-gray-800">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🌿</span>
                                        <?= htmlspecialchars($a['nom_allergie'] ?? $a['nom'] ?? 'N/A') ?>
                                    </div>
                                  </td>

                                <td class="p-3 text-gray-600 max-w-xs">
                                    <?= htmlspecialchars(substr($a['description'] ?? '', 0, 80)) ?>
                                    <?= isset($a['description']) && strlen($a['description']) > 80 ? '...' : '' ?>
                                  </td>

                                <td class="p-3">
                                    <?php 
                                        $gravite = $a['gravite'] ?? 'non définie';
                                        $color = match(strtolower($gravite)) {
                                            'faible' => 'text-green-600 bg-green-50',
                                            'moyenne' => 'text-orange-600 bg-orange-50',
                                            'grave' => 'text-red-600 bg-red-50',
                                            default => 'text-gray-600 bg-gray-50'
                                        };
                                    ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $color ?>">
                                        <?= ucfirst(htmlspecialchars($gravite)) ?>
                                    </span>
                                  </td>

                                <td class="p-3 text-gray-600 max-w-xs">
                                    <?php 
                                        $symptomes = $a['symptomes'] ?? '';
                                        $symptomes_short = substr($symptomes, 0, 60);
                                        echo htmlspecialchars($symptomes_short);
                                        echo strlen($symptomes) > 60 ? '...' : '';
                                    ?>
                                  </td>

                                <!-- Actions -->
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="flex gap-2 justify-center">
                                        <a href="allergie_edit.php?id=<?= $a['id_allergie'] ?>"
                                            class="bg-yellow-400 text-gray-800 px-3 py-1 rounded hover:bg-yellow-500 transition inline-block"
                                            title="Modifier">
                                            ✏️ Modifier
                                        </a>
                                        <a href="allergie_delete.php?id=<?= $a['id_allergie'] ?>"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette allergie ? Tous les traitements associés seront également supprimés.')"
                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition inline-block"
                                            title="Supprimer">
                                            🗑️ Supprimer
                                        </a>
                                        <a href="traitement_list.php?id_allergie=<?= $a['id_allergie'] ?>"
                                            class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition inline-block"
                                            title="Gérer les traitements">
                                            💊 Traitements
                                        </a>
                                    </div>
                                  </td>

                              </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                         <tr>
                            <td colspan="5" class="text-center p-6 text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-4xl">📭</span>
                                    <p>Aucune allergie trouvée</p>
                                    <a href="allergie_add.php" 
                                        class="text-blue-600 hover:underline text-sm">
                                        Ajouter votre première allergie
                                    </a>
                                </div>
                              </td>
                         </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>

        <!-- Pied de page avec statistiques -->
        <?php if ($allergies && is_array($allergies) && count($allergies) > 0): ?>
            <div class="mt-4 text-sm text-gray-500 border-t pt-3 flex justify-between items-center">
                <div>
                    Total : <span class="font-semibold"><?= count($allergies) ?></span> allergie(s)
                </div>
                <div class="flex gap-2">
                    <a href="allergie_add.php" class="text-blue-600 hover:underline text-sm">
                        ➕ Nouvelle allergie
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>