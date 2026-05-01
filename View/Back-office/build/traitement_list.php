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
    <style>
        /* Styles pour le modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            animation: slideDown 0.3s ease;
        }
        
        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 12px 12px 0 0;
        }
        
        .modal-header.warning {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .modal-body {
            padding: 24px;
            font-size: 16px;
            line-height: 1.5;
        }
        
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-radius: 0 0 12px 12px;
        }
        
        .btn-cancel {
            background-color: #e5e7eb;
            color: #374151;
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
            font-weight: 500;
        }
        
        .btn-cancel:hover {
            background-color: #d1d5db;
        }
        
        .btn-confirm {
            background-color: #ef4444;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
            font-weight: 500;
        }
        
        .btn-confirm:hover {
            background-color: #dc2626;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
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
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm" id="successMessage">
                <?php 
                    if ($_GET['success'] === 'added') echo '✔ Traitement ajouté avec succès';
                    if ($_GET['success'] === 'updated') echo '✔ Traitement modifié avec succès';
                    if ($_GET['success'] === 'deleted') echo '✔ Traitement supprimé avec succès';
                ?>
            </div>
            <script>
                setTimeout(function() {
                    var msg = document.getElementById('successMessage');
                    if (msg) msg.style.display = 'none';
                }, 3000);
            </script>
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

                                        <button type="button"
                                            onclick="showDeleteModal(<?= $t['id_traitement'] ?>, '<?= htmlspecialchars($t['nom_traitement']) ?>', '<?= $id_allergie ?>')"
                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition inline-block"
                                            title="Supprimer">
                                            🗑️ Supprimer
                                        </button>
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

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header warning">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <span>⚠️</span> Confirmation de suppression
                </h3>
            </div>
            <div class="modal-body" id="modalBody">
                Êtes-vous sûr de vouloir supprimer ce traitement ?
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="hideDeleteModal()">Annuler</button>
                <button class="btn-confirm" id="confirmDeleteBtn">Supprimer</button>
            </div>
        </div>
    </div>

    <script>
        let traitementToDelete = null;
        let allergieId = null;

        function showDeleteModal(id, nom, idAllergie) {
            traitementToDelete = id;
            allergieId = idAllergie;
            
            // Personnaliser le message avec le nom du traitement
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="text-3xl">🗑️</div>
                    <div>
                        <p class="font-semibold mb-2">Supprimer le traitement :</p>
                        <p class="text-gray-700 bg-gray-100 p-2 rounded">"${nom}"</p>
                        <p class="text-red-600 text-sm mt-3">⚠️ Cette action est irréversible.</p>
                    </div>
                </div>
            `;
            
            // Afficher le modal
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'block';
            
            // Empêcher le scrolling de la page
            document.body.style.overflow = 'hidden';
            
            // Configurer le bouton de confirmation
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            
            // Supprimer les anciens événements
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            // Ajouter le nouvel événement
            newConfirmBtn.addEventListener('click', function() {
                let url = 'traitement_delete.php?id=' + traitementToDelete;
                if (allergieId) {
                    url += '&id_allergie=' + encodeURIComponent(allergieId);
                }
                window.location.href = url;
            });
        }

        function hideDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            traitementToDelete = null;
            allergieId = null;
        }

        // Fermer le modal si on clique en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                hideDeleteModal();
            }
        }

        // Fermer avec la touche Echap
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideDeleteModal();
            }
        });
    </script>

</body>
</html>