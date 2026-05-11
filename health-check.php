<?php

/**
 * Script de vérification de santé du système Ecobyte
 * Permet de vérifier que tout est correctement configuré
 */

$checks = [];
$passed = 0;
$failed = 0;

function addCheck($name, $passed, $message = '') {
    global $checks, $passed as $p, $failed as $f;
    $checks[] = [
        'name' => $name,
        'passed' => $passed,
        'message' => $message
    ];
    if ($passed) $p++; else $f++;
}

// 1. Vérifier la connexion à la base de données
try {
    require_once __DIR__ . '/config.php';
    $db = config::getConnexion();
    $result = $db->query("SELECT 1");
    addCheck('Connexion base de données', true, 'MySQL connecté');
} catch (Exception $e) {
    addCheck('Connexion base de données', false, $e->getMessage());
}

// 2. Vérifier la colonne summary
try {
    require_once __DIR__ . '/config.php';
    $db = config::getConnexion();
    $result = $db->query("DESCRIBE post");
    $columns = array_column($result->fetchAll(PDO::FETCH_ASSOC), 'Field');
    $hasSummary = in_array('summary', $columns);
    addCheck('Colonne summary dans post', $hasSummary, $hasSummary ? 'Migration appliquée' : 'Exécutez migration_add_summary.sql');
} catch (Exception $e) {
    addCheck('Colonne summary dans post', false, $e->getMessage());
}

// 3. Vérifier la clé API OpenAI
require_once __DIR__ . '/config_ai.php';
$apiKey = OPENAI_API_KEY;
$hasApiKey = !empty($apiKey) && $apiKey !== 'sk-your-api-key-here';
addCheck('Clé API OpenAI', $hasApiKey, $hasApiKey ? 'Configurée' : 'À configurer dans config_ai.php');

// 4. Vérifier les fichiers IA
$aiFiles = [
    'controller/ai_summary.php',
    'api/get_summary.php',
    'config_ai.php',
];
foreach ($aiFiles as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    addCheck("Fichier: $file", $exists, $exists ? 'Présent' : 'Manquant');
}

// 5. Vérifier les fichiers front-office
$frontFiles = [
    'view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/js/ai-summary.js',
    'view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/css/ai-summary.css',
];
foreach ($frontFiles as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    addCheck("Fichier: $file", $exists, $exists ? 'Présent' : 'Manquant');
}

// 6. Vérifier les fichiers admin
$adminFiles = [
    'admin/summaries.php',
];
foreach ($adminFiles as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    addCheck("Fichier: $file", $exists, $exists ? 'Présent' : 'Manquant');
}

// 7. Vérifier les permissions des dossiers
$uploadDir = __DIR__ . '/view/uploads';
$isWritable = is_writable($uploadDir);
addCheck('Permissions view/uploads/', $isWritable, $isWritable ? 'Accessible en écriture' : 'À modifier (chmod 755)');

// 8. Tester la fonction generateSummary
try {
    require_once __DIR__ . '/controller/ai_summary.php';
    $testContent = "Ceci est un test de résumé. " . str_repeat("Lorem ipsum dolor sit amet. ", 10);
    $summary = generateSummary($testContent);
    $success = !empty($summary);
    addCheck('Fonction generateSummary()', $success, $success ? 'Fonctionne' : 'Erreur lors de la génération');
} catch (Exception $e) {
    addCheck('Fonction generateSummary()', false, $e->getMessage());
}

// 9. Vérifier la modification du fichier blog.php
try {
    $blogContent = file_get_contents(__DIR__ . '/blog.php');
    $hasIntegration = strpos($blogContent, 'ai-summary-container') !== false;
    addCheck('Intégration blog.php', $hasIntegration, $hasIntegration ? 'Intégrée' : 'À intégrer');
} catch (Exception $e) {
    addCheck('Intégration blog.php', false, $e->getMessage());
}

// 10. Vérifier curl
$curlEnabled = function_exists('curl_init');
addCheck('Extension cURL', $curlEnabled, $curlEnabled ? 'Activée' : 'À activer');

// 11. Vérifier json_encode
$jsonSupport = function_exists('json_encode');
addCheck('Support JSON', $jsonSupport, $jsonSupport ? 'Disponible' : 'À activer');

// 12. Vérifier PDO
$pdoSupport = extension_loaded('pdo') && extension_loaded('pdo_mysql');
addCheck('Support PDO MySQL', $pdoSupport, $pdoSupport ? 'Disponible' : 'À installer');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Check - Ecobyte IA</title>
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        h1 {
            color: #333;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .summary-item {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
        }
        
        .summary-item.passed {
            background: #d4edda;
            color: #155724;
        }
        
        .summary-item.failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .summary-item-value {
            font-size: 24px;
            font-weight: 700;
        }
        
        .summary-item-label {
            font-size: 12px;
            margin-top: 5px;
        }
        
        .checks {
            margin-top: 30px;
        }
        
        .check-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #ddd;
        }
        
        .check-item.success {
            background: #f0fdf4;
            border-left-color: #86efac;
        }
        
        .check-item.failed {
            background: #fef2f2;
            border-left-color: #fca5a5;
        }
        
        .check-icon {
            font-size: 24px;
            margin-right: 15px;
            min-width: 30px;
        }
        
        .check-content {
            flex: 1;
        }
        
        .check-name {
            font-weight: 600;
            color: #333;
        }
        
        .check-message {
            font-size: 13px;
            color: #666;
            margin-top: 3px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-badge.success {
            background: #86efac;
            color: #166534;
        }
        
        .status-badge.failed {
            background: #fca5a5;
            color: #7f1d1d;
        }
        
        .actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏥 Health Check - Ecobyte IA</h1>
        
        <div class="summary">
            <div class="summary-item <?= $passed > 0 ? 'passed' : 'failed' ?>">
                <div class="summary-item-value"><?= $passed ?></div>
                <div class="summary-item-label">Vérifications réussies</div>
            </div>
            <div class="summary-item <?= $failed > 0 ? 'failed' : 'passed' ?>">
                <div class="summary-item-value"><?= $failed ?></div>
                <div class="summary-item-label">Vérifications échouées</div>
            </div>
            <div class="summary-item">
                <div class="summary-item-value"><?= count($checks) ?></div>
                <div class="summary-item-label">Vérifications totales</div>
            </div>
            <div class="summary-item <?= $failed === 0 ? 'passed' : 'failed' ?>">
                <div class="summary-item-value"><?= (int)(($passed / count($checks)) * 100) ?>%</div>
                <div class="summary-item-label">Taux de réussite</div>
            </div>
        </div>
        
        <div class="checks">
            <?php foreach ($checks as $check) { ?>
                <div class="check-item <?= $check['passed'] ? 'success' : 'failed' ?>">
                    <div class="check-icon"><?= $check['passed'] ? '✅' : '❌' ?></div>
                    <div class="check-content">
                        <div class="check-name"><?= htmlspecialchars($check['name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="check-message"><?= htmlspecialchars($check['message'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="status-badge <?= $check['passed'] ? 'success' : 'failed' ?>">
                        <?= $check['passed'] ? '✓ OK' : '✗ ERREUR' ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        
        <div class="actions">
            <a href="ai_summary_test.php" class="btn btn-primary">🧪 Tester la génération</a>
            <a href="admin/summaries.php" class="btn btn-primary">⚙️ Gérer les résumés</a>
            <a href="blog.php" class="btn btn-secondary">📰 Voir le blog</a>
            <button class="btn btn-secondary" onclick="location.reload()">🔄 Rafraîchir</button>
        </div>
    </div>
</body>
</html>
