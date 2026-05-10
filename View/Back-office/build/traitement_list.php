<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/traitement.php';
require_once __DIR__ . '/../../../Controller/traitement.Controller.php';
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';

$controller = new TraitementC();

// Récupérer l'ID de l'allergie pour filtrer (optionnel)
$id_allergie = $_GET['id_allergie'] ?? null;

// Récupérer le nom de l'allergie pour l'affichage
$allergie_nom = null;
if ($id_allergie) {
    $allergieController = new AllergieC();
    $allergieInfo = $allergieController->getAllergieById($id_allergie);
    if ($allergieInfo) {
        $allergie_nom = $allergieInfo['nom'];
    }
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
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            animation: slideDown 0.3s ease;
            max-height: 85vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 12px 12px 0 0;
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }
        
        .modal-header.warning {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .modal-header.medical {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .modal-header.medical h3 {
            color: white;
        }
        
        .modal-body {
            padding: 24px;
            font-size: 14px;
            line-height: 1.6;
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-radius: 0 0 12px 12px;
            position: sticky;
            bottom: 0;
            background: white;
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

        .loading-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

        .btn-analysis {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-analysis:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        /* Styles pour l'analyse IA */
        .ia-thinking {
            text-align: center;
            padding: 40px;
        }
        .ia-brain {
            font-size: 48px;
            animation: pulse 1s ease-in-out infinite;
            display: inline-block;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
        }
        .ia-progress {
            width: 80%;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            margin: 20px auto;
            overflow: hidden;
        }
        .ia-progress-fill {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        .ia-step {
            font-size: 13px;
            color: #667eea;
            margin-top: 12px;
        }
        
        /* Résultats IA */
        .ia-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid #e5e7eb;
        }
        .ia-card-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ia-item {
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }
        .ia-item:last-child { border-bottom: none; }
        .ia-item-nom { font-weight: 600; color: #1f2937; }
        .ia-item-detail { font-size: 12px; color: #6b7280; margin-top: 4px; }
        .ia-badge-danger { background: #dc2626; color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; }
        .ia-badge-warning { background: #f59e0b; color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; }
        .ia-badge-info { background: #3b82f6; color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; }
        .ia-alert-critical { background: #fef2f2; border-left: 4px solid #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .ia-resume { background: #f0fdf4; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        .ia-footer { background: #f9fafb; border-radius: 12px; padding: 12px; text-align: center; font-size: 11px; color: #6b7280; }
        
        /* Badge score */
        .score-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .score-high { background: #10b981; color: white; }
        .score-medium { background: #f59e0b; color: white; }
        .score-low { background: #ef4444; color: white; }
    </style>
</head>

<body class="bg-gray-100 p-6">

    <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-blue-600">💊 Traitements</h2>
                <?php if ($allergie_nom): ?>
                    <p class="text-sm text-gray-600 mt-1">
                        <span class="font-semibold">🎯 Allergie contextuelle :</span> 
                        <?= htmlspecialchars($allergie_nom) ?>
                    </p>
                <?php endif; ?>
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
                        <th class="p-3 text-center" style="min-width: 250px;">Actions</th>
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
                                    <div class="flex gap-2 justify-center flex-wrap">
                                        <button type="button"
                                            onclick="openAnalysisModal(<?= $t['id_traitement'] ?>, '<?= htmlspecialchars($t['nom_traitement']) ?>', <?= $id_allergie ? $id_allergie : 'null' ?>)"
                                            class="btn-analysis px-3 py-1 rounded inline-flex items-center gap-1">
                                            🤖 Analyser
                                        </button>

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

    <!-- Modal d'analyse médicale -->
    <div id="analysisModal" class="modal">
        <div class="modal-content">
            <div class="modal-header medical">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <span>🩺</span> Analyse Médicale Intelligente
                    <span class="text-xs ml-2 opacity-75">Expert Allergologie</span>
                </h3>
            </div>
            <div class="modal-body" id="analysisBody">
                <div class="text-center py-8">
                    <div class="loading-spinner"></div>
                    <p class="mt-4 text-gray-600">Analyse en cours...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="hideAnalysisModal()">Fermer</button>
            </div>
        </div>
    </div>

    <script>
        let traitementToDelete = null;
        let allergieId = null;

        function showDeleteModal(id, nom, idAllergie) {
            traitementToDelete = id;
            allergieId = idAllergie;
            
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
            
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
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

        // Fonction d'analyse médicale
        async function openAnalysisModal(traitementId, traitementNom, allergieId) {
            const modal = document.getElementById('analysisModal');
            const analysisBody = document.getElementById('analysisBody');
            
            // Afficher l'interface de chargement
            analysisBody.innerHTML = `
                <div class="ia-thinking">
                    <div class="ia-brain">🧠</div>
                    <div style="margin-top: 20px; font-weight: 500;">Analyse en cours</div>
                    <div class="ia-progress">
                        <div class="ia-progress-fill" id="iaProgressFill"></div>
                    </div>
                    <div class="ia-step" id="iaStepText">🔍 Récupération des données...</div>
                </div>
            `;
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Animation de progression
            const steps = [
                "🔍 Récupération des données du traitement...",
                "🧬 Analyse de compatibilité allergique...",
                "🩺 Évaluation des risques et interactions...",
                "📊 Génération des recommandations..."
            ];
            let stepIndex = 0;
            const progressFill = document.getElementById('iaProgressFill');
            const stepText = document.getElementById('iaStepText');
            
            const interval = setInterval(() => {
                if (stepIndex < steps.length && progressFill && stepText) {
                    stepText.innerHTML = steps[stepIndex];
                    const progress = ((stepIndex + 1) / steps.length) * 100;
                    progressFill.style.width = progress + '%';
                    stepIndex++;
                }
            }, 600);
            
            try {
                // Appel à l'API d'analyse intégrée
                const response = await fetch('analyse_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_traitement: traitementId,
                        id_allergie: allergieId || null
                    })
                });
                
                clearInterval(interval);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Afficher les résultats
                displayAnalysisResults(data, traitementNom);
                
            } catch (error) {
                clearInterval(interval);
                analysisBody.innerHTML = `
                    <div class="text-center py-8 text-red-600">
                        <p class="text-4xl mb-4">⚠️</p>
                        <p class="font-semibold">Erreur d'analyse</p>
                        <p class="text-sm mt-2">${error.message}</p>
                        <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded" onclick="hideAnalysisModal()">Fermer</button>
                    </div>
                `;
            }
        }
        
        function displayAnalysisResults(data, traitementNom) {
            const analysisBody = document.getElementById('analysisBody');
            
            // Couleurs selon niveau d'alerte
            const alerteConfig = {
                critique: { bg: '#fef2f2', border: '#dc2626', icon: '🚨', text: 'ALERTE CRITIQUE', class: 'score-low' },
                warning: { bg: '#fffbeb', border: '#f59e0b', icon: '⚠️', text: 'PRUDENCE REQUISE', class: 'score-medium' },
                normal: { bg: '#f0fdf4', border: '#10b981', icon: '✅', text: 'COMPATIBLE', class: 'score-high' },
                info: { bg: '#eff6ff', border: '#3b82f6', icon: 'ℹ️', text: 'INFORMATION', class: 'score-high' }
            };
            
            const alerte = data.alerte || 'normal';
            const config = alerteConfig[alerte] || alerteConfig.normal;
            const compat = data.compatibilite || {};
            const score = compat.score || 50;
            
            // Classe CSS pour le score
            let scoreClass = 'score-high';
            if (score < 50) scoreClass = 'score-low';
            else if (score < 75) scoreClass = 'score-medium';
            
            // Construction des interactions
            let interactionsHtml = '';
            if (data.interactions_medicamenteuses && data.interactions_medicamenteuses.length > 0) {
                data.interactions_medicamenteuses.forEach(i => {
                    interactionsHtml += `
                        <div class="ia-item">
                            <div style="flex:1">
                                <div class="ia-item-nom">⚠️ ${escapeHtml(i.avec)}</div>
                                <div class="ia-item-detail">${escapeHtml(i.effet)}</div>
                                <div class="text-xs text-blue-600 mt-1">💡 ${escapeHtml(i.conduite_a_tenir)}</div>
                            </div>
                        </div>
                    `;
                });
            } else {
                interactionsHtml = '<div class="ia-item"><span>✅</span><span>Aucune interaction majeure détectée</span></div>';
            }
            
            // Construction des risques
            let risquesHtml = '';
            if (data.risques && data.risques.length > 0) {
                data.risques.forEach(r => {
                    let badgeClass = r.probabilite === 'haute' ? 'ia-badge-danger' : 
                                    (r.probabilite === 'moyenne' ? 'ia-badge-warning' : 'ia-badge-info');
                    let badgeText = r.probabilite === 'haute' ? 'RISQUE ÉLEVÉ' : 
                                    (r.probabilite === 'moyenne' ? 'RISQUE MODÉRÉ' : 'RISQUE FAIBLE');
                    risquesHtml += `
                        <div class="ia-item">
                            <div style="flex:1">
                                <div class="ia-item-nom">⚠️ ${escapeHtml(r.type)}</div>
                                <div class="ia-item-detail">${escapeHtml(r.description)}</div>
                            </div>
                            <span class="${badgeClass}">${badgeText}</span>
                        </div>
                    `;
                });
            }
            
            // Construction des effets secondaires
            let effetsHtml = '';
            if (data.effets_secondaires && data.effets_secondaires.length > 0) {
                data.effets_secondaires.forEach(e => {
                    effetsHtml += `<div class="ia-item"><span>📋</span><span>${escapeHtml(e)}</span></div>`;
                });
            }
            
            // Construction des recommandations
            let recommandationsHtml = '';
            if (data.recommandations_personnalisees && data.recommandations_personnalisees.length > 0) {
                data.recommandations_personnalisees.forEach(r => {
                    recommandationsHtml += `<div class="ia-item"><span>✓</span><span>${escapeHtml(r)}</span></div>`;
                });
            }
            
            // Construction de la surveillance
            let surveillanceHtml = '';
            if (data.conseils_surveillance && data.conseils_surveillance.length > 0) {
                data.conseils_surveillance.forEach(s => {
                    surveillanceHtml += `<div class="ia-item"><span>👁️</span><span>${escapeHtml(s)}</span></div>`;
                });
            }
            
            // Alerte critique
            let alertHtml = '';
            if (alerte === 'critique') {
                alertHtml = `
                    <div class="ia-alert-critical">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <span style="font-size:24px;">🚨</span>
                            <div>
                                <strong style="color:#dc2626;">ALERTE CRITIQUE - CONTRE-INDICATION</strong>
                                <p style="font-size:12px;color:#991b1b;margin-top:4px;">Consultation médicale OBLIGATOIRE avant toute utilisation</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Section urgence
            let urgenceHtml = '';
            if (data.urgence && data.urgence.necessaire) {
                urgenceHtml = `
                    <div class="ia-card" style="background:#fef3c7; border-color:#f59e0b;">
                        <div class="ia-card-title" style="color:#92400e;">
                            <span>🚨 URGENCE</span>
                        </div>
                        <p class="text-sm" style="color:#92400e;">${escapeHtml(data.urgence.quoi_faire)}</p>
                    </div>
                `;
            }
            
            analysisBody.innerHTML = `
                <div>
                    <!-- En-tête -->
                    <div class="ia-card" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white; text-align: center;">
                        <div style="font-size:40px;">${config.icon}</div>
                        <div style="margin-top:8px; font-weight:bold;">${escapeHtml(traitementNom)}</div>
                        <div style="background: ${config.bg}; color: ${config.border}; padding: 4px 12px; border-radius: 20px; display: inline-block; margin-top: 10px; font-size: 12px; font-weight: bold;">
                            ${config.text}
                        </div>
                    </div>
                    
                    ${alertHtml}
                    
                    <!-- Score de compatibilité -->
                    <div class="ia-card">
                        <div class="ia-card-title">
                            <span>📊 Compatibilité</span>
                            <span class="${scoreClass} score-badge">${score}%</span>
                        </div>
                        <div style="background: #e5e7eb; border-radius: 10px; height: 8px; margin: 10px 0; overflow: hidden;">
                            <div style="background: ${score >= 70 ? '#10b981' : (score >= 50 ? '#f59e0b' : '#ef4444')}; width: ${score}%; height: 100%; transition: width 0.5s ease;"></div>
                        </div>
                        <p class="text-sm text-gray-600">${escapeHtml(compat.explication || '')}</p>
                    </div>
                    
                    <!-- Risques -->
                    ${risquesHtml ? `
                    <div class="ia-card">
                        <div class="ia-card-title">
                            <span>⚠️ Risques identifiés</span>
                            <span class="ia-badge-warning">${data.risques?.length || 0}</span>
                        </div>
                        ${risquesHtml}
                    </div>
                    ` : ''}
                    
                    <!-- Interactions -->
                    <div class="ia-card">
                        <div class="ia-card-title">
                            <span>🔄 Interactions médicamenteuses</span>
                            <span class="ia-badge-info">${data.interactions_medicamenteuses?.length || 0}</span>
                        </div>
                        ${interactionsHtml}
                    </div>
                    
                    <!-- Effets secondaires -->
                    <div class="ia-card">
                        <div class="ia-card-title">
                            <span>📋 Effets secondaires possibles</span>
                        </div>
                        ${effetsHtml}
                    </div>
                    
                    <!-- Recommandations -->
                    <div class="ia-card">
                        <div class="ia-card-title">
                            <span>💡 Recommandations personnalisées</span>
                        </div>
                        ${recommandationsHtml}
                    </div>
                    
                    <!-- Surveillance -->
                    <div class="ia-card" style="background:#eff6ff;">
                        <div class="ia-card-title">
                            <span>👀 Conseils de surveillance</span>
                        </div>
                        ${surveillanceHtml}
                    </div>
                    
                    ${urgenceHtml}
                    
                    <!-- Synthèse patient -->
                    <div class="ia-resume">
                        <div style="font-weight:600;margin-bottom:8px;">📋 Synthèse pour le patient</div>
                        <p style="font-size:13px;line-height:1.5;">${escapeHtml(data.synthese_patient || data.resume || 'Suivez les recommandations médicales.')}</p>
                    </div>
                    
                    <!-- Footer -->
                    <div class="ia-footer">
                        <div>🩺 Analyse médicale intelligente</div>
                        <div style="font-size:10px;margin-top:4px;">
                            ${data.meta?.analyse_realisee_le || ''} • Version ${data.meta?.version || '1.0'}
                        </div>
                    </div>
                </div>
            `;
        }
        
        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function hideAnalysisModal() {
            const modal = document.getElementById('analysisModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Fermer le modal si on clique en dehors
        window.onclick = function(event) {
            const deleteModal = document.getElementById('deleteModal');
            const analysisModal = document.getElementById('analysisModal');
            if (event.target === deleteModal) {
                hideDeleteModal();
            }
            if (event.target === analysisModal) {
                hideAnalysisModal();
            }
        }

        // Fermer avec la touche Echap
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideDeleteModal();
                hideAnalysisModal();
            }
        });
    </script>

</body>
</html>