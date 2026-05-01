<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/allergie.php';
require_once __DIR__ . '/../../Controller/allergie.Controller.php';
require_once __DIR__ . '/../../Controller/traitement.Controller.php'; // AJOUTÉ

$controller = new AllergieC();
$allergies = $controller->listAllergie();

// S'assurer que c'est un tableau
if (!is_array($allergies)) {
    $allergies = [];
}

// Récupérer l'ID pour les détails
$selected_id = $_GET['id'] ?? null;
$allergie_detail = null;
$traitements = [];

if ($selected_id) {
    $allergie_detail = $controller->getAllergieById($selected_id);
    if ($allergie_detail) {
        $traitementController = new TraitementC();
        $traitements = $traitementController->listTraitementByAllergie($selected_id);
        if (!is_array($traitements)) {
            $traitements = [];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allergies - Guide complet</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .card-allergy {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card-allergy:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .modal {
            display: <?= $selected_id ? 'flex' : 'none' ?>;
        }
        
        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .back-button {
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            transform: translateX(-5px);
        }
    </style>
</head>
<!-- Bouton flottant pour ouvrir le chatbot -->
<div class="fixed bottom-6 right-6 z-50">
    <a href="chatbot.php" 
       class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-full p-4 shadow-lg hover:shadow-xl transition transform hover:scale-105 flex items-center gap-2 group">
        <span class="text-2xl">🤖</span>
        <span class="hidden group-hover:inline text-sm font-semibold mr-2">AllergieBot</span>
    </a>
</div>
<body class="bg-gray-50">

    <!-- Header / Hero Section -->
    <div class="hero-section text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <!-- Bouton retour -->
            <div class="absolute top-4 left-4 md:top-8 md:left-8">
                <a href="index.html" class="back-button inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 rounded-lg px-4 py-2 text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour à l'accueil
                </a>
            </div>
            
            <div class="flex justify-center mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="64" height="64">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Guide complet des allergies</h1>
            <p class="text-lg md:text-xl opacity-90 max-w-2xl mx-auto">
                Découvrez les différentes allergies, leurs symptômes et les traitements associés
            </p>
            <div class="flex justify-center gap-4 mt-8">
                <div class="bg-white/20 rounded-full px-4 py-2 text-sm">
                    📊 <?= count($allergies) ?> allergies répertoriées
                </div>
                <div class="bg-white/20 rounded-full px-4 py-2 text-sm">
                    💊 Traitements disponibles
                </div>
            </div>
        </div>
    </div>

    <!-- Section des allergies -->
    <div class="container mx-auto px-4 py-12">
        
        <!-- Barre de recherche et filtre -->
        <div class="mb-8">
            <div class="max-w-4xl mx-auto">
                <!-- Barre de recherche -->
                <div class="mb-4">
                    <input type="text" 
                           id="searchInput" 
                           placeholder="🔍 Rechercher une allergie..." 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <!-- Filtre déroulant par gravité -->
                <div class="flex gap-3 items-center">
                    <label for="graviteFilter" class="text-gray-700 font-medium">Filtrer par gravité :</label>
                    <select id="graviteFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        <option value="all">🌍 Toutes les allergies</option>
                        <option value="faible">🟢 Gravité faible</option>
                        <option value="moyenne">🟠 Gravité moyenne</option>
                        <option value="grave">🔴 Gravité grave</option>
                    </select>
                    
                    <!-- Compteur de résultats -->
                    <div class="ml-auto text-sm text-gray-500">
                        <span id="resultCount"><?= count($allergies) ?></span> allergie(s) trouvée(s)
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Message si aucune allergie -->
        <?php if (count($allergies) == 0): ?>
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🌿</div>
                <p class="text-gray-500 text-lg">Aucune allergie disponible pour le moment.</p>
                <a href="allergie_add.php" class="text-blue-600 hover:underline mt-2 inline-block">Ajouter une allergie</a>
            </div>
        <?php else: ?>
            
            <!-- Grille des allergies -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="allergiesGrid">
                
                <?php foreach ($allergies as $allergie): 
                    $graviteValue = strtolower($allergie['gravite'] ?? 'non définie');
                ?>
                    <div class="card-allergy bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 allergy-card" 
                         data-name="<?= strtolower(htmlspecialchars($allergie['nom'])) ?>"
                         data-gravite="<?= $graviteValue ?>">
                        
                        <!-- En-tête -->
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-xl">🌿</span>
                                    </div>
                                    <h3 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($allergie['nom']) ?></h3>
                                </div>
                                
                                <!-- Badge gravité -->
                                <?php 
                                    $graviteClass = match($graviteValue) {
                                        'faible' => 'bg-green-100 text-green-700',
                                        'moyenne' => 'bg-orange-100 text-orange-700',
                                        'grave' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-600'
                                    };
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $graviteClass ?>">
                                    <?= ucfirst($graviteValue) ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Description courte -->
                        <div class="p-4">
                            <p class="text-gray-600 text-sm">
                                <?= htmlspecialchars(substr($allergie['description'] ?? '', 0, 100)) ?>
                                <?= isset($allergie['description']) && strlen($allergie['description']) > 100 ? '...' : '' ?>
                            </p>
                        </div>
                        
                        <!-- Symptômes aperçu -->
                        <?php if (!empty($allergie['symptomes'])): ?>
                        <div class="px-4 pb-2">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>🤧</span>
                                <span class="truncate"><?= htmlspecialchars(substr($allergie['symptomes'], 0, 60)) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Bouton Détails -->
                        <div class="p-4 pt-2">
                            <a href="?id=<?= $allergie['id_allergie'] ?>" 
                               class="block w-full bg-blue-600 text-white py-2 rounded-lg font-semibold text-center hover:bg-blue-700 transition">
                                📖 Voir détails
                            </a>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
                
            </div>
            
        <?php endif; ?>
        
    </div>

    <!-- Modal/Carte de détails -->
    <?php if ($allergie_detail): ?>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            
            <!-- En-tête -->
            <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">
                    <span class="text-2xl mr-2">🌿</span>
                    <?= htmlspecialchars($allergie_detail['nom']) ?>
                </h2>
                <a href="?" class="text-gray-400 hover:text-gray-600 transition text-2xl">
                    ✕
                </a>
            </div>
            
            <div class="p-6">
                
                <!-- Gravité -->
                <div class="mb-4">
                    <?php 
                        $gravite = $allergie_detail['gravite'] ?? 'non définie';
                        $graviteBadgeClass = match(strtolower($gravite)) {
                            'faible' => 'bg-green-100 text-green-700',
                            'moyenne' => 'bg-orange-100 text-orange-700',
                            'grave' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-600'
                        };
                    ?>
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold <?= $graviteBadgeClass ?>">
                        Gravité : <?= ucfirst($gravite) ?>
                    </span>
                </div>
                
                <!-- Description complète -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-2 text-lg">📝 Description</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-600 leading-relaxed">
                            <?= htmlspecialchars($allergie_detail['description'] ?? 'Aucune description disponible') ?>
                        </p>
                    </div>
                </div>
                
                <!-- Symptômes -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-2 text-lg">🤧 Symptômes</h3>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-gray-600 leading-relaxed">
                            <?= htmlspecialchars($allergie_detail['symptomes'] ?? 'Aucun symptôme répertorié') ?>
                        </p>
                    </div>
                </div>
                
                <!-- Traitements associés -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-700 mb-3 text-lg">💊 Traitements associés</h3>
                    
                    <?php if (count($traitements) > 0): ?>
                        <div class="space-y-3">
                            <?php foreach ($traitements as $index => $t): ?>
                                <div class="bg-white rounded-lg p-3 border border-gray-200 hover:shadow-md transition">
                                    <div class="flex items-start gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                                            <span class="text-white text-xs font-bold"><?= $index + 1 ?></span>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($t['nom_traitement']) ?></h4>
                                            <?php if (!empty($t['conseils'])): ?>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    <span class="font-medium text-green-700">📌 Conseils :</span> <?= htmlspecialchars($t['conseils']) ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if (!empty($t['interdiction'])): ?>
                                                <p class="text-sm text-red-600 mt-1">
                                                    <span class="font-medium">🚫 Interdictions :</span> <?= htmlspecialchars($t['interdiction']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-gray-500 text-sm">💊 Aucun traitement répertorié pour cette allergie.</p>
                        </div>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Statistiques -->
                <div class="bg-gray-50 rounded-lg p-4 mt-4">
                    <div class="flex justify-around text-center">
                        <div>
                            <p class="text-2xl font-bold text-blue-600"><?= count($traitements) ?></p>
                            <p class="text-xs text-gray-500">Traitements</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">1</p>
                            <p class="text-xs text-gray-500">Allergie</p>
                        </div>
                    </div>
                </div>
                
                <!-- Bouton fermer -->
                <div class="mt-6">
                    <a href="?" class="block w-full bg-gray-200 text-gray-700 py-2 rounded-lg font-semibold text-center hover:bg-gray-300 transition">
                        Fermer
                    </a>
                </div>
                
            </div>
            
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Filtre par gravité avec select
        const graviteSelect = document.getElementById('graviteFilter');
        const searchInput = document.getElementById('searchInput');
        const allergyCards = document.querySelectorAll('.allergy-card');
        const resultCountSpan = document.getElementById('resultCount');
        
        let currentSearch = '';
        
        // Fonction pour filtrer les cartes
        function filterCards() {
            const selectedGravite = graviteSelect.value;
            let visibleCount = 0;
            
            allergyCards.forEach(card => {
                const gravite = card.getAttribute('data-gravite');
                const name = card.getAttribute('data-name');
                
                // Vérifier le filtre de gravité
                const graviteMatch = selectedGravite === 'all' || gravite === selectedGravite;
                
                // Vérifier la recherche
                const searchMatch = !currentSearch || (name && name.includes(currentSearch));
                
                // Afficher ou masquer la carte
                if (graviteMatch && searchMatch) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Mettre à jour le compteur
            if (resultCountSpan) {
                resultCountSpan.textContent = visibleCount;
            }
            
            // Afficher un message si aucun résultat
            const allergiesGrid = document.getElementById('allergiesGrid');
            let noResultMsg = document.getElementById('noResultMessage');
            
            if (visibleCount === 0) {
                if (!noResultMsg) {
                    noResultMsg = document.createElement('div');
                    noResultMsg.id = 'noResultMessage';
                    noResultMsg.className = 'col-span-full text-center py-12';
                    noResultMsg.innerHTML = `
                        <div class="text-6xl mb-4">🔍</div>
                        <p class="text-gray-500 text-lg">Aucune allergie ne correspond à vos critères</p>
                        <button onclick="resetFilters()" class="mt-4 text-blue-600 hover:underline">Réinitialiser les filtres</button>
                    `;
                    allergiesGrid.appendChild(noResultMsg);
                }
            } else {
                if (noResultMsg) {
                    noResultMsg.remove();
                }
            }
        }
        
        // Fonction pour réinitialiser les filtres
        window.resetFilters = function() {
            graviteSelect.value = 'all';
            if (searchInput) {
                searchInput.value = '';
                currentSearch = '';
            }
            filterCards();
        }
        
        // Écouter les changements du select
        if (graviteSelect) {
            graviteSelect.addEventListener('change', filterCards);
        }
        
        // Écouter la recherche
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                currentSearch = this.value.toLowerCase();
                filterCards();
            });
        }
        
        // Initialisation
        filterCards();
    </script>
    
</body>
</html>