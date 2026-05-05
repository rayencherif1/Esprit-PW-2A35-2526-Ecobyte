<?php
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';

$controller = new AllergieC();

// Si c'est une requête AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    $search = $_GET['search'] ?? null;
    $gravite = $_GET['gravite'] ?? null;

    $allergies = $controller->listAllergie($gravite, $search);

    header('Content-Type: application/json');
    echo json_encode($allergies);
    exit;
}

// Chargement initial
$allergies = $controller->listAllergie();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Allergies - Analyse IA Dynamique</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .resultat-item {
            transition: all 0.2s ease;
        }
        .resultat-item:hover {
            background-color: #f3f4f6;
            transform: translateX(5px);
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 0.5s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Styles pour le modal Analyse IA */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 1000px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .modal-header {
            padding: 20px 24px;
            border-radius: 20px 20px 0 0;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .ia-card {
            background: white;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
            transition: transform 0.2s;
        }
        .ia-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .ia-card-title {
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .btn-analyse {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-analyse:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-7xl mx-auto bg-white p-6 rounded-xl shadow">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-violet-600 bg-clip-text text-transparent">
                🌿 Liste des Allergies
            </h2>
            <p class="text-sm text-gray-500 mt-1">Gestion complète + Analyse IA dynamique</p>
        </div>
        <div class="flex gap-2">
            <a href="stat.php" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition flex items-center gap-2">
                📊 Statistiques
            </a>
            <a href="export_pdf.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition flex items-center gap-2">
                📄 Export PDF
            </a>
            <a href="allergie_add.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                ➕ Nouvelle Allergie
            </a>
        </div>
    </div>

    <!-- RECHERCHE -->
    <div class="mb-6 bg-gradient-to-r from-gray-50 to-gray-100 p-4 rounded-lg">
        <div class="relative">
            <input type="text" id="searchInput" placeholder="🔍 Rechercher une allergie..." 
                   class="w-full border-2 px-4 py-3 rounded-lg text-lg focus:outline-none focus:border-purple-500">
            <div id="loadingIndicator" class="absolute right-3 top-3" style="display:none;">
                <div class="loading"></div>
            </div>
        </div>

        <div class="mt-3 flex gap-3 items-center flex-wrap">
            <label class="text-sm font-semibold text-gray-700">Filtrer par gravité :</label>
            <select id="graviteFilter" class="border px-3 py-2 rounded text-sm">
                <option value="">Toutes les gravités</option>
                <option value="faible">🟢 Faible</option>
                <option value="moyenne">🟠 Moyenne</option>
                <option value="grave">🔴 Grave</option>
            </select>
            <button id="resetBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition text-sm">
                🔄 Réinitialiser
            </button>
        </div>
    </div>

    <div id="searchInfo" class="mb-3 text-sm text-gray-600"></div>

    <!-- TABLE -->
    <div class="overflow-x-auto">
        <div id="resultsContainer">
            <table class="w-full border text-sm">
                <thead class="bg-gradient-to-r from-gray-200 to-gray-100">
                    <tr>
                        <th class="p-3 text-left">Nom</th>
                        <th class="p-3 text-left">Description</th>
                        <th class="p-3 text-left">Gravité</th>
                        <th class="p-3 text-left">Symptômes</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allergies)): ?>
                        <tr>
                            <td colspan="5" class="text-center p-8 text-gray-500">
                                Aucune allergie trouvée
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($allergies as $a): ?>
                        <tr class="border-t hover:bg-gray-50 resultat-item transition">
                            <td class="p-3 font-semibold">🌿 <?= htmlspecialchars($a['nom']) ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars(substr($a['description'], 0, 80)) ?>...</td>
                            <td class="p-3">
                                <?php
                                $g = strtolower($a['gravite']);
                                $color = match ($g) {
                                    'faible' => 'text-green-600 bg-green-100',
                                    'moyenne' => 'text-orange-600 bg-orange-100',
                                    'grave' => 'text-red-600 bg-red-100',
                                    default => 'text-gray-600 bg-gray-100'
                                };
                                ?>
                                <span class="px-2 py-1 rounded text-xs font-semibold <?= $color ?>">
                                    <?= ucfirst($a['gravite']) ?>
                                </span>
                            </td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars(substr($a['symptomes'], 0, 60)) ?>...</td>
                            <td class="p-3 text-center">
                                <div class="flex gap-2 justify-center flex-wrap">
                                    
                                    <!-- BOUTON ANALYSE IA DYNAMIQUE -->
                                    <button onclick="launchDynamicAnalysis(<?= $a['id_allergie'] ?>, '<?= htmlspecialchars(addslashes($a['nom'])) ?>')"
                                            class="btn-analyse text-white px-4 py-2 rounded-lg transition flex items-center gap-2 text-sm font-medium">
                                        🤖 Analyse IA
                                    </button>

                                    <a href="allergie_edit.php?id=<?= $a['id_allergie'] ?>" class="bg-yellow-400 px-3 py-1 rounded hover:bg-yellow-500 transition">✏️</a>
                                    
                                    <a href="allergie_delete.php?id=<?= $a['id_allergie'] ?>" 
                                       onclick="return confirm('Supprimer cette allergie ?')" 
                                       class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">🗑️</a>
                                    
                                    <a href="traitement_list.php?id_allergie=<?= $a['id_allergie'] ?>" 
                                       class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">💊</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL ANALYSE DYNAMIQUE -->
<div id="dynamicAnalysisModal" class="modal">
    <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold flex items-center gap-3">
                    🧠 Analyse IA Experte - <span id="modalAllergieNom"></span>
                </h3>
                <button onclick="hideDynamicAnalysisModal()" class="text-white hover:text-gray-200 text-2xl">&times;</button>
            </div>
            <p class="text-sm opacity-90 mt-1">✨ Analyse unique générée à chaque demande</p>
        </div>
        <div class="modal-body p-6" id="dynamicAnalysisBody"></div>
        <div class="modal-footer p-4 border-t flex justify-end gap-3 bg-gray-50 rounded-b-2xl">
            <button onclick="hideDynamicAnalysisModal()" 
                    class="px-6 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg transition">
                Fermer
            </button>
            <button onclick="exportDynamicReport()" 
                    class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition flex items-center gap-2">
                📄 Exporter en PDF
            </button>
            <button onclick="regenerateAnalysis()" 
                    class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition flex items-center gap-2">
                🔄 Nouvelle Analyse
            </button>
        </div>
    </div>
</div>

<script>
let currentAllergieId = null;
let currentAllergieNom = null;

// Fonctions de recherche existantes
let typingTimer;
const searchInput = document.getElementById('searchInput');
const graviteFilter = document.getElementById('graviteFilter');
const resetBtn = document.getElementById('resetBtn');
const loadingIndicator = document.getElementById('loadingIndicator');
const resultsContainer = document.getElementById('resultsContainer');
const searchInfo = document.getElementById('searchInfo');

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function searchAllergies() {
    const search = searchInput.value.trim();
    const gravite = graviteFilter.value;
    
    loadingIndicator.style.display = 'block';
    
    let url = window.location.href.split('?')[0];
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (gravite) params.append('gravite', gravite);
    params.append('_ajax', '1');
    
    try {
        const response = await fetch(url + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) throw new Error('Erreur réseau');
        
        const allergies = await response.json();
        
        const count = allergies.length;
        searchInfo.innerHTML = count > 0 ? `${count} allergie(s) trouvée(s)` : 'Aucune allergie trouvée';
        
        if (allergies.length === 0) {
            resultsContainer.innerHTML = `
                <table class="w-full border text-sm">
                    <thead class="bg-gray-200"><tr><th class="p-3 text-left">Nom</th><th class="p-3 text-left">Description</th><th class="p-3 text-left">Gravité</th><th class="p-3 text-left">Symptômes</th><th class="p-3 text-center">Actions</th></tr></thead>
                    <tbody><tr><td colspan="5" class="text-center p-8 text-gray-500">Aucune allergie trouvée</td></tr></tbody>
                </table>
            `;
        } else {
            let html = `<table class="w-full border text-sm">
                <thead class="bg-gradient-to-r from-gray-200 to-gray-100">
                    <tr>
                        <th class="p-3 text-left">Nom</th>
                        <th class="p-3 text-left">Description</th>
                        <th class="p-3 text-left">Gravité</th>
                        <th class="p-3 text-left">Symptômes</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>`;
            
            allergies.forEach(a => {
                const graviteClass = getGraviteClass(a.gravite);
                
                html += `
                    <tr class="border-t hover:bg-gray-50 resultat-item">
                        <td class="p-3 font-semibold">🌿 ${escapeHtml(a.nom)}</td>
                        <td class="p-3 text-gray-600">${escapeHtml(a.description?.substring(0, 80) || '')}...</td>
                        <td class="p-3"><span class="px-2 py-1 rounded text-xs font-semibold ${graviteClass}">${escapeHtml(a.gravite)}</span></td>
                        <td class="p-3 text-gray-600">${escapeHtml(a.symptomes?.substring(0, 60) || '')}...</td>
                        <td class="p-3 text-center">
                            <div class="flex gap-2 justify-center flex-wrap">
                                <button onclick="launchDynamicAnalysis(${a.id_allergie}, '${escapeHtml(a.nom).replace(/'/g, "\\'")}')"
                                        class="btn-analyse text-white px-4 py-2 rounded-lg transition flex items-center gap-2 text-sm font-medium">
                                    🤖 Analyse IA
                                </button>
                                <a href="allergie_edit.php?id=${a.id_allergie}" class="bg-yellow-400 px-3 py-1 rounded hover:bg-yellow-500 transition">✏️</a>
                                <a href="allergie_delete.php?id=${a.id_allergie}" onclick="return confirm('Supprimer cette allergie ?')" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">🗑️</a>
                                <a href="traitement_list.php?id_allergie=${a.id_allergie}" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">💊</a>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            html += `</tbody></table>`;
            resultsContainer.innerHTML = html;
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        searchInfo.innerHTML = 'Erreur lors de la recherche';
    } finally {
        loadingIndicator.style.display = 'none';
    }
}

function getGraviteClass(gravite) {
    const g = (gravite || '').toLowerCase();
    if (g === 'faible') return 'text-green-600 bg-green-100';
    if (g === 'moyenne') return 'text-orange-600 bg-orange-100';
    if (g === 'grave') return 'text-red-600 bg-red-100';
    return 'text-gray-600 bg-gray-100';
}

// FONCTIONS POUR L'ANALYSE IA DYNAMIQUE
async function launchDynamicAnalysis(allergieId, allergieNom) {
    currentAllergieId = allergieId;
    currentAllergieNom = allergieNom;
    
    const modal = document.getElementById('dynamicAnalysisModal');
    const body = document.getElementById('dynamicAnalysisBody');
    
    document.getElementById('modalAllergieNom').textContent = allergieNom;
    
    // Afficher un squelette de chargement animé
    body.innerHTML = `
        <div class="text-center py-12">
            <div class="inline-block">
                <div class="text-6xl mb-4 animate-pulse">🧠</div>
            </div>
            <p class="text-lg font-medium text-purple-700">Génération d'une analyse unique...</p>
            <p class="text-sm text-gray-500 mt-2">L'IA crée une analyse personnalisée pour ${escapeHtml(allergieNom)}</p>
            <div class="mt-6 space-y-3">
                <div class="skeleton h-20 w-full"></div>
                <div class="skeleton h-32 w-full"></div>
                <div class="skeleton h-24 w-full"></div>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    await fetchAnalysis();
}

async function fetchAnalysis() {
    try {
        const response = await fetch('analyse_allergie_complete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_allergie: currentAllergieId })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error || 'Erreur inconnue');
        }

        displayDynamicAnalysis(result.data);

    } catch (err) {
        console.error('Erreur analyse:', err);
        document.getElementById('dynamicAnalysisBody').innerHTML = `
            <div class="text-center py-12 text-red-600">
                <p class="text-4xl mb-3">⚠️</p>
                <p class="font-bold">Erreur lors de l'analyse</p>
                <p class="text-sm mt-2">${escapeHtml(err.message)}</p>
                <button onclick="retryAnalysis()" class="mt-4 px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                    🔄 Réessayer
                </button>
            </div>
        `;
    }
}

function retryAnalysis() {
    fetchAnalysis();
}

function regenerateAnalysis() {
    // Re-générer une nouvelle analyse (différente)
    fetchAnalysis();
}

function displayDynamicAnalysis(data) {
    const body = document.getElementById('dynamicAnalysisBody');
    const now = new Date();
    const timestamp = `${now.getHours().toString().padStart(2,'0')}:${now.getMinutes().toString().padStart(2,'0')}:${now.getSeconds().toString().padStart(2,'0')}`;
    const seed = Math.floor(Math.random() * 10000);
    
    let html = `
        <div class="flex justify-between items-center mb-4 pb-2 border-b">
            <div class="text-xs text-gray-400">
                🎲 Analyse unique #${seed}
            </div>
            <div class="text-xs text-gray-400">
                🕐 Générée à ${timestamp}
            </div>
        </div>
        <div class="space-y-6">
    `;

    // Synthèse unique
    if (data.synthese_unique) {
        html += `
            <div class="bg-gradient-to-r from-purple-50 to-violet-50 border-l-4 border-purple-600 p-5 rounded-lg">
                <h4 class="text-lg font-bold text-purple-800 mb-3 flex items-center gap-2">
                    <span>📋</span> Synthèse Clinique
                </h4>
                <p class="text-gray-700 leading-relaxed">${escapeHtml(data.synthese_unique)}</p>
            </div>`;
    }

    // Analyse approfondie
    if (data.analyse_approfondie) {
        html += `
            <div class="bg-blue-50 border-l-4 border-blue-600 p-5 rounded-lg">
                <h4 class="text-lg font-bold text-blue-800 mb-3 flex items-center gap-2">
                    <span>🔬</span> Analyse Approfondie
                </h4>
                <p class="text-gray-700 leading-relaxed">${escapeHtml(data.analyse_approfondie)}</p>
            </div>`;
    }

    // Recommandations sur mesure
    if (data.recommandations_sur_mesure && Array.isArray(data.recommandations_sur_mesure)) {
        html += `
            <div class="bg-green-50 border-l-4 border-green-600 p-5 rounded-lg">
                <h4 class="text-lg font-bold text-green-800 mb-3 flex items-center gap-2">
                    <span>✅</span> Recommandations Personnalisées
                </h4>
                <ul class="space-y-2">`;
        data.recommandations_sur_mesure.forEach(rec => {
            html += `<li class="flex items-start gap-2"><span class="text-green-600 font-bold">•</span> ${escapeHtml(rec)}</li>`;
        });
        html += `</ul></div>`;
    }

    // Traitements innovants
    if (data.traitements_innovants && Array.isArray(data.traitements_innovants)) {
        html += `
            <div class="bg-orange-50 border-l-4 border-orange-600 p-5 rounded-lg">
                <h4 class="text-lg font-bold text-orange-800 mb-3 flex items-center gap-2">
                    <span>🚀</span> Thérapies Innovantes 2025-2026
                </h4>
                <ul class="space-y-2">`;
        data.traitements_innovants.forEach(trait => {
            html += `<li class="flex items-start gap-2"><span class="text-orange-600 font-bold">✦</span> ${escapeHtml(trait)}</li>`;
        });
        html += `</ul></div>`;
    }

    // Aller plus loin
    if (data.aller_plus_loin) {
        html += `
            <div class="bg-indigo-50 border-l-4 border-indigo-600 p-5 rounded-lg">
                <h4 class="text-lg font-bold text-indigo-800 mb-3 flex items-center gap-2">
                    <span>💡</span> Pour aller plus loin
                </h4>
                <p class="text-gray-700 leading-relaxed">${escapeHtml(data.aller_plus_loin)}</p>
            </div>`;
    }

    // Note de bas de page
    html += `
        <div class="text-center text-xs text-gray-400 pt-4 border-t mt-4">
            🤖 Analyse IA générée dynamiquement - Chaque analyse est unique et personnalisée<br>
            <span class="text-purple-600">✨ Ne contient pas de données pré-apprises spécifiques à cette consultation ✨</span>
        </div>
    `;
    
    html += `</div>`;
    body.innerHTML = html;
}

function hideDynamicAnalysisModal() {
    document.getElementById('dynamicAnalysisModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function exportDynamicReport() {
    const content = document.getElementById('dynamicAnalysisBody').innerHTML;
    const allergieNom = currentAllergieNom;
    const now = new Date();
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Analyse IA - ${allergieNom}</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; padding: 40px; line-height: 1.6; }
                h1 { color: #667eea; }
                .header { text-align: center; margin-bottom: 30px; }
                .footer { text-align: center; color: #666; font-size: 12px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; }
                @media print {
                    body { margin: 0; padding: 20px; }
                    .no-print { display: none; }
                }
                ${getPrintStyles()}
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🧠 Analyse IA - ${escapeHtml(allergieNom)}</h1>
                <p>Généré le ${now.toLocaleString()}</p>
                <p style="color: #667eea;">✨ Analyse unique générée dynamiquement ✨</p>
            </div>
            <div class="content">
                ${content}
            </div>
            <div class="footer">
                Document généré par AllergieBot Pro - IA médicale experte<br>
                Chaque analyse est unique et personnalisée
            </div>
            <div class="no-print" style="text-align: center; margin-top: 30px;">
                <button onclick="window.print()">🖨️ Imprimer</button>
                <button onclick="window.close()">❌ Fermer</button>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
}

function getPrintStyles() {
    return `
        .bg-gradient-to-r { background: #f3e8ff; }
        .bg-blue-50 { background: #eff6ff; }
        .bg-green-50 { background: #f0fdf4; }
        .bg-orange-50 { background: #fff7ed; }
        .bg-indigo-50 { background: #eef2ff; }
        .border-l-4 { border-left-width: 4px; }
        .border-purple-600 { border-left-color: #9333ea; }
        .border-blue-600 { border-left-color: #2563eb; }
        .border-green-600 { border-left-color: #16a34a; }
        .border-orange-600 { border-left-color: #ea580c; }
        .border-indigo-600 { border-left-color: #4f46e5; }
        .p-5 { padding: 20px; }
        .rounded-lg { border-radius: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .space-y-6 > * + * { margin-top: 24px; }
        .space-y-2 > * + * { margin-top: 8px; }
    `;
}

// Événements
searchInput.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(searchAllergies, 300);
});

graviteFilter.addEventListener('change', searchAllergies);
resetBtn.addEventListener('click', () => {
    searchInput.value = '';
    graviteFilter.value = '';
    searchAllergies();
});

// Fermeture modale au clic en dehors
window.onclick = function(event) {
    const modal = document.getElementById('dynamicAnalysisModal');
    if (event.target === modal) hideDynamicAnalysisModal();
}

// Chargement initial
if (searchInput) searchAllergies();
</script>

</body>
</html>