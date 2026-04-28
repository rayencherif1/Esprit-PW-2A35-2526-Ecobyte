<?php
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';

$controller = new AllergieC();

// Si c'est une requête AJAX, on retourne uniquement le JSON
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    $search = $_GET['search'] ?? null;
    $gravite = $_GET['gravite'] ?? null;
    
    $allergies = $controller->listAllergie($gravite, $search);
    
    header('Content-Type: application/json');
    echo json_encode($allergies);
    exit;
}

// Chargement initial sans filtre
$allergies = $controller->listAllergie();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Allergies - Recherche Dynamique</title>
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
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="bg-gray-100 p-6">

<div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-blue-600">🌿 Liste des Allergies</h2>
        </div>

        <div class="flex gap-2">
            <!-- Bouton Analyse Statistique -->
            <a href="stat.php" 
               class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition flex items-center gap-2">
                📊 Analyse Statistique
            </a>
            
            <a href="allergie_add.php"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                ➕ Nouvelle Allergie
            </a>
        </div>
    </div>

    <!-- 🔍 BARRE DE RECHERCHE DYNAMIQUE -->
    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
        <div class="relative">
            <input 
                type="text"
                id="searchInput"
                placeholder="🔍 Rechercher une allergie (ex: pollen, p...)"
                autocomplete="off"
                class="w-full border-2 px-4 py-3 rounded-lg text-lg focus:outline-none focus:border-blue-500 transition"
            >
            <div id="loadingIndicator" class="absolute right-3 top-3" style="display: none;">
                <div class="loading"></div>
            </div>
        </div>
        
        <!-- Filtre gravité -->
        <div class="mt-3 flex gap-3 items-center">
            <label class="text-sm font-semibold text-gray-700">Filtrer par gravité :</label>
            <select id="graviteFilter" class="border px-3 py-2 rounded text-sm focus:outline-none focus:border-blue-500">
                <option value="">📊 Toutes les gravités</option>
                <option value="faible">🟢 Faible</option>
                <option value="moyenne">🟠 Moyenne</option>
                <option value="grave">🔴 Grave</option>
            </select>
            
            <button id="resetBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition text-sm">
                🔄 Réinitialiser
            </button>
        </div>
    </div>

    <!-- 💡 INFO RECHERCHE -->
    <div id="searchInfo" class="mb-3 text-sm text-gray-600"></div>

    <!-- 📊 TABLEAU DES RÉSULTATS -->
    <div class="overflow-x-auto">
        <div id="resultsContainer" class="fade-in">
            <table class="w-full border text-sm">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-3 text-left">Nom</th>
                        <th class="p-3 text-left">Description</th>
                        <th class="p-3 text-left">Gravité</th>
                        <th class="p-3 text-left">Symptômes</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allergies as $a): ?>
                    <tr class="border-t hover:bg-gray-50 resultat-item">
                        <td class="p-3 font-semibold">
                            🌿 <?= htmlspecialchars($a['nom']) ?>
                        </td>
                        <td class="p-3 text-gray-600">
                            <?= htmlspecialchars(substr($a['description'], 0, 80)) ?>
                            <?= strlen($a['description']) > 80 ? '...' : '' ?>
                        </td>
                        <td class="p-3">
                            <?php
                            $g = strtolower($a['gravite']);
                            $color = match($g) {
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
                        <td class="p-3 text-gray-600">
                            <?= htmlspecialchars(substr($a['symptomes'], 0, 60)) ?>
                            <?= strlen($a['symptomes']) > 60 ? '...' : '' ?>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex gap-2 justify-center">
                                <a href="allergie_edit.php?id=<?= $a['id_allergie'] ?>"
                                   class="bg-yellow-400 px-3 py-1 rounded hover:bg-yellow-500 transition">
                                    ✏️
                                </a>
                                <a href="allergie_delete.php?id=<?= $a['id_allergie'] ?>"
                                   onclick="return confirm('Supprimer cette allergie ?')"
                                   class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                                    🗑️
                                </a>
                                <a href="traitement_list.php?id_allergie=<?= $a['id_allergie'] ?>"
                                   class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">
                                    💊
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Fonction pour échapper les caractères HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Fonction pour afficher les résultats (sans surlignage)
function displayResults(allergies, searchTerm) {
    const container = document.getElementById('resultsContainer');
    
    if (!allergies || allergies.length === 0) {
        container.innerHTML = `
            <div class="text-center p-8 text-gray-500">
                <div class="text-4xl mb-2">📭</div>
                <div>Aucune allergie trouvée</div>
                ${searchTerm ? '<div class="text-sm mt-2">Essayez avec un autre terme de recherche</div>' : ''}
            </div>
        `;
        return;
    }
    
    let html = `
        <table class="w-full border text-sm">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-3 text-left">Nom</th>
                    <th class="p-3 text-left">Description</th>
                    <th class="p-3 text-left">Gravité</th>
                    <th class="p-3 text-left">Symptômes</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    allergies.forEach(a => {
        let nom = escapeHtml(a.nom);
        let description = escapeHtml(a.description.length > 80 ? a.description.substring(0, 80) + '...' : a.description);
        let symptomes = escapeHtml(a.symptomes.length > 60 ? a.symptomes.substring(0, 60) + '...' : a.symptomes);
        
        let graviteClass = '';
        let graviteText = '';
        switch(a.gravite.toLowerCase()) {
            case 'faible':
                graviteClass = 'text-green-600 bg-green-100';
                graviteText = 'Faible';
                break;
            case 'moyenne':
                graviteClass = 'text-orange-600 bg-orange-100';
                graviteText = 'Moyenne';
                break;
            case 'grave':
                graviteClass = 'text-red-600 bg-red-100';
                graviteText = 'Grave';
                break;
            default:
                graviteClass = 'text-gray-600 bg-gray-100';
                graviteText = a.gravite;
        }
        
        html += `
            <tr class="border-t hover:bg-gray-50 resultat-item">
                <td class="p-3 font-semibold">
                    🌿 ${nom}
                </td>
                <td class="p-3 text-gray-600">
                    ${description}
                </td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs font-semibold ${graviteClass}">
                        ${graviteText}
                    </span>
                </td>
                <td class="p-3 text-gray-600">
                    ${symptomes}
                </td>
                <td class="p-3 text-center">
                    <div class="flex gap-2 justify-center">
                        <a href="allergie_edit.php?id=${a.id_allergie}"
                           class="bg-yellow-400 px-3 py-1 rounded hover:bg-yellow-500 transition">
                            ✏️
                        </a>
                        <a href="allergie_delete.php?id=${a.id_allergie}"
                           onclick="return confirm('Supprimer cette allergie ?')"
                           class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                            🗑️
                        </a>
                        <a href="traitement_list.php?id_allergie=${a.id_allergie}"
                           class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">
                            💊
                        </a>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
            <tfoot>
                <tr class="bg-gray-50">
                    <td colspan="5" class="p-3 text-sm text-gray-500">
                        Total : <span class="font-semibold">${allergies.length}</span> allergie(s)
                    </td>
                </tr>
            </tfoot>
        </table>
    `;
    
    container.innerHTML = html;
    container.classList.add('fade-in');
    setTimeout(() => container.classList.remove('fade-in'), 300);
}

async function searchAllergies() {
    const searchTerm = document.getElementById('searchInput').value;
    const gravite = document.getElementById('graviteFilter').value;
    
    document.getElementById('loadingIndicator').style.display = 'block';
    
    const searchInfo = document.getElementById('searchInfo');
    if (searchTerm) {
        searchInfo.innerHTML = `🔍 Recherche des allergies contenant "<strong>${escapeHtml(searchTerm)}</strong>"...`;
    } else if (gravite) {
        searchInfo.innerHTML = `🔍 Filtrage par gravité "<strong>${escapeHtml(gravite)}</strong>"...`;
    } else {
        searchInfo.innerHTML = '';
    }
    
    try {
        const response = await fetch(`?search=${encodeURIComponent(searchTerm)}&gravite=${encodeURIComponent(gravite)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const allergies = await response.json();
        displayResults(allergies, searchTerm);
        
        if (searchTerm) {
            if (allergies.length === 0) {
                searchInfo.innerHTML = `🔍 Aucun résultat trouvé pour "<strong>${escapeHtml(searchTerm)}</strong>"`;
            } else {
                searchInfo.innerHTML = `🔍 ${allergies.length} résultat(s) trouvé(s) pour "<strong>${escapeHtml(searchTerm)}</strong>"`;
            }
        } else if (gravite) {
            searchInfo.innerHTML = `🔍 ${allergies.length} résultat(s) pour la gravité "<strong>${escapeHtml(gravite)}</strong>"`;
        } else {
            searchInfo.innerHTML = `📊 ${allergies.length} allergie(s) au total`;
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        document.getElementById('resultsContainer').innerHTML = '<div class="text-red-500 text-center p-4">❌ Erreur lors du chargement des données</div>';
    } finally {
        document.getElementById('loadingIndicator').style.display = 'none';
    }
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('graviteFilter').value = '';
    searchAllergies();
}

let typingTimer;
const searchInput = document.getElementById('searchInput');
const graviteFilter = document.getElementById('graviteFilter');
const resetBtn = document.getElementById('resetBtn');

searchInput.addEventListener('input', function() {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(searchAllergies, 200);
});

graviteFilter.addEventListener('change', searchAllergies);
resetBtn.addEventListener('click', resetFilters);
searchAllergies();
</script>

</body>
</html>