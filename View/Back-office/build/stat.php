<?php
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';
require_once __DIR__ . '/../../../Controller/traitement.Controller.php';

$controller = new AllergieC();
$traitementController = new TraitementC();

// Récupérer toutes les allergies
$allergies = $controller->listAllergie();

// Préparer les données pour les graphiques
$graviteCount = [
    'faible' => 0,
    'moyenne' => 0,
    'grave' => 0
];

$symptomesList = [];
$allergiesParSaison = [
    'Printemps' => 0,
    'Été' => 0,
    'Automne' => 0,
    'Hiver' => 0,
    'Toute l\'année' => 0
];

$typesAllergies = [
    'Alimentaire' => 0,
    'Respiratoire' => 0,
    'Cutané' => 0,
    'Médicamenteuse' => 0,
    'Autre' => 0
];

foreach ($allergies as $a) {
    $gravite = strtolower($a['gravite']);
    if (isset($graviteCount[$gravite])) {
        $graviteCount[$gravite]++;
    }
    
    // Catégorisation des allergies (basée sur le nom et la description)
    $nomComplet = strtolower($a['nom'] . ' ' . ($a['description'] ?? ''));
    if (strpos($nomComplet, 'aliment') !== false || strpos($nomComplet, 'fruit') !== false || strpos($nomComplet, 'lait') !== false || strpos($nomComplet, 'oeuf') !== false || strpos($nomComplet, 'gluten') !== false) {
        $typesAllergies['Alimentaire']++;
    } elseif (strpos($nomComplet, 'pollen') !== false || strpos($nomComplet, 'acarien') !== false || strpos($nomComplet, 'poussière') !== false || strpos($nomComplet, 'moisissure') !== false) {
        $typesAllergies['Respiratoire']++;
    } elseif (strpos($nomComplet, 'peau') !== false || strpos($nomComplet, 'contact') !== false || strpos($nomComplet, 'eczéma') !== false) {
        $typesAllergies['Cutané']++;
    } elseif (strpos($nomComplet, 'médicament') !== false || strpos($nomComplet, 'pénicilline') !== false) {
        $typesAllergies['Médicamenteuse']++;
    } else {
        $typesAllergies['Autre']++;
    }
    
    // Simulation de saison (basée sur la description ou défaut)
    $saison = 'Toute l\'année';
    if (strpos($nomComplet, 'printemps') !== false) $saison = 'Printemps';
    elseif (strpos($nomComplet, 'été') !== false) $saison = 'Été';
    elseif (strpos($nomComplet, 'automne') !== false) $saison = 'Automne';
    elseif (strpos($nomComplet, 'hiver') !== false) $saison = 'Hiver';
    $allergiesParSaison[$saison]++;
    
    // Compter les symptômes
    if (!empty($a['symptomes'])) {
        $symptomes = explode(',', $a['symptomes']);
        foreach ($symptomes as $symptome) {
            $symptome = trim($symptome);
            if (!empty($symptome)) {
                if (!isset($symptomesList[$symptome])) {
                    $symptomesList[$symptome] = 0;
                }
                $symptomesList[$symptome]++;
            }
        }
    }
}

// Trier les symptômes par fréquence
arsort($symptomesList);
$topSymptomes = array_slice($symptomesList, 0, 5);

// Statistiques des traitements
$traitements = $traitementController->listTraitement();
$totalTraitements = count($traitements);

// Calculer les pourcentages
$totalAllergies = count($allergies);
$pourcentages = [];
if ($totalAllergies > 0) {
    foreach ($graviteCount as $key => $value) {
        $pourcentages[$key] = round(($value / $totalAllergies) * 100, 1);
    }
    foreach ($typesAllergies as $key => $value) {
        $typesAllergiesPercent[$key] = round(($value / $totalAllergies) * 100, 1);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord Statistique - Allergies</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body class="bg-gray-100 p-6">

<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-purple-600">📊 Tableau de Bord Statistique</h1>
                <p class="text-gray-600 mt-1">Analyse professionnelle des données d'allergies</p>
            </div>
            <a href="allergies_list.php" 
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                ← Retour à la liste
            </a>
        </div>
    </div>

    <!-- Cartes KPI principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-4 stat-card text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Base de connaissances</p>
                    <p class="text-3xl font-bold"><?= $totalAllergies ?></p>
                    <p class="text-blue-100 text-xs mt-1">Allergies répertoriées</p>
                </div>
                <div class="text-4xl opacity-80">📚</div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-4 stat-card text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Thérapeutique</p>
                    <p class="text-3xl font-bold"><?= $totalTraitements ?></p>
                    <p class="text-green-100 text-xs mt-1">Traitements disponibles</p>
                </div>
                <div class="text-4xl opacity-80">💊</div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow p-4 stat-card text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Diversité symptomatique</p>
                    <p class="text-3xl font-bold"><?= count($symptomesList) ?></p>
                    <p class="text-purple-100 text-xs mt-1">Symptômes uniques</p>
                </div>
                <div class="text-4xl opacity-80">🤧</div>
            </div>
        </div>
    </div>

    <!-- Graphiques principaux -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- Graphique Répartition par Gravité -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🎯 Distribution par Niveau de Gravité</h3>
            <canvas id="graviteChart" height="250"></canvas>
            <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                <div class="bg-green-50 rounded p-2">
                    <p class="text-xs text-gray-600">Faible</p>
                    <p class="text-lg font-bold text-green-600"><?= $graviteCount['faible'] ?></p>
                    <p class="text-xs text-gray-500"><?= $pourcentages['faible'] ?>%</p>
                </div>
                <div class="bg-orange-50 rounded p-2">
                    <p class="text-xs text-gray-600">Moyenne</p>
                    <p class="text-lg font-bold text-orange-600"><?= $graviteCount['moyenne'] ?></p>
                    <p class="text-xs text-gray-500"><?= $pourcentages['moyenne'] ?>%</p>
                </div>
                <div class="bg-red-50 rounded p-2">
                    <p class="text-xs text-gray-600">Grave</p>
                    <p class="text-lg font-bold text-red-600"><?= $graviteCount['grave'] ?></p>
                    <p class="text-xs text-gray-500"><?= $pourcentages['grave'] ?>%</p>
                </div>
            </div>
        </div>

        <!-- Graphique Types d'Allergies -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🏷️ Classification par Type</h3>
            <canvas id="typesChart" height="250"></canvas>
        </div>
    </div>

    <!-- Deuxième ligne de graphiques -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- Graphique Saisonnalité -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📅 Répartition Saisonnière</h3>
            <canvas id="saisonChart" height="250"></canvas>
        </div>

        <!-- Graphique Top Symptômes -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🤧 Symptômes les plus fréquents</h3>
            <canvas id="symptomesChart" height="250"></canvas>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="bg-white rounded-lg shadow p-4 text-center text-gray-500 text-sm">
        <p>📊 Rapport généré le <?= date('d/m/Y à H:i:s') ?></p>
        <p class="mt-1">Analyse basée sur <?= $totalAllergies ?> allergies | <?= $totalTraitements ?> traitements disponibles</p>
    </div>
</div>

<script>
// Graphique de répartition par gravité
const ctx1 = document.getElementById('graviteChart').getContext('2d');
new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: ['Gravité Faible', 'Gravité Moyenne', 'Gravité Grave'],
        datasets: [{
            data: [<?= $graviteCount['faible'] ?>, <?= $graviteCount['moyenne'] ?>, <?= $graviteCount['grave'] ?>],
            backgroundColor: ['#10b981', '#f97316', '#ef4444'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = <?= $totalAllergies ?>;
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// Graphique des types d'allergies
const ctx2 = document.getElementById('typesChart').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($typesAllergies)) ?>,
        datasets: [{
            label: 'Nombre d\'allergies',
            data: <?= json_encode(array_values($typesAllergies)) ?>,
            backgroundColor: '#8b5cf6',
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Graphique de saisonnalité
const ctx3 = document.getElementById('saisonChart').getContext('2d');
new Chart(ctx3, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_keys($allergiesParSaison)) ?>,
        datasets: [{
            label: 'Allergies par saison',
            data: <?= json_encode(array_values($allergiesParSaison)) ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Graphique des symptômes
const ctx4 = document.getElementById('symptomesChart').getContext('2d');
new Chart(ctx4, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($topSymptomes)) ?>,
        datasets: [{
            label: 'Fréquence d\'apparition',
            data: <?= json_encode(array_values($topSymptomes)) ?>,
            backgroundColor: '#ec489a',
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

</body>
</html>