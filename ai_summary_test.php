<?php

/**
 * Test simple de la génération de résumé IA
 * Accédez à http://localhost/ai_summary_test.php pour tester
 */

require_once __DIR__ . '/controller/ai_summary.php';
require_once __DIR__ . '/controller/post.controller.php';

$testResult = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testContent = trim($_POST['content'] ?? '');
    
    if (empty($testContent)) {
        $error = 'Veuillez entrer un contenu à résumer.';
    } else {
        try {
            $summary = generateSummary($testContent);
            if (empty($summary)) {
                $error = 'Le contenu est trop court pour être résumé (minimum 50 caractères).';
            } else {
                $testResult = [
                    'input_length' => strlen($testContent),
                    'summary_length' => strlen($summary),
                    'summary' => $summary
                ];
            }
        } catch (Exception $e) {
            $error = 'Erreur: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Génération de résumé IA</title>
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-top: 0;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .info {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        
        form {
            margin: 20px 0;
        }
        
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        textarea {
            width: 100%;
            min-height: 150px;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }
        
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        button {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .result {
            background: #f9f9f9;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .result.success {
            background: #f0fdf4;
            border-color: #86efac;
        }
        
        .result.error {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        
        .result-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .success .result-title {
            color: #16a34a;
        }
        
        .error .result-title {
            color: #dc2626;
        }
        
        .summary-box {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #667eea;
            margin: 10px 0;
            line-height: 1.6;
            font-style: italic;
        }
        
        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
            font-size: 13px;
        }
        
        .stat-item {
            background: #f0f4ff;
            padding: 10px;
            border-radius: 6px;
        }
        
        .stat-label {
            color: #666;
            font-weight: 600;
        }
        
        .stat-value {
            color: #333;
            font-weight: 700;
            font-size: 16px;
        }
        
        .examples {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e0e0e0;
        }
        
        .examples h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .example-item {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .example-item:hover {
            background: #f0f0f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .example-item small {
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Test - Résumé IA Ecobyte</h1>
        
        <div class="info">
            <strong>ℹ️ Information:</strong> Cet outil teste la génération de résumés IA pour vos articles. 
            Entrez un texte d'au moins 50 caractères et cliquez sur "Générer le résumé" pour voir le résultat.
        </div>
        
        <form method="POST">
            <label for="content">Contenu à résumer :</label>
            <textarea id="content" name="content" placeholder="Collez ici le contenu d'un article à résumer..."><?= htmlspecialchars($_POST['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            
            <div class="button-group">
                <button type="submit" class="btn-primary">🚀 Générer le résumé</button>
                <button type="reset" class="btn-secondary">Effacer</button>
            </div>
        </form>
        
        <?php if ($error) { ?>
            <div class="result error">
                <div class="result-title">⚠️ Erreur</div>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php } ?>
        
        <?php if ($testResult) { ?>
            <div class="result success">
                <div class="result-title">✓ Résumé généré avec succès</div>
                
                <div class="summary-box">
                    <?= htmlspecialchars($testResult['summary'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-label">Caractères (entrée)</div>
                        <div class="stat-value"><?= $testResult['input_length'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Caractères (résumé)</div>
                        <div class="stat-value"><?= $testResult['summary_length'] ?></div>
                    </div>
                </div>
            </div>
        <?php } ?>
        
        <div class="examples">
            <h3>📝 Exemples de textes à tester :</h3>
            
            <div class="example-item" onclick="setExample('Les antioxydants sont des molécules qui aident à combattre les radicaux libres dans notre corps. On les trouve principalement dans les fruits et légumes colorés, tels que les baies, les tomates, les carottes et les épinards. Ils jouent un rôle crucial dans la prévention du vieillissement prématuré et de nombreuses maladies chroniques. Pour maximiser votre apport en antioxydants, consommez au moins 5 portions de fruits et légumes par jour.')">
                <strong>Antioxydants et nutrition</strong>
                <small>Article sur les antioxydants dans l'alimentation</small>
            </div>
            
            <div class="example-item" onclick="setExample('Le changement climatique a un impact significatif sur notre système alimentaire. Les cultures sont affectées par les variations de température et de précipitations, ce qui réduit le rendement agricole. L\'élevage intensif contribue également à l\'émission de gaz à effet de serre. Pour une alimentation plus durable et respectueuse de l\'environnement, privilégiez les produits locaux, les aliments de saison et réduisez votre consommation de viande. Vous contribuerez ainsi à la protection de la planète tout en améliorant votre santé.')">
                <strong>Alimentation durable et écologie</strong>
                <small>Article sur l'impact écologique de notre alimentation</small>
            </div>
            
            <div class="example-item" onclick="setExample('Les probiotiques sont des bactéries bénéfiques qui vivent dans votre intestin et contribuent à votre santé digestive. Ils améliorent l\'absorption des nutriments, renforcent votre système immunitaire et aident à maintenir un microbiome équilibré. Les sources naturelles de probiotiques incluent le yaourt, le kéfir, la choucroute et les aliments fermentés. Consommer régulièrement des aliments riches en probiotiques peut améliorer votre digestion et renforcer votre bien-être général.')">
                <strong>Probiotiques et santé digestive</strong>
                <small>Article sur les probiotiques et la flore intestinale</small>
            </div>
        </div>
    </div>
    
    <script>
        function setExample(text) {
            document.getElementById('content').value = text;
            document.getElementById('content').focus();
        }
    </script>
</body>
</html>
