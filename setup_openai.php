<?php
/**
 * Script de configuration et test de l'API OpenAI
 */

require_once __DIR__ . '/config_ai.php';
require_once __DIR__ . '/controller/ai_summary.php';

$message = '';
$testResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['api_key'])) {
        // Sauvegarder la clé API
        $newKey = trim($_POST['api_key']);
        if (!empty($newKey)) {
            // Mettre à jour le fichier config_ai.php
            $configContent = file_get_contents(__DIR__ . '/config_ai.php');
            $configContent = preg_replace(
                "/define\('OPENAI_API_KEY', .*?\);/",
                "define('OPENAI_API_KEY', '" . addslashes($newKey) . "');",
                $configContent
            );
            file_put_contents(__DIR__ . '/config_ai.php', $configContent);

            $message = "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>✅ Clé API sauvegardée ! Rechargez la page pour appliquer les changements.</div>";
        }
    } elseif (isset($_POST['test_api'])) {
        // Tester l'API avec un contenu d'exemple
        $testContent = "La nutrition durable est essentielle pour notre planète. En choisissant des aliments locaux et de saison, nous réduisons notre empreinte carbone. Les légumes bio contribuent à la biodiversité et à la santé des sols. Une alimentation équilibrée incluant des protéines végétales permet de maintenir notre santé tout en préservant l'environnement.";

        try {
            $summary = generateSummary($testContent);
            $testResult = ['success' => true, 'summary' => $summary];
        } catch (Exception $e) {
            $testResult = ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

$currentKey = OPENAI_API_KEY;
$keyConfigured = ($currentKey !== 'sk-your-api-key-here' && !empty($currentKey));
$maskedKey = $keyConfigured ? substr($currentKey, 0, 10) . '...' . substr($currentKey, -4) : '';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration API OpenAI - Ecobyte</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; }
        button { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #2980b9; }
        .test-section { background: #ecf0f1; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .result { margin: 10px 0; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .status { padding: 10px; border-radius: 4px; margin: 10px 0; }
        .configured { background: #d4edda; color: #155724; }
        .not-configured { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Configuration API OpenAI</h1>

        <?php echo $message; ?>

        <div class="status <?php echo $keyConfigured ? 'configured' : 'not-configured'; ?>">
            <strong>Statut :</strong> <?php echo $keyConfigured ? '✅ Clé API configurée (' . $maskedKey . ')' : '❌ Clé API non configurée'; ?>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="api_key">Clé API OpenAI :</label>
                <input type="text"
                       id="api_key"
                       name="api_key"
                       placeholder="sk-..."
                       value="<?php echo htmlspecialchars($currentKey === 'sk-your-api-key-here' ? '' : $currentKey); ?>">
                <small style="color: #666;">
                    Obtenez votre clé sur <a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI Platform</a>
                </small>
            </div>
            <button type="submit">💾 Sauvegarder la clé API</button>
        </form>

        <div class="test-section">
            <h3>🧪 Tester l'API</h3>
            <p>Cliquez sur le bouton ci-dessous pour tester la génération de résumé avec un exemple.</p>

            <form method="POST" style="display: inline;">
                <button type="submit" name="test_api" <?php echo !$keyConfigured ? 'disabled' : ''; ?>>
                    🚀 Tester la génération de résumé
                </button>
            </form>

            <?php if ($testResult): ?>
                <div class="result <?php echo $testResult['success'] ? 'success' : 'error'; ?>">
                    <strong><?php echo $testResult['success'] ? '✅ Test réussi' : '❌ Erreur'; ?> :</strong><br>
                    <?php if ($testResult['success']): ?>
                        <strong>Résumé généré :</strong><br>
                        <?php echo htmlspecialchars($testResult['summary']); ?>
                    <?php else: ?>
                        <?php echo htmlspecialchars($testResult['error']); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="info">
            <h4>📋 Configuration requise :</h4>
            <ul>
                <li>Clé API OpenAI valide</li>
                <li>Extension cURL activée dans PHP</li>
                <li>Connexion internet pour les appels API</li>
            </ul>

            <h4>🎯 Modèle utilisé :</h4>
            <ul>
                <li><strong>Modèle :</strong> <?php echo OPENAI_MODEL; ?></li>
                <li><strong>Max tokens :</strong> <?php echo SUMMARY_MAX_TOKENS; ?></li>
                <li><strong>Température :</strong> <?php echo SUMMARY_TEMPERATURE; ?></li>
            </ul>
        </div>

        <p style="text-align: center; margin-top: 20px;">
            <a href="blog.php">← Retour au blog</a> |
            <a href="admin/summaries.php">Interface admin</a>
        </p>
    </div>
</body>
</html>