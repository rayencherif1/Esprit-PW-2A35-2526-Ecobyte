<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/allergie.Controller.php';
require_once __DIR__ . '/../../Controller/traitement.Controller.php';

session_start();

// Initialisation des contrôleurs
$allergieController = new AllergieC();
$traitementController = new TraitementC();

// Gestion de la session
if (!isset($_SESSION['diagnostic'])) {
    $_SESSION['diagnostic'] = [
        'step' => 0,
        'allergie_diagnostiquee' => null,
        'reponses_alimentation' => [],
        'recettes_demandees' => false,
        'type_repas' => null
    ];
}

$diagnostic = &$_SESSION['diagnostic'];

// Base de données des recettes et aliments
$alimentsDB = [
    'Arachides' => [
        'a_eviter' => [
            '🥜 Cacahuètes', '🍪 Biscuits apéritifs', '🍫 Chocolat (certains)', 
            '🥜 Beurre de cacahuète', '🍜 Sauce saté', '🥘 Plats asiatiques (sauce arachide)',
            '🍦 Glaces (traces possibles)', '🥐 Pâtisseries', '🌰 Fruits secs mélangés'
        ],
        'a_manger' => [
            '🥩 Viandes fraîches', '🐟 Poissons', '🥚 Oeufs', '🥛 Produits laitiers',
            '🍚 Riz', '🍝 Pâtes (sans arachides)', '🥔 Pommes de terre', '🍅 Légumes frais',
            '🍎 Fruits frais', '🥖 Pain classique', '🧀 Fromages'
        ],
        'recettes' => [
            'dejeuner' => [
                'nom' => '🥗 Salade de poulet grillé',
                'ingredients' => ['Poulet grillé', 'Salade verte', 'Tomates cerises', 'Concombre', 'Vinaigrette maison (huile d\'olive, vinaigre)'],
                'preparation' => 'Faites griller le poulet, coupez-le en morceaux. Mélangez avec la salade, tomates et concombre. Assaisonnez avec la vinaigrette.'
            ],
            'diner' => [
                'nom' => '🍝 Pâtes à la carbonara sans arachides',
                'ingredients' => ['Pâtes', 'Œufs', 'Parmesan', 'Lardons', 'Poivre'],
                'preparation' => 'Cuire les pâtes. Mélanger les œufs et le parmesan. Dorer les lardons. Mélanger le tout hors du feu.'
            ]
        ]
    ],
    'Lactose' => [
        'a_eviter' => [
            '🥛 Lait de vache', '🧀 Fromages frais (ricotta, cottage)', '🍦 Glaces', 
            '🥛 Crème fraîche', '🧈 Beurre', '🥛 Yaourts classiques', '🍫 Chocolat au lait'
        ],
        'a_manger' => [
            '🥛 Laits végétaux (amande, soja, avoine)', '🧀 Fromages affinés (comté, parmesan)', 
            '🥛 Yaourts végétaux (soja, coco)', '🥑 Avocat', '🥩 Viandes', '🐟 Poissons',
            '🥚 Oeufs', '🍚 Riz', '🍝 Pâtes', '🥔 Légumes'
        ],
        'recettes' => [
            'dejeuner' => [
                'nom' => '🍚 Riz sauté aux légumes (sans lactose)',
                'ingredients' => ['Riz', 'Carottes', 'Petits pois', 'Oignon', 'Sauce soja', 'Huile d\'olive'],
                'preparation' => 'Faites revenir les légumes, ajoutez le riz cuit, mélangez avec la sauce soja.'
            ],
            'diner' => [
                'nom' => '🍲 Soupe de légumes maison',
                'ingredients' => ['Courgettes', 'Carottes', 'Poireaux', 'Pommes de terre', 'Eau', 'Sel, poivre'],
                'preparation' => 'Coupez tous les légumes, faites-les cuire dans l\'eau 30 min, mixez.'
            ]
        ]
    ],
    'Gluten' => [
        'a_eviter' => [
            '🍞 Pain classique', '🍝 Pâtes classiques', '🍪 Biscuits', '🍰 Gâteaux', 
            '🥐 Viennoiseries', '🍺 Bière', '🍕 Pizza classique'
        ],
        'a_manger' => [
            '🌾 Pain sans gluten', '🍝 Pâtes sans gluten (riz, maïs, quinoa)', '🍚 Riz', 
            '🥔 Pommes de terre', '🌽 Maïs', '🍚 Quinoa', '🥩 Viandes fraîches', '🐟 Poissons'
        ],
        'recettes' => [
            'dejeuner' => [
                'nom' => '🌯 Wrap sans gluten',
                'ingredients' => ['Galette de riz ou maïs', 'Poulet', 'Salade', 'Tomate', 'Avocat'],
                'preparation' => 'Farcissez la galette avec les ingrédients, roulez et dégustez.'
            ],
            'diner' => [
                'nom' => '🍚 Bowl Quinoa/Légumes',
                'ingredients' => ['Quinoa', 'Patate douce', 'Brocoli', 'Poulet', 'Huile d\'olive'],
                'preparation' => 'Cuire le quinoa, rôtir les légumes et le poulet, assemblez dans un bol.'
            ]
        ]
    ],
    'Pollen de bouleau' => [
        'a_eviter' => [
            '🍎 Pommes (réaction croisée)', '🍐 Poires', '🥝 Kiwis', '🌰 Noisettes', 
            '🥕 Carottes crues', '🍒 Cerises', 'Amandes'
        ],
        'a_manger' => [
            '🍌 Bananes', '🍊 Agrumes', '🍉 Melon', '🥑 Avocat', '🥦 Légumes cuits', 
            '🥩 Viandes', '🐟 Poissons', '🥚 Oeufs', '🍚 Riz'
        ],
        'recettes' => [
            'dejeuner' => [
                'nom' => '🥑 Toast à l\'avocat (sans pomme)',
                'ingredients' => ['Pain (sans issue pollen)', 'Avocat', 'Jus de citron', 'Sel', 'Poivre'],
                'preparation' => 'Écrasez l\'avocat, assaisonnez, tartinez sur le pain toasté.'
            ],
            'diner' => [
                'nom' => '🍗 Poulet rôti aux légumes cuits',
                'ingredients' => ['Poulet', 'Carottes cuites', 'Courgettes cuites', 'Pommes de terre'],
                'preparation' => 'Enfournez le poulet avec les légumes pour 45 min à 180°C.'
            ]
        ]
    ]
];

// Traitement du formulaire
$response = null;
$showAlimentation = false;
$showRecettes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Étape 1: Diagnostic initial
    if (isset($_POST['symptoms']) && !empty($_POST['symptoms'])) {
        $userSymptoms = $_POST['symptoms'];
        
        // Analyse pour trouver la meilleure allergie
        $analysis = analyzeSymptomsForTopMatch($userSymptoms, $allergieController, $traitementController);
        
        if ($analysis && isset($analysis['best_match'])) {
            $diagnostic['allergie_diagnostiquee'] = $analysis['best_match'];
            $diagnostic['step'] = 1;
            $response = $analysis;
        } else {
            $response = ['error' => 'Aucune allergie correspondante trouvée'];
        }
    }
    
    // Étape 2: Réponse sur l'alimentation
    elseif (isset($_POST['alimentation_reponse'])) {
        $reponse = $_POST['alimentation_reponse'];
        $diagnostic['reponses_alimentation'][] = $reponse;
        $diagnostic['step'] = 2;
        $response = ['best_match' => $diagnostic['allergie_diagnostiquee']];
    }
    
    // Étape 3: Demande de recettes
    elseif (isset($_POST['demande_recettes'])) {
        $typeRepas = $_POST['type_repas'];
        $diagnostic['type_repas'] = $typeRepas;
        $diagnostic['step'] = 3;
        $response = ['best_match' => $diagnostic['allergie_diagnostiquee']];
        
        // Préparer la recette demandée
        $allergieNom = $diagnostic['allergie_diagnostiquee']['nom'];
        $recettes = getRecettes($allergieNom, $typeRepas);
        $response['recette'] = $recettes;
    }
    
    // Reset
    elseif (isset($_POST['reset'])) {
        session_destroy();
        session_start();
        $_SESSION['diagnostic'] = [
            'step' => 0,
            'allergie_diagnostiquee' => null,
            'reponses_alimentation' => [],
            'recettes_demandees' => false,
            'type_repas' => null
        ];
        $diagnostic = &$_SESSION['diagnostic'];
        $response = null;
    }
}

/**
 * Analyse pour trouver LA meilleure allergie
 */
function analyzeSymptomsForTopMatch($userInput, $allergieController, $traitementController) {
    $userInput = strtolower(trim($userInput));
    $userSymptomsList = extractSymptomsList($userInput);
    
    $allergies = $allergieController->listAllergie();
    
    if (empty($allergies)) {
        return null;
    }
    
    $scores = [];
    
    foreach ($allergies as $allergie) {
        $allergieSymptoms = strtolower($allergie['symptomes'] ?? '');
        $allergieSymptomsList = extractSymptomsList($allergieSymptoms);
        
        $score = calculatePrecisionScore($userSymptomsList, $allergieSymptomsList);
        
        // Bonus gravité
        $graviteBonus = match($allergie['gravite']) {
            'grave' => 15,
            'moyenne' => 10,
            'faible' => 5,
            default => 0
        };
        
        $finalScore = min(100, $score + $graviteBonus);
        
        $scores[] = [
            'id' => $allergie['id_allergie'],
            'nom' => $allergie['nom'],
            'gravite' => $allergie['gravite'],
            'description' => $allergie['description'],
            'symptomes' => $allergie['symptomes'],
            'score' => round($finalScore, 1),
            'matched_symptoms' => findMatchedSymptomsList($userSymptomsList, $allergieSymptomsList)
        ];
    }
    
    // Trier et prendre le meilleur
    usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);
    $bestMatch = $scores[0] ?? null;
    
    // Récupérer les traitements
    if ($bestMatch) {
        $bestMatch['traitements'] = $traitementController->listTraitementByAllergie($bestMatch['id']);
    }
    
    return [
        'best_match' => $bestMatch,
        'user_symptoms' => $userSymptomsList
    ];
}

function extractSymptomsList($text) {
    $words = preg_split('/[\s,;.?!]+/', $text);
    $words = array_filter($words, fn($w) => strlen($w) > 2);
    return array_unique($words);
}

function calculatePrecisionScore($userSymptoms, $allergieSymptoms) {
    if (empty($allergieSymptoms)) return 0;
    
    $matchCount = 0;
    foreach ($userSymptoms as $userSymp) {
        foreach ($allergieSymptoms as $allergieSymp) {
            if (strpos($allergieSymp, $userSymp) !== false || 
                strpos($userSymp, $allergieSymp) !== false ||
                levenshtein($userSymp, $allergieSymp) <= 2) {
                $matchCount++;
                break;
            }
        }
    }
    
    return ($matchCount / count($allergieSymptoms)) * 100;
}

function findMatchedSymptomsList($userSymptoms, $allergieSymptoms) {
    $matched = [];
    foreach ($userSymptoms as $userSymp) {
        foreach ($allergieSymptoms as $allergieSymp) {
            if (strpos($allergieSymp, $userSymp) !== false || 
                strpos($userSymp, $allergieSymp) !== false) {
                $matched[] = $allergieSymp;
                break;
            }
        }
    }
    return array_unique($matched);
}

function getAliments($allergieNom, $type = 'a_eviter') {
    global $alimentsDB;
    
    // Chercher la clé correspondante
    $foundKey = null;
    foreach ($alimentsDB as $key => $value) {
        if (strpos($allergieNom, $key) !== false || strpos($key, $allergieNom) !== false) {
            $foundKey = $key;
            break;
        }
    }
    
    if ($foundKey && isset($alimentsDB[$foundKey][$type])) {
        return $alimentsDB[$foundKey][$type];
    }
    return [];
}

function getRecettes($allergieNom, $type = 'dejeuner') {
    global $alimentsDB;
    
    // Chercher la clé correspondante
    $foundKey = null;
    foreach ($alimentsDB as $key => $value) {
        if (strpos($allergieNom, $key) !== false || strpos($key, $allergieNom) !== false) {
            $foundKey = $key;
            break;
        }
    }
    
    if ($foundKey && isset($alimentsDB[$foundKey]['recettes'][$type])) {
        return $alimentsDB[$foundKey]['recettes'][$type];
    }
    
    // Recette par défaut
    return [
        'nom' => '🥗 Repas équilibré personnalisé',
        'ingredients' => ['Légumes frais', 'Protéines (viande/poisson/œufs)', 'Féculents (riz/pommes de terre)'],
        'preparation' => 'Consultez votre médecin pour un régime adapté à votre allergie.'
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AllergieBot - Diagnostic + Nutrition</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        
        .chat-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .message-animate {
            animation: slideIn 0.4s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="chat-container">

<div class="container mx-auto px-4 py-8 max-w-4xl min-h-screen flex flex-col">

    <!-- Header -->
    <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center">
                    <span class="text-3xl">🤖</span>
                </div>
                <div>
                    <h1 class="text-white font-bold text-xl">AllergieBot</h1>
                    <p class="text-white/70 text-sm">Diagnostic + Nutrition personnalisée</p>
                </div>
            </div>
            <?php if ($diagnostic['step'] > 0): ?>
                <form method="POST">
                    <button type="submit" name="reset" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition text-sm">
                        🔄 Nouveau diagnostic
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Zone de chat -->
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col" style="height: calc(100vh - 200px);">
        
        <div class="flex-1 overflow-y-auto p-6 space-y-4" id="chatMessages">
            
            <!-- ÉTAPE 0: Accueil -->
            <?php if ($diagnostic['step'] == 0 && !$response): ?>
                <div class="message-animate flex justify-start">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl rounded-tl-none px-6 py-4 max-w-[85%] text-white shadow-lg">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-2xl">🤖</span>
                            <span class="font-semibold">AllergieBot Nutrition</span>
                        </div>
                        <p class="leading-relaxed">
                            Bonjour ! 👋<br><br>
                            Je suis votre assistant spécialisé en allergies et nutrition.<br><br>
                            <strong>Comment ça marche ?</strong><br>
                            1️⃣ Décrivez vos symptômes → Je diagnostique votre allergie<br>
                            2️⃣ Je vous conseille sur l'alimentation (à éviter/à manger)<br>
                            3️⃣ Je vous propose des recettes pour déjeuner et dîner<br><br>
                            ✨ Commençons !
                        </p>
                    </div>
                </div>
                
                <div class="message-animate flex justify-start">
                    <div class="bg-gray-100 rounded-2xl rounded-tl-none px-6 py-4 max-w-[85%]">
                        <p class="text-sm text-gray-600 mb-3">📝 Décrivez vos symptômes :</p>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="fillSymptoms('urticaire, gonflement des lèvres, difficultés respiratoires')" class="text-sm bg-white hover:bg-gray-200 px-3 py-1 rounded-full shadow-sm transition">
                                🥜 Arachides
                            </button>
                            <button onclick="fillSymptoms('ballonnements, diarrhée, douleurs abdominales après boire lait')" class="text-sm bg-white hover:bg-gray-200 px-3 py-1 rounded-full shadow-sm transition">
                                🥛 Lactose
                            </button>
                            <button onclick="fillSymptoms('fatigue chronique, maux de tête, perte de poids')" class="text-sm bg-white hover:bg-gray-200 px-3 py-1 rounded-full shadow-sm transition">
                                🌾 Gluten
                            </button>
                            <button onclick="fillSymptoms('éternuements, nez qui coule, yeux qui piquent au printemps')" class="text-sm bg-white hover:bg-gray-200 px-3 py-1 rounded-full shadow-sm transition">
                                🌸 Pollen
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ÉTAPE 1: Diagnostic -->
            <?php if ($diagnostic['step'] == 1 && $response && isset($response['best_match']) && $response['best_match']): ?>
                <!-- Message utilisateur -->
                <div class="message-animate flex justify-end">
                    <div class="bg-blue-600 rounded-2xl rounded-tr-none px-6 py-3 max-w-[85%] text-white shadow">
                        <p class="leading-relaxed"><?= htmlspecialchars($_POST['symptoms'] ?? '') ?></p>
                    </div>
                </div>
                
                <!-- Diagnostic -->
                <div class="message-animate flex justify-start">
                    <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-none p-6 max-w-[95%] shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-4xl">🔍</span>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Diagnostic</h2>
                                <p class="text-sm text-gray-500">Analyse intelligente</p>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-4 mb-4">
                            <div class="flex items-center justify-between flex-wrap gap-3">
                                <div>
                                    <p class="text-sm text-gray-600">Allergie détectée :</p>
                                    <h3 class="text-2xl font-bold text-blue-800"><?= htmlspecialchars($response['best_match']['nom']) ?></h3>
                                    <?php 
                                        $graviteClass = match($response['best_match']['gravite']) {
                                            'grave' => 'bg-red-100 text-red-700',
                                            'moyenne' => 'bg-orange-100 text-orange-700',
                                            'faible' => 'bg-green-100 text-green-700',
                                            default => 'bg-gray-100'
                                        };
                                    ?>
                                    <span class="inline-block mt-1 px-2 py-1 rounded-full text-xs font-semibold <?= $graviteClass ?>">
                                        Gravité : <?= ucfirst($response['best_match']['gravite']) ?>
                                    </span>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-blue-600"><?= $response['best_match']['score'] ?>%</div>
                                    <div class="text-xs text-gray-500">confiance</div>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 mb-4 text-sm leading-relaxed">
                            <?= htmlspecialchars($response['best_match']['description']) ?>
                        </p>
                        
                        <?php if (!empty($response['best_match']['matched_symptoms'])): ?>
                            <div class="mb-4">
                                <p class="text-sm font-semibold text-green-700 mb-2">✓ Symptômes détectés :</p>
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach ($response['best_match']['matched_symptoms'] as $symptom): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                            <?= htmlspecialchars($symptom) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Traitements -->
                        <?php if (!empty($response['best_match']['traitements'])): ?>
                            <div class="mb-4">
                                <p class="text-sm font-semibold text-blue-800 mb-2">💊 Traitements recommandés :</p>
                                <div class="space-y-2">
                                    <?php foreach (array_slice($response['best_match']['traitements'], 0, 2) as $traitement): ?>
                                        <div class="bg-blue-50 rounded-lg p-2">
                                            <p class="font-semibold text-gray-800 text-sm">• <?= htmlspecialchars($traitement['nom_traitement']) ?></p>
                                            <?php if (!empty($traitement['conseils'])): ?>
                                                <p class="text-xs text-green-700 mt-1">📌 <?= htmlspecialchars(substr($traitement['conseils'], 0, 100)) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Question sur l'alimentation -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="bg-yellow-50 rounded-xl p-4">
                                <p class="font-semibold text-yellow-800 mb-2">🍽️ Question sur votre alimentation :</p>
                                <p class="text-gray-700 mb-3">Avez-vous remarqué des symptômes après avoir mangé certains aliments ?</p>
                                
                                <form method="POST">
                                    <div class="flex gap-3 flex-wrap">
                                        <button type="submit" name="alimentation_reponse" value="Oui, après certains repas" 
                                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                                            Oui, après certains repas
                                        </button>
                                        <button type="submit" name="alimentation_reponse" value="Non, pas de lien avec l'alimentation"
                                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                                            Non, pas de lien alimentaire
                                        </button>
                                        <button type="submit" name="alimentation_reponse" value="Je ne suis pas sûr"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                                            Je ne suis pas sûr
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ÉTAPE 2: Aliments à éviter et à manger -->
            <?php if ($diagnostic['step'] == 2 && $response && isset($response['best_match']) && $response['best_match']): 
                $allergieNom = $response['best_match']['nom'];
                $aEviter = getAliments($allergieNom, 'a_eviter');
                $aManger = getAliments($allergieNom, 'a_manger');
            ?>
                <div class="message-animate flex justify-start">
                    <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-none p-6 max-w-[95%] shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-4xl">🍽️</span>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Conseils nutritionnels</h2>
                                <p class="text-sm text-gray-500">Pour <?= htmlspecialchars($allergieNom) ?></p>
                            </div>
                        </div>
                        
                        <!-- Aliments à éviter -->
                        <div class="mb-4 p-3 bg-red-50 rounded-xl border border-red-200">
                            <h3 class="font-bold text-red-800 mb-2 flex items-center gap-2">
                                <span>🚫</span> Aliments à ÉVITER
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <?php if (!empty($aEviter)): ?>
                                    <?php foreach ($aEviter as $aliment): ?>
                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">
                                            <?= htmlspecialchars($aliment) ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-sm text-red-600">Consultez votre médecin pour une liste personnalisée</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Aliments à manger -->
                        <div class="mb-4 p-3 bg-green-50 rounded-xl border border-green-200">
                            <h3 class="font-bold text-green-800 mb-2 flex items-center gap-2">
                                <span>✅</span> Aliments À PRIVILÉGIER
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <?php if (!empty($aManger)): ?>
                                    <?php foreach ($aManger as $aliment): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">
                                            <?= htmlspecialchars($aliment) ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-sm text-green-600">Privilégiez une alimentation équilibrée</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Proposition de recettes -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="bg-blue-50 rounded-xl p-4">
                                <p class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
                                    <span>👨‍🍳</span> Envie de recettes adaptées ?
                                </p>
                                <div class="flex gap-3">
                                    <button onclick="demanderRecette('dejeuner')" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                                        🥗 Idée déjeuner
                                    </button>
                                    <button onclick="demanderRecette('diner')" 
                                            class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition">
                                        🍲 Idée dîner
                                    </button>
                                </div>
                                <form id="recetteForm" method="POST" class="hidden">
                                    <input type="hidden" name="demande_recettes" value="1">
                                    <input type="hidden" name="type_repas" id="type_repas_input">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ÉTAPE 3: Recettes -->
            <?php if ($diagnostic['step'] == 3 && isset($response['recette'])): 
                $recette = $response['recette'];
            ?>
                <div class="message-animate flex justify-end">
                    <div class="bg-blue-600 rounded-2xl rounded-tr-none px-5 py-2 max-w-[80%] text-white shadow">
                        <p>Je veux une idée pour <?= $diagnostic['type_repas'] === 'dejeuner' ? 'déjeuner' : 'dîner' ?> 🍽️</p>
                    </div>
                </div>
                
                <div class="message-animate flex justify-start">
                    <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-none p-6 max-w-[95%] shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-4xl">👨‍🍳</span>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($recette['nom']) ?></h2>
                                <p class="text-sm text-gray-500">Recette sans allergène</p>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                                <span>🛒</span> Ingrédients :
                            </h3>
                            <ul class="list-disc list-inside space-y-1 text-gray-700 text-sm">
                                <?php foreach ($recette['ingredients'] as $ingredient): ?>
                                    <li><?= htmlspecialchars($ingredient) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="mb-4">
                            <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                                <span>🔪</span> Préparation :
                            </h3>
                            <p class="text-gray-700 text-sm leading-relaxed">
                                <?= htmlspecialchars($recette['preparation']) ?>
                            </p>
                        </div>
                        
                        <div class="mt-4 pt-3 border-t border-gray-200 flex gap-3">
                            <button onclick="demanderRecette('dejeuner')" 
                                    class="text-sm bg-green-100 text-green-700 px-3 py-1 rounded-lg hover:bg-green-200 transition">
                                🥗 Autre déjeuner
                            </button>
                            <button onclick="demanderRecette('diner')" 
                                    class="text-sm bg-purple-100 text-purple-700 px-3 py-1 rounded-lg hover:bg-purple-200 transition">
                                🍲 Autre dîner
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>

        <!-- Formulaire initial (ÉTAPE 0 uniquement) -->
        <?php if ($diagnostic['step'] == 0 && !$response): ?>
            <div class="border-t border-gray-200 p-4 bg-gray-50">
                <form method="POST" id="symptomForm">
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <textarea name="symptoms" 
                                      id="symptomsInput"
                                      rows="3" 
                                      placeholder="Décrivez vos symptômes...&#10;&#10;Ex: urticaire, gonflement, difficultés respiratoires, ballonnements..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                      required></textarea>
                        </div>
                        <button type="submit" 
                                class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-xl font-semibold hover:shadow-lg transition h-fit">
                            Analyser
                            <span class="ml-2">🔍</span>
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
    </div>
    
    <div class="mt-4 text-center text-white/60 text-xs">
        <p>⚠️ Diagnostic assisté par IA - Consultez toujours un médecin pour un avis professionnel</p>
    </div>
    
</div>

<script>
    function fillSymptoms(text) {
        document.getElementById('symptomsInput').value = text;
        document.getElementById('symptomsInput').focus();
    }
    
    function demanderRecette(type) {
        document.getElementById('type_repas_input').value = type;
        document.getElementById('recetteForm').submit();
    }
    
    // Scroll auto
    const container = document.getElementById('chatMessages');
    if (container) container.scrollTop = container.scrollHeight;
</script>

<!-- Formulaire caché pour les recettes -->
<form id="recetteForm" method="POST" style="display: none;">
    <input type="hidden" name="demande_recettes" value="1">
    <input type="hidden" name="type_repas" id="type_repas_input">
</form>

</body>
</html>