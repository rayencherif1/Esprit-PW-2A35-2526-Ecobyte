<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';
require_once __DIR__ . '/../../../Controller/traitement.Controller.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id_allergie = $input['id_allergie'] ?? null;
    
    if (!$id_allergie) {
        throw new Exception('ID allergie manquant');
    }
    
    $allergieC = new AllergieC();
    $traitementC = new TraitementC();
    
    $allergie = $allergieC->getAllergieById($id_allergie);
    $traitements = $traitementC->listTraitementByAllergie($id_allergie);
    
    if (!$allergie) {
        throw new Exception('Allergie non trouvée');
    }
    
    $analyse = getGroqDynamicAnalysis($allergie, $traitements);
    
    echo json_encode([
        'success' => true,
        'data' => $analyse
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

function getGroqDynamicAnalysis($allergie, $traitements) {
    $api_key = '';
    
    // Perspectives variées pour des analyses uniques
    $perspectives = [
        "clinique et thérapeutique",
        "préventive et éducative", 
        "recherche et innovation",
        "pratique quotidienne",
        "gestion de crise",
        "approche holistique"
    ];
    
    $randomPerspective = $perspectives[array_rand($perspectives)];
    $timestamp = time();
    $seed = rand(10000, 99999);
    
    $prompt = "=== GÉNÉRATION D'UNE ANALYSE UNIQUE (Seed: $seed | $timestamp) ===\n\n";
    $prompt .= "IMPORTANT: Cette analyse doit être COMPLÈTEMENT DIFFÉRENTE de toute analyse précédente.\n";
    $prompt .= "Ne réutilise JAMAIS les mêmes phrases, mêmes exemples ou mêmes formulations.\n\n";
    
    $prompt .= "=== DONNÉES ===\n";
    $prompt .= "Allergie: " . ($allergie['nom'] ?? 'Non spécifié') . "\n";
    $prompt .= "Gravité: " . ($allergie['gravite'] ?? 'Non définie') . "\n";
    $prompt .= "Description: " . ($allergie['description'] ?? 'Non renseignée') . "\n";
    $prompt .= "Symptômes: " . ($allergie['symptomes'] ?? 'Non renseignés') . "\n\n";
    
    $prompt .= "=== TRAITEMENTS ===\n";
    if (empty($traitements)) {
        $prompt .= "Aucun traitement enregistré.\n";
    } else {
        foreach ($traitements as $t) {
            $prompt .= "- " . ($t['nom_traitement'] ?? 'Sans nom') . "\n";
        }
    }
    $prompt .= "\n";
    
    $prompt .= "=== INSTRUCTIONS ===\n";
    $prompt .= "Adopte une perspective $randomPerspective.\n";
    $prompt .= "Sois ORIGINAL et CRÉATIF.\n";
    $prompt .= "Utilise un langage professionnel mais varié.\n\n";
    
    $prompt .= "=== FORMAT JSON ===\n";
    $prompt .= "{\n";
    $prompt .= "  \"synthese_unique\": \"Synthèse originale et détaillée (120-150 mots)\",\n";
    $prompt .= "  \"analyse_approfondie\": \"Analyse sous l'angle $randomPerspective (80-100 mots)\",\n";
    $prompt .= "  \"recommandations_sur_mesure\": [\"Recommandation 1\", \"Recommandation 2\", \"Recommandation 3\"],\n";
    $prompt .= "  \"traitements_innovants\": [\"Thérapie innovante 1\", \"Thérapie 2\", \"Thérapie 3\"],\n";
    $prompt .= "  \"aller_plus_loin\": \"Conseil personnalisé et unique pour ce patient\"\n";
    $prompt .= "}\n";
    
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'llama-3.1-8b-instant',
        'messages' => [
            [
                'role' => 'system',
                'content' => "Tu es un allergologue expert. Tu génères des analyses MÉDICALES UNIQUES à chaque fois. Tu ne te répètes jamais. Tu utilises un vocabulaire varié et des formulations originales. Tu réponds UNIQUEMENT en JSON valide."
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.95,
        'max_tokens' => 1500
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            $content = $result['choices'][0]['message']['content'];
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                $json = json_decode($matches[0], true);
                if ($json) return $json;
            }
        }
    }
    
    // Fallback créatif
    return [
        "synthese_unique" => "Analyse personnalisée pour " . ($allergie['nom'] ?? 'cette allergie') . ". Approche thérapeutique individualisée recommandée.",
        "analyse_approfondie" => "Évaluation complète nécessaire. Consultation spécialisée préconisée.",
        "recommandations_sur_mesure" => ["Consultation spécialisée", "Bilan allergologique", "Plan d'action"],
        "traitements_innovants" => ["Immunothérapie", "Biothérapies", "Désensibilisation"],
        "aller_plus_loin" => "Suivi régulier recommandé"
    ];
}
?>