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
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gray-100 p-6">

<div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">

        <div>
            <h2 class="text-2xl font-bold text-blue-600">
                🌿 Liste des Allergies
            </h2>
        </div>

        <div class="flex gap-2">

            <!-- Analyse -->
            <a href="stat.php"
               class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition flex items-center gap-2">
                📊 Analyse Statistique
            </a>

            <!-- Export PDF -->
            <a href="export_pdf.php"
               class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition flex items-center gap-2">
                📄 Export PDF
            </a>

            <!-- Ajouter -->
            <a href="allergie_add.php"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                ➕ Nouvelle Allergie
            </a>

        </div>
    </div>

    <!-- 🔍 RECHERCHE -->
    <div class="mb-6 bg-gray-50 p-4 rounded-lg">

        <div class="relative">
            <input
                type="text"
                id="searchInput"
                placeholder="🔍 Rechercher une allergie..."
                autocomplete="off"
                class="w-full border-2 px-4 py-3 rounded-lg text-lg focus:outline-none focus:border-blue-500 transition"
            >

            <div id="loadingIndicator"
                 class="absolute right-3 top-3"
                 style="display:none;">
                <div class="loading"></div>
            </div>
        </div>

        <!-- Filtre gravité -->
        <div class="mt-3 flex gap-3 items-center">

            <label class="text-sm font-semibold text-gray-700">
                Filtrer par gravité :
            </label>

            <select id="graviteFilter"
                    class="border px-3 py-2 rounded text-sm focus:outline-none focus:border-blue-500">

                <option value="">📊 Toutes les gravités</option>
                <option value="faible">🟢 Faible</option>
                <option value="moyenne">🟠 Moyenne</option>
                <option value="grave">🔴 Grave</option>

            </select>

            <button id="resetBtn"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition text-sm">
                🔄 Réinitialiser
            </button>

        </div>
    </div>

    <!-- INFO -->
    <div id="searchInfo" class="mb-3 text-sm text-gray-600"></div>

    <!-- TABLE -->
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

                        <td class="p-3 text-gray-600">
                            <?= htmlspecialchars(substr($a['symptomes'], 0, 60)) ?>
                            <?= strlen($a['symptomes']) > 60 ? '...' : '' ?>
                        </td>

                        <td class="p-3 text-center">

                            <div class="flex gap-2 justify-center">

                                <!-- Modifier -->
                                <a href="allergie_edit.php?id=<?= $a['id_allergie'] ?>"
                                   class="bg-yellow-400 px-3 py-1 rounded hover:bg-yellow-500 transition">
                                    ✏️
                                </a>

                                <!-- Supprimer -->
                                <a href="allergie_delete.php?id=<?= $a['id_allergie'] ?>"
                                   onclick="return confirm('Supprimer cette allergie ?')"
                                   class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                                    🗑️
                                </a>

                                <!-- Traitements -->
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
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function searchAllergies() {

    const searchTerm = document.getElementById('searchInput').value;
    const gravite = document.getElementById('graviteFilter').value;

    document.getElementById('loadingIndicator').style.display = 'block';

    try {

        const response = await fetch(
            `?search=${encodeURIComponent(searchTerm)}&gravite=${encodeURIComponent(gravite)}`,
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );

        const allergies = await response.json();

        displayResults(allergies);

        document.getElementById('searchInfo').innerHTML =
            `📊 ${allergies.length} allergie(s) trouvée(s)`;

    } catch (error) {

        console.error(error);

    } finally {

        document.getElementById('loadingIndicator').style.display = 'none';

    }
}

function displayResults(allergies) {

    const container = document.getElementById('resultsContainer');

    if (allergies.length === 0) {

        container.innerHTML = `
            <div class="text-center p-8 text-gray-500">
                📭 Aucune allergie trouvée
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

        let graviteClass = '';

        switch(a.gravite.toLowerCase()) {

            case 'faible':
                graviteClass = 'text-green-600 bg-green-100';
                break;

            case 'moyenne':
                graviteClass = 'text-orange-600 bg-orange-100';
                break;

            case 'grave':
                graviteClass = 'text-red-600 bg-red-100';
                break;

            default:
                graviteClass = 'text-gray-600 bg-gray-100';
        }

        html += `
            <tr class="border-t hover:bg-gray-50 resultat-item">

                <td class="p-3 font-semibold">
                    🌿 ${escapeHtml(a.nom)}
                </td>

                <td class="p-3 text-gray-600">
                    ${escapeHtml(a.description)}
                </td>

                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs font-semibold ${graviteClass}">
                        ${escapeHtml(a.gravite)}
                    </span>
                </td>

                <td class="p-3 text-gray-600">
                    ${escapeHtml(a.symptomes)}
                </td>

                <td class="p-3 text-center">

                    <div class="flex gap-2 justify-center">

                        <a href="allergie_edit.php?id=${a.id_allergie}"
                           class="bg-yellow-400 px-3 py-1 rounded hover:bg-yellow-500 transition">
                            ✏️
                        </a>

                        <a href="allergie_delete.php?id=${a.id_allergie}"
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
        </table>
    `;

    container.innerHTML = html;
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('graviteFilter').value = '';
    searchAllergies();
}

let typingTimer;

document.getElementById('searchInput').addEventListener('input', function() {

    clearTimeout(typingTimer);

    typingTimer = setTimeout(searchAllergies, 200);
});

document.getElementById('graviteFilter').addEventListener('change', searchAllergies);

document.getElementById('resetBtn').addEventListener('click', resetFilters);

searchAllergies();
</script>

</body>
</html>