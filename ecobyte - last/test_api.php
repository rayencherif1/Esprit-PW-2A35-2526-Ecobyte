<?php

// Script de test pour l'API get_summary.php
echo "Test de l'API get_summary.php\n";
echo "==============================\n\n";

// Test 1: Vérifier que le fichier existe
$apiFile = __DIR__ . '/api/get_summary.php';
if (file_exists($apiFile)) {
    echo "✅ Fichier API trouvé: $apiFile\n";
} else {
    echo "❌ Fichier API manquant: $apiFile\n";
    exit(1);
}

// Test 2: Vérifier que les dépendances existent
$dependencies = [
    __DIR__ . '/controller/ai_summary.php',
    __DIR__ . '/controller/post.controller.php',
    __DIR__ . '/config.php'
];

foreach ($dependencies as $dep) {
    if (file_exists($dep)) {
        echo "✅ Dépendance trouvée: " . basename($dep) . "\n";
    } else {
        echo "❌ Dépendance manquante: " . basename($dep) . "\n";
    }
}

// Test 3: Simuler un appel à l'API
echo "\nTest d'appel à l'API:\n";
echo "--------------------\n";

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['post_id' => 1]; // Simuler un POST

// Inclure l'API et capturer la sortie
ob_start();
try {
    include $apiFile;
    $output = ob_get_clean();

    // Vérifier si c'est du JSON valide
    $jsonData = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ Réponse JSON valide:\n";
        echo json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "❌ Réponse n'est pas du JSON valide:\n";
        echo "Erreur JSON: " . json_last_error_msg() . "\n";
        echo "Contenu brut:\n$output\n";
    }
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "❌ Erreur lors de l'exécution de l'API:\n";
    echo $e->getMessage() . "\n";
    if ($output) {
        echo "Sortie capturée:\n$output\n";
    }
}

echo "\nTest terminé.\n";
?>