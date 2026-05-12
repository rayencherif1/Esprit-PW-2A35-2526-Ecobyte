<?php
/**
 * Test rapide de l'API OpenAI avec résumé réel
 */

require_once __DIR__ . '/controller/ai_summary.php';

echo "<h2>🧪 Test de génération de résumé IA réel</h2>";

// Contenu de test représentatif du blog Ecobyte
$testContent = "L'alimentation durable représente un défi majeur pour notre société moderne. En optant pour des produits locaux et de saison, nous pouvons réduire considérablement notre empreinte carbone. Les légumes biologiques cultivés dans le respect des cycles naturels offrent non seulement une meilleure qualité nutritionnelle, mais contribuent également à la préservation de la biodiversité. Il est essentiel d'adopter une approche holistique qui considère à la fois notre santé personnelle et l'impact environnemental de nos choix alimentaires.";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📝 Contenu original à résumer :</h3>";
echo "<p><em>" . htmlspecialchars($testContent) . "</em></p>";
echo "</div>";

try {
    echo "<h3>🤖 Génération du résumé...</h3>";
    $summary = generateSummary($testContent);

    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #c3e6cb;'>";
    echo "<h3>✅ Résumé généré avec succès :</h3>";
    echo "<p><strong>" . htmlspecialchars($summary) . "</strong></p>";
    echo "</div>";

    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>📊 Statistiques :</strong><br>";
    echo "• Longueur originale : " . strlen($testContent) . " caractères<br>";
    echo "• Longueur résumé : " . strlen($summary) . " caractères<br>";
    echo "• Ratio : " . round((strlen($summary) / strlen($testContent)) * 100, 1) . "%";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #f5c6cb;'>";
    echo "<h3>❌ Erreur lors de la génération :</h3>";
    echo "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";

    if (strpos($e->getMessage(), 'non configurée') !== false) {
        echo "<p>💡 <a href='setup_openai.php'>Configurez votre clé API OpenAI ici</a></p>";
    }
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='blog.php'>← Retour au blog</a> | <a href='setup_openai.php'>🔧 Configuration API</a></p>";
?>