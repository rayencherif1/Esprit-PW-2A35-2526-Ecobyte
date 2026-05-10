<?php
/**
 * Test du système de résumé automatique GRATUIT
 */

require_once __DIR__ . '/controller/ai_summary.php';

echo "<h1>🆓 Test du système de résumé GRATUIT</h1>";
echo "<p style='color: green; font-weight: bold;'>✅ Aucun coût - Fonctionne sans API externe !</p>";

// Exemples d'articles sur la nutrition et écologie
$testArticles = [
    [
        'title' => 'Alimentation durable : les clés pour réduire son empreinte carbone',
        'content' => 'L\'alimentation représente 25% des émissions de gaz à effet de serre d\'un ménage français. En privilégiant les produits locaux et de saison, nous pouvons diviser par 10 l\'empreinte carbone de nos repas. Les légumes bio cultivés dans un rayon de 100km autour de chez soi permettent de limiter le transport et favorisent la biodiversité locale. Une alimentation végétale bien équilibrée apporte tous les nutriments nécessaires tout en préservant les ressources de la planète.'
    ],
    [
        'title' => 'Les bienfaits des protéines végétales',
        'content' => 'Les protéines végétales offrent de nombreux avantages pour la santé et l\'environnement. Issues des légumineuses, céréales et graines, elles contiennent moins de graisses saturées que les protéines animales. Le quinoa, les lentilles et les pois chiches sont particulièrement riches en fer et en fibres. Une alimentation basée sur les plantes réduit le risque de maladies cardiovasculaires et contribue à la préservation des sols agricoles.'
    ],
    [
        'title' => 'Zéro déchet : l\'art de cuisiner sans gaspiller',
        'content' => 'Le gaspillage alimentaire représente 10% des émissions de CO2 liées à l\'alimentation. Pour éviter le déchet, il faut planifier ses repas à l\'avance et utiliser tous les ingrédients. Les épluchures de légumes peuvent servir à faire des bouillons riches en nutriments. Les restes de pain rassis donnent d\'excellents desserts ou chapelure. Une cuisine créative permet de valoriser chaque partie des aliments et de réduire considérablement son impact environnemental.'
    ]
];

echo "<div style='margin: 20px 0;'>";
foreach ($testArticles as $index => $article) {
    echo "<div style='background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #28a745;'>";
    echo "<h3>📝 Article " . ($index + 1) . ": {$article['title']}</h3>";
    echo "<p><strong>Contenu original :</strong><br><em>" . htmlspecialchars(substr($article['content'], 0, 200)) . "...</em></p>";

    // Générer le résumé
    $summary = generateSummary($article['content']);

    echo "<p><strong>🤖 Résumé automatique (GRATUIT) :</strong><br><span style='color: #28a745; font-weight: bold;'>" . htmlspecialchars($summary) . "</span></p>";

    // Statistiques
    $originalLength = strlen($article['content']);
    $summaryLength = strlen($summary);
    $ratio = round(($summaryLength / $originalLength) * 100, 1);

    echo "<div style='background: #e9ecef; padding: 8px; border-radius: 4px; margin-top: 10px; font-size: 0.9em;'>";
    echo "<strong>📊 Stats :</strong> {$originalLength} → {$summaryLength} caractères ({$ratio}% du texte original)";
    echo "</div>";

    echo "</div>";
}

echo "</div>";

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>🎉 Avantages du système GRATUIT :</h3>";
echo "<ul>";
echo "<li>✅ <strong>0€ de coût</strong> - Aucun abonnement requis</li>";
echo "<li>✅ <strong>Instantané</strong> - Pas d'appel réseau</li>";
echo "<li>✅ <strong>Privé</strong> - Tout reste sur votre serveur</li>";
echo "<li>✅ <strong>Spécialisé</strong> - Optimisé pour nutrition/écologie</li>";
echo "<li>✅ <strong>Fiable</strong> - Fonctionne toujours, même sans internet</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><a href='blog.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Retour au blog</a></p>";
?>