<?php
/**
 * Test rapide de l'API get_summary.php
 */

echo "<h2>🧪 Test rapide de l'API get_summary.php</h2>";

// Simuler un appel POST à l'API
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['post_id' => 1]; // Tester avec le post ID 1

echo "<p>🔄 Test de l'appel API...</p>";

// Inclure l'API et capturer la sortie
ob_start();
try {
    include __DIR__ . '/api/get_summary.php';
    $output = ob_get_clean();

    // Analyser la réponse JSON
    $response = json_decode($output, true);

    if ($response && isset($response['success'])) {
        if ($response['success']) {
            echo "<p style='color: green;'>✅ API fonctionnelle !</p>";
            echo "<p><strong>Résumé généré:</strong> " . substr($response['summary'], 0, 100) . "...</p>";
            echo "<p><strong>Cached:</strong> " . ($response['cached'] ? 'Oui' : 'Non') . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Erreur API: " . ($response['error'] ?? 'Erreur inconnue') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Réponse JSON invalide</p>";
        echo "<pre>$output</pre>";
    }

} catch (Exception $e) {
    $output = ob_get_clean();
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
    if ($output) {
        echo "<pre>$output</pre>";
    }
}

echo "<hr>";
echo "<p><a href='blog.php'>← Retour au blog</a></p>";
?>