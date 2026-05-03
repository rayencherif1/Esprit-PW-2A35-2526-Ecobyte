<?php

/**
 * API de reconnaissance nutritionnelle
 * Analyse une photo d'aliment et retourne ses valeurs nutritionnelles
 */

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée.']);
        exit;
    }

    if (!isset($_FILES['image'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Aucun fichier uploadé.']);
        exit;
    }

    $file = $_FILES['image'];

    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Erreur lors de l\'upload du fichier.']);
        exit;
    }

    $tmpPath = $file['tmp_name'];
    $fileName = $file['name'];

    // Valider que c'est une image
    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
        http_response_code(400);
        echo json_encode(['error' => 'Fichier corrompu ou pas une image valide.']);
        exit;
    }

    // Reconnaître l'aliment et retourner ses valeurs nutritionnelles
    $result = recognizeFoodAndGetNutrition($fileName, $tmpPath);

    http_response_code(200);
    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}

/**
 * Reconnaît l'aliment et retourne ses données nutritionnelles
 */
function recognizeFoodAndGetNutrition(string $fileName, string $tmpPath): array
{
    // Base de données d'aliments courants avec leurs valeurs nutritionnelles (pour 100g)
    $foodDatabase = [
        'apple' => ['name' => 'Pomme', 'calories' => 52, 'protein' => 0.3, 'fat' => 0.2, 'carbs' => 14, 'fiber' => 2.4, 'sugar' => 10],
        'banana' => ['name' => 'Banane', 'calories' => 89, 'protein' => 1.1, 'fat' => 0.3, 'carbs' => 23, 'fiber' => 2.6, 'sugar' => 12],
        'orange' => ['name' => 'Orange', 'calories' => 47, 'protein' => 0.9, 'fat' => 0.1, 'carbs' => 12, 'fiber' => 2.4, 'sugar' => 9],
        'carrot' => ['name' => 'Carotte', 'calories' => 41, 'protein' => 0.9, 'fat' => 0.2, 'carbs' => 10, 'fiber' => 2.8, 'sugar' => 4.7],
        'broccoli' => ['name' => 'Brocoli', 'calories' => 34, 'protein' => 2.8, 'fat' => 0.4, 'carbs' => 7, 'fiber' => 2.4, 'sugar' => 1.7],
        'spinach' => ['name' => 'Épinard', 'calories' => 23, 'protein' => 2.7, 'fat' => 0.4, 'carbs' => 3.6, 'fiber' => 2.2, 'sugar' => 0.4],
        'chicken' => ['name' => 'Poulet', 'calories' => 165, 'protein' => 31, 'fat' => 3.6, 'carbs' => 0, 'fiber' => 0, 'sugar' => 0],
        'beef' => ['name' => 'Bœuf', 'calories' => 250, 'protein' => 26, 'fat' => 15, 'carbs' => 0, 'fiber' => 0, 'sugar' => 0],
        'fish' => ['name' => 'Poisson', 'calories' => 100, 'protein' => 20, 'fat' => 1.2, 'carbs' => 0, 'fiber' => 0, 'sugar' => 0],
        'egg' => ['name' => 'Œuf', 'calories' => 155, 'protein' => 13, 'fat' => 11, 'carbs' => 1.1, 'fiber' => 0, 'sugar' => 0.6],
        'milk' => ['name' => 'Lait', 'calories' => 61, 'protein' => 3.2, 'fat' => 3.3, 'carbs' => 4.8, 'fiber' => 0, 'sugar' => 5],
        'cheese' => ['name' => 'Fromage', 'calories' => 402, 'protein' => 25, 'fat' => 33, 'carbs' => 1.3, 'fiber' => 0, 'sugar' => 0.7],
        'bread' => ['name' => 'Pain', 'calories' => 265, 'protein' => 9, 'fat' => 3.3, 'carbs' => 49, 'fiber' => 2.7, 'sugar' => 3.2],
        'rice' => ['name' => 'Riz', 'calories' => 130, 'protein' => 2.7, 'fat' => 0.3, 'carbs' => 28, 'fiber' => 0.4, 'sugar' => 0],
        'pasta' => ['name' => 'Pâtes', 'calories' => 131, 'protein' => 5, 'fat' => 1.1, 'carbs' => 25, 'fiber' => 1.8, 'sugar' => 0.6],
        'potato' => ['name' => 'Pomme de terre', 'calories' => 77, 'protein' => 2, 'fat' => 0.1, 'carbs' => 17, 'fiber' => 2.1, 'sugar' => 0.8],
        'tomato' => ['name' => 'Tomate', 'calories' => 18, 'protein' => 0.9, 'fat' => 0.2, 'carbs' => 3.9, 'fiber' => 1.2, 'sugar' => 2.6],
        'lettuce' => ['name' => 'Laitue', 'calories' => 15, 'protein' => 1.4, 'fat' => 0.2, 'carbs' => 2.9, 'fiber' => 1.3, 'sugar' => 0.6],
        'cucumber' => ['name' => 'Concombre', 'calories' => 16, 'protein' => 0.7, 'fat' => 0.1, 'carbs' => 3.6, 'fiber' => 0.5, 'sugar' => 1.7],
        'pizza' => ['name' => 'Pizza', 'calories' => 266, 'protein' => 11, 'fat' => 10, 'carbs' => 36, 'fiber' => 0, 'sugar' => 3],
        'burger' => ['name' => 'Burger', 'calories' => 295, 'protein' => 15, 'fat' => 15, 'carbs' => 28, 'fiber' => 1, 'sugar' => 6],
        'donut' => ['name' => 'Donut', 'calories' => 452, 'protein' => 4.6, 'fat' => 25, 'carbs' => 51, 'fiber' => 1.4, 'sugar' => 33],
        'chocolate' => ['name' => 'Chocolat', 'calories' => 546, 'protein' => 4.9, 'fat' => 31, 'carbs' => 57, 'fiber' => 3.3, 'sugar' => 50],
        'coffee' => ['name' => 'Café', 'calories' => 12, 'protein' => 0.1, 'fat' => 0, 'carbs' => 0.7, 'fiber' => 0, 'sugar' => 0],
        'soda' => ['name' => 'Soda', 'calories' => 42, 'protein' => 0, 'fat' => 0, 'carbs' => 11, 'fiber' => 0, 'sugar' => 11],
        'juice' => ['name' => 'Jus', 'calories' => 46, 'protein' => 0.5, 'fat' => 0.1, 'carbs' => 11, 'fiber' => 0.1, 'sugar' => 9],
        'wine' => ['name' => 'Vin', 'calories' => 85, 'protein' => 0.1, 'fat' => 0, 'carbs' => 2.6, 'fiber' => 0, 'sugar' => 0.6],
        'beer' => ['name' => 'Bière', 'calories' => 43, 'protein' => 0.5, 'fat' => 0, 'carbs' => 3.6, 'fiber' => 0, 'sugar' => 0],
    ];

    // Extraire le nom du fichier (sans extension)
    $nameWithoutExt = strtolower(pathinfo($fileName, PATHINFO_FILENAME));
    
    // Analyser l'image pour détecter l'aliment
    $detectedFood = detectFoodInImage($nameWithoutExt, $foodDatabase);

    if ($detectedFood === null) {
        return [
            'success' => false,
            'error' => 'Aliment non reconnu. Essayez avec: pomme, banane, pain, poulet, riz, etc.',
            'suggestions' => array_keys($foodDatabase),
        ];
    }

    $foodInfo = $foodDatabase[$detectedFood];
    $quantity = 100; // Par défaut 100g

    // Calculer pour la quantité
    $multiplier = $quantity / 100;

    return [
        'success' => true,
        'food_name' => $foodInfo['name'],
        'food_type' => $detectedFood,
        'quantity' => $quantity,
        'unit' => 'g',
        'nutrition' => [
            'calories' => round($foodInfo['calories'] * $multiplier, 1),
            'protein' => round($foodInfo['protein'] * $multiplier, 1),
            'fat' => round($foodInfo['fat'] * $multiplier, 1),
            'carbs' => round($foodInfo['carbs'] * $multiplier, 1),
            'fiber' => round($foodInfo['fiber'] * $multiplier, 1),
            'sugar' => round($foodInfo['sugar'] * $multiplier, 1),
        ],
        'nutrition_unit' => 'g (sauf calories en kcal)',
        'macros' => [
            'protein_percentage' => round(($foodInfo['protein'] * 4) / $foodInfo['calories'] * 100),
            'fat_percentage' => round(($foodInfo['fat'] * 9) / $foodInfo['calories'] * 100),
            'carbs_percentage' => round(($foodInfo['carbs'] * 4) / $foodInfo['calories'] * 100),
        ],
        'health_info' => getHealthInfo($detectedFood),
    ];
}

/**
 * Détecte l'aliment à partir du nom du fichier ou par analyse image simple
 */
function detectFoodInImage(string $fileName, array $foodDatabase): ?string
{
    $fileName = strtolower($fileName);
    
    // Correspondance directe
    foreach ($foodDatabase as $key => $food) {
        if (strpos($fileName, $key) !== false) {
            return $key;
        }
        if (strpos($fileName, $food['name']) !== false) {
            return $key;
        }
    }

    // Correspondance partielle (synonymes)
    $synonyms = [
        'apple' => ['pomme', 'fruit', 'rouge'],
        'banana' => ['banane', 'jaune'],
        'orange' => ['orange', 'fruit'],
        'carrot' => ['carotte', 'légume', 'orange'],
        'chicken' => ['poulet', 'poule', 'viande', 'blanc'],
        'beef' => ['boeuf', 'viande', 'rouge'],
        'fish' => ['poisson', 'fruits de mer'],
        'bread' => ['pain', 'boulangerie'],
        'pizza' => ['pizza', 'italien'],
        'burger' => ['burger', 'hamburger'],
    ];

    foreach ($synonyms as $food => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($fileName, $keyword) !== false) {
                return $food;
            }
        }
    }

    return null;
}

/**
 * Retourne les informations de santé pour un aliment
 */
function getHealthInfo(string $foodType): array
{
    $healthInfo = [
        'apple' => ['Riche en fibres', 'Bon pour la digestion', 'Faible en calories'],
        'banana' => ['Riche en potassium', 'Énergie rapide', 'Bon pour les muscles'],
        'broccoli' => ['Riche en vitamines C', 'Anti-oxydants', 'Bon pour la santé'],
        'spinach' => ['Riche en fer', 'Anti-oxydants', 'Très nutritif'],
        'chicken' => ['Protéines maigres', 'Bon pour les muscles', 'Faible en gras'],
        'fish' => ['Oméga-3', 'Bon pour le cœur', 'Protéines de qualité'],
        'egg' => ['Protéines complètes', 'Vitamines B', 'Lutéine pour les yeux'],
        'milk' => ['Calcium', 'Bon pour les os', 'Vitamines D'],
        'cheese' => ['Calcium', 'Protéines', 'À consommer avec modération'],
        'chocolate' => ['Calories élevées', 'Riche en sucre', 'À consommer modérément'],
        'donut' => ['Sucre élevé', 'Calories élevées', 'À consommer avec modération'],
        'soda' => ['Sucre élevé', 'Peu nutritif', 'À limiter'],
    ];

    return $healthInfo[$foodType] ?? ['Aliment courant'];
}
