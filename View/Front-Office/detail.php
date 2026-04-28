<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Controller/allergie.Controller.php';
require_once __DIR__ . '/../Controller/traitement.Controller.php';

// Récupérer l'ID de l'allergie depuis l'URL
$id_allergie = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_allergie <= 0) {
    header('Location: front_allergies.php');
    exit();
}

// Récupérer les détails de l'allergie
$allergieController = new AllergieC();
$allergie = $allergieController->getAllergieById($id_allergie);

if (!$allergie) {
    header('Location: front_allergies.php');
    exit();
}

// Récupérer les traitements associés à cette allergie
$traitementController = new TraitementC();
$traitements = $traitementController->listTraitementByAllergie($id_allergie);

if (!is_array($traitements)) {
    $traitements = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($allergie['nom']) ?> - Détails de l'allergie</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .traitement-card {
            transition: all 0.3s ease;
        }
        
        .traitement-card:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .back-btn {
            transition: all 0.2s ease;
        }
        
        .back-btn:hover {
            transform: translateX(-3px);
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <div class="hero-section text-white py-8">
        <div class="container mx-auto px-4">
            <a href="front_allergies.php" class="back-btn inline-flex items-center gap-2 text-white/90 hover:text-white mb-4">
                ← Retour à la liste des allergies
            </a>
            <h1 class="text-3xl md:text-4xl font-bold">Détails de l'allergie</h1>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        
        <!-- Carte principale de l'allergie -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            
            <!-- En-tête avec le nom -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <span class="text-2xl">🌿</span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-white"><?= htmlspecialchars($allergie['nom']) ?></h2>
                </div>
            </div>
            
            <!-- Corps de la carte -->
            <div class="p-6">
                
                <!-- Gravité -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg">⚠️</span>
                        <h3 class="font-semibold text-gray-700">Niveau de gravité</h3>
                    </div>
                    <?php 
                        $gravite = $allergie['gravite'] ?? 'non définie';
                        $graviteClass = match(strtolower($gravite)) {
                            'faible' => 'bg-green-100 text-green-700 border-green-200',
                            'moyenne' => 'bg-orange-100 text-orange-700 border-orange-200',
                            'grave' => 'bg-red-100 text-red-700 border-red-200',
                            default => 'bg-gray-100 text-gray-600 border-gray-200'
                        };
                    ?>
                    <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold border <?= $graviteClass ?>">
                        <?= ucfirst($gravite) ?>
                    </span>
                </div>
                
                <!-- Description -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg">📝</span>
                        <h3 class="font-semibold text-gray-700">Description</h3>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-600 leading-relaxed">
                            <?= htmlspecialchars($allergie['description'] ?? 'Aucune description disponible') ?>
                        </p>
                    </div>
                </div>
                
                <!-- Symptômes -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg">🤧</span>
                        <h3 class="font-semibold text-gray-700">Symptômes</h3>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-100">
                        <p class="text-gray-600 leading-relaxed">
                            <?= htmlspecialchars($allergie['symptomes'] ?? 'Aucun symptôme répertorié') ?>
                        </p>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Section des traitements -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <span class="text-xl">💊</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Traitements disponibles</h2>
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">
                    <?= count($traitements) ?> traitement(s)
                </span>
            </div>
            
            <?php if (count($traitements) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($traitements as $index => $traitement): ?>
                        <div class="traitement-card bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-blue-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center">
                                        <span class="text-white text-xs font-bold"><?= $index + 1 ?></span>
                                    </div>
                                    <h3 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($traitement['nom_traitement']) ?></h3>
                                </div>
                            </div>
                            
                            <div class="p-5">
                                <!-- Conseils -->
                                <?php if (!empty($traitement['conseils'])): ?>
                                    <div class="mb-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-green-600">📌</span>
                                            <h4 class="font-semibold text-gray-700">Conseils</h4>
                                        </div>
                                        <p class="text-gray-600 text-sm leading-relaxed ml-6">
                                            <?= htmlspecialchars($traitement['conseils']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Interdictions -->
                                <?php if (!empty($traitement['interdiction'])): ?>
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-red-600">🚫</span>
                                            <h4 class="font-semibold text-gray-700">Interdictions</h4>
                                        </div>
                                        <p class="text-gray-600 text-sm leading-relaxed ml-6">
                                            <?= htmlspecialchars($traitement['interdiction']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-gray-50 rounded-xl p-8 text-center">
                    <div class="text-5xl mb-4">💊</div>
                    <p class="text-gray-500 text-lg">Aucun traitement n'est actuellement répertorié pour cette allergie.</p>
                    <p class="text-gray-400 text-sm mt-2">Consultez votre médecin pour plus d'informations.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Informations supplémentaires -->
        <div class="bg-gray-100 rounded-lg p-4 text-center text-sm text-gray-500">
            <p>ID de l'allergie : <?= htmlspecialchars($allergie['id_allergie']) ?></p>
            <p class="mt-1">Dernière mise à jour : <?= date('d/m/Y') ?></p>
        </div>
        
    </div>
    
</body>
</html>