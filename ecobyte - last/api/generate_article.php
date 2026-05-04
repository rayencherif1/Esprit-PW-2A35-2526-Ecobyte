<?php

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
        exit;
    }

    if (!isset($_FILES['image'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Aucun fichier image fourni.']);
        exit;
    }

    $file = $_FILES['image'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (limite php.ini)',
            UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (limite formulaire)',
            UPLOAD_ERR_PARTIAL => 'Upload interrompu',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier',
            UPLOAD_ERR_NO_TMP_DIR => 'Répertoire temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture',
        ];
        echo json_encode(['success' => false, 'error' => $errors[$file['error']] ?? 'Erreur d\'upload.']);
        exit;
    }

    $tmpPath = $file['tmp_name'];
    $fileName = $file['name'];
    $fileSize = $file['size'];

    $analysis = analyzeImage($tmpPath, $fileName, $fileSize);
    if (!$analysis['success']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Analyse de l\'image impossible.', 'analysis' => $analysis]);
        exit;
    }

    $article = generateArticleFromAnalysis($analysis);
    echo json_encode(array_merge(['success' => true], $analysis, $article));
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}

function analyzeImage(string $tmpPath, string $fileName, int $fileSize): array
{
    $result = [
        'success' => false,
        'file_name' => $fileName,
        'file_size' => $fileSize,
        'file_size_mb' => round($fileSize / (1024 * 1024), 2),
        'validation' => [],
        'metadata' => [],
        'quality_issues' => [],
        'is_safe' => false,
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
    if (!in_array($mimeType, $allowedMimes, true)) {
        $result['validation'][] = "❌ Type MIME invalide : $mimeType";
        return $result;
    }
    $result['validation'][] = "✅ Type MIME valide : $mimeType";
    $result['metadata']['mime_type'] = $mimeType;

    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
        $result['validation'][] = '❌ Fichier corrompu ou image invalide.';
        return $result;
    }
    $result['validation'][] = '✅ Image valide.';

    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $bits = $imageInfo['bits'] ?? 8;
    $channels = $imageInfo['channels'] ?? 3;

    $result['metadata']['width'] = $width;
    $result['metadata']['height'] = $height;
    $result['metadata']['bits'] = $bits;
    $result['metadata']['channels'] = $channels;
    $result['metadata']['ratio'] = round($width / max($height, 1), 2);
    $result['metadata']['pixel_count'] = $width * $height;

    $issues = [];
    if ($width < 100 || $height < 100) {
        $issues[] = "⚠️ Image très petite ({$width}x{$height})";
    }
    if ($width > 10000 || $height > 10000) {
        $issues[] = "⚠️ Image très grande ({$width}x{$height})";
    }
    if ($result['metadata']['pixel_count'] > 50000000) {
        $issues[] = '⚠️ Image haute résolution';
    }
    $maxSizeMB = 10;
    if ($fileSize > $maxSizeMB * 1024 * 1024) {
        $issues[] = '⚠️ Fichier trop gros (' . round($fileSize / (1024 * 1024), 2) . ' MB)';
    }
    $result['quality_issues'] = $issues;

    $colorAnalysis = analyzeImageColors($tmpPath, $mimeType);
    $result['metadata']['dominant_colors'] = $colorAnalysis['dominant_colors'];
    $result['metadata']['brightness'] = $colorAnalysis['brightness'];
    $result['metadata']['saturation'] = $colorAnalysis['saturation'];

    $safetyChecks = [];
    $isSafe = true;
    if ($width < 200 && $height < 200) {
        $safetyChecks[] = '⚠️ Dimensions suspectes (possible QR/barcode)';
        $isSafe = false;
    }
    if ($colorAnalysis['brightness'] < 10) {
        $safetyChecks[] = '⚠️ Image trop sombre';
    }
    if ($colorAnalysis['brightness'] > 240) {
        $safetyChecks[] = '⚠️ Image très claire';
    }

    $result['safety_checks'] = $safetyChecks;
    $result['is_safe'] = $isSafe;
    $result['success'] = true;

    return $result;
}

function analyzeImageColors(string $tmpPath, string $mimeType): array
{
    $result = [
        'dominant_colors' => [],
        'brightness' => 0,
        'saturation' => 0,
    ];

    // Si l'extension GD n'est pas active, on ignore l'analyse des couleurs
    // pour éviter une erreur fatale et garder la génération d'article fonctionnelle.
    if (
        !function_exists('imagecreatefromjpeg') ||
        !function_exists('imagecreatefrompng') ||
        !function_exists('imagecreatefromgif') ||
        !function_exists('imagecolorat') ||
        !function_exists('imagesx') ||
        !function_exists('imagesy') ||
        !function_exists('imagedestroy')
    ) {
        return $result;
    }

    $image = null;
    if ($mimeType === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
        $image = @imagecreatefromjpeg($tmpPath);
    } elseif ($mimeType === 'image/png' && function_exists('imagecreatefrompng')) {
        $image = @imagecreatefrompng($tmpPath);
    } elseif ($mimeType === 'image/gif' && function_exists('imagecreatefromgif')) {
        $image = @imagecreatefromgif($tmpPath);
    } elseif ($mimeType === 'image/webp' && function_exists('imagecreatefromwebp')) {
        $image = @imagecreatefromwebp($tmpPath);
    } elseif ($mimeType === 'image/bmp' && function_exists('imagecreatefromwbmp')) {
        $image = @imagecreatefromwbmp($tmpPath);
    }

    if (!$image && function_exists('imagecreatefromstring')) {
        $imageData = @file_get_contents($tmpPath);
        if ($imageData !== false) {
            $image = @imagecreatefromstring($imageData);
        }
    }

    if (!$image) {
        return $result;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $sampleSize = 10;
    $colors = [];
    $brightnessSum = 0;
    $saturationSum = 0;
    $sampleCount = 0;

    for ($y = 0; $y < $height; $y += $sampleSize) {
        for ($x = 0; $x < $width; $x += $sampleSize) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $brightness = (0.299 * $r + 0.587 * $g + 0.114 * $b);
            $brightnessSum += $brightness;
            $saturationSum += max($r, $g, $b) - min($r, $g, $b);
            $sampleCount++;
            $colorKey = sprintf('#%02x%02x%02x', $r, $g, $b);
            $colors[$colorKey] = ($colors[$colorKey] ?? 0) + 1;
        }
    }

    if ($sampleCount > 0) {
        $result['brightness'] = round($brightnessSum / $sampleCount, 2);
        $result['saturation'] = round($saturationSum / $sampleCount, 2);

        arsort($colors);
        $result['dominant_colors'] = array_slice(array_keys($colors), 0, 3);
    }

    imagedestroy($image);
    return $result;
}

function generateArticleFromAnalysis(array $analysis): array
{
    $foodContext = detectFoodContext($analysis);
    $profile = detectArticleProfile($analysis, $foodContext);
    if ($profile !== null) {
        return [
            'title' => $profile['title'],
            'category' => $profile['category'],
            'content' => $profile['content'],
            'article_hint' => 'Article généré automatiquement selon le thème détecté dans l\'image.',
        ];
    }
    if ($foodContext !== null) {
        return buildFoodArticle($foodContext, $analysis);
    }

    $subject = guessImageSubject($analysis, $foodContext);
    $title = "Découverte : " . ucfirst($subject);
    if (!$analysis['is_safe']) {
        $title = "Analyse d'image : " . ucfirst($subject);
    }

    $summary = "Cette image met en valeur " . $subject . ".";
    $details = [];
    if (!empty($analysis['metadata']['width']) && !empty($analysis['metadata']['height'])) {
        $details[] = "Dimensions : {$analysis['metadata']['width']}x{$analysis['metadata']['height']} pixels.";
    }
    $details[] = "Taille du fichier : {$analysis['file_size_mb']} Mo.";
    if (!empty($analysis['metadata']['dominant_colors'])) {
        $details[] = 'Couleurs dominantes : ' . implode(', ', $analysis['metadata']['dominant_colors']) . '.';
    }
    if (!empty($analysis['quality_issues'])) {
        $details[] = 'Points à améliorer : ' . implode(' ', $analysis['quality_issues']);
    }

    $content = $summary . ' ' . implode(' ', $details) . ' ';
    $content .= 'Cet article propose une lecture rapide et utile du visuel, pour inspirer une publication riche et adaptée à votre audience.';

    $category = $foodContext['category'] ?? 'Santé & Nutrition';

    return [
        'title' => $title,
        'category' => $category,
        'content' => $content,
        'article_hint' => 'Cet article a été généré automatiquement à partir de l\'image téléchargée.',
    ];
}

function buildFoodArticle(array $foodContext, array $analysis): array
{
    $name = $foodContext['name'];
    $category = $foodContext['category'];
    $title = 'Pourquoi ' . $name . ' mérite sa place dans votre assiette';

    $content = "L'image met en avant {$name}, un aliment intéressant à intégrer dans une alimentation équilibrée. ";
    $content .= "Consommé de façon régulière, {$name} peut apporter de l'énergie et des nutriments utiles au quotidien. ";
    $content .= "Selon vos objectifs (forme, bien-être, récupération), cet aliment peut être adapté en collation, en repas principal ou en accompagnement. ";
    if (!empty($analysis['file_size_mb'])) {
        $content .= "Le visuel analysé ({$analysis['file_size_mb']} Mo) confirme un sujet culinaire clair, idéal pour un contenu orienté conseils pratiques. ";
    }
    $content .= "Pour en tirer le meilleur, privilégiez des préparations simples, peu transformées et associées à des ingrédients frais.";

    return [
        'title' => $title,
        'category' => $category,
        'content' => $content,
        'article_hint' => "Article généré automatiquement autour de l'aliment détecté ({$name}).",
    ];
}

function detectArticleProfile(array $analysis, ?array $foodContext = null): ?array
{
    $fileName = strtolower((string) ($analysis['file_name'] ?? ''));
    $foodKey = $foodContext['key'] ?? '';
    $profiles = [
        [
            'keywords' => ['banana', 'banane'],
            'food_keys' => ['banana'],
            'title' => 'Les bienfaits cachés de la banane au quotidien',
            'category' => 'Santé & Nutrition',
            'content' => "La banane est l'un des fruits les plus consommés au monde grâce à sa richesse en nutriments essentiels. Elle contient du potassium, qui aide à réguler la pression artérielle, ainsi que des fibres favorisant une bonne digestion. Facile à transporter et à consommer, elle constitue une excellente collation pour les sportifs et les étudiants. De plus, son apport en glucides naturels en fait une source rapide d'énergie. Intégrer la banane dans son alimentation quotidienne peut donc contribuer à améliorer le bien-être général.",
        ],
        [
            'keywords' => ['apple', 'pomme'],
            'food_keys' => ['apple'],
            'title' => 'Pourquoi la pomme mérite sa place chaque jour',
            'category' => 'Santé & Nutrition',
            'content' => "La pomme est un fruit simple, accessible et très bénéfique pour la santé. Riche en fibres, elle participe à la sensation de satiété et au bon fonctionnement digestif. Elle contient également des antioxydants qui aident à protéger les cellules contre le stress oxydatif. Facile à intégrer au petit-déjeuner, en collation ou en dessert, la pomme favorise une alimentation équilibrée. En consommer régulièrement peut soutenir un mode de vie plus sain.",
        ],
    ];

    foreach ($profiles as $profile) {
        if ($foodKey !== '' && in_array($foodKey, $profile['food_keys'], true)) {
            return [
                'title' => $profile['title'],
                'category' => $profile['category'],
                'content' => $profile['content'],
            ];
        }
        foreach ($profile['keywords'] as $keyword) {
            if (strpos($fileName, $keyword) !== false) {
                return [
                    'title' => $profile['title'],
                    'category' => $profile['category'],
                    'content' => $profile['content'],
                ];
            }
        }
    }

    return null;
}

function detectFoodContext(array $analysis): ?array
{
    $fileName = strtolower((string) ($analysis['file_name'] ?? ''));
    $foods = [
        'banana' => ['name' => 'banane', 'category' => 'Santé & Nutrition', 'keywords' => ['banana', 'banane']],
        'apple' => ['name' => 'pomme', 'category' => 'Santé & Nutrition', 'keywords' => ['apple', 'pomme']],
        'orange' => ['name' => 'orange', 'category' => 'Santé & Nutrition', 'keywords' => ['orange']],
        'carrot' => ['name' => 'carotte', 'category' => 'Santé & Nutrition', 'keywords' => ['carrot', 'carotte']],
        'broccoli' => ['name' => 'brocoli', 'category' => 'Santé & Nutrition', 'keywords' => ['broccoli', 'brocoli']],
        'spinach' => ['name' => 'epinard', 'category' => 'Santé & Nutrition', 'keywords' => ['spinach', 'epinard', 'épinard']],
        'chicken' => ['name' => 'poulet', 'category' => 'Cuisine & Recettes', 'keywords' => ['chicken', 'poulet']],
        'fish' => ['name' => 'poisson', 'category' => 'Cuisine & Recettes', 'keywords' => ['fish', 'poisson', 'salmon']],
        'egg' => ['name' => 'oeuf', 'category' => 'Cuisine & Recettes', 'keywords' => ['egg', 'oeuf', 'oeufs', 'œuf', 'œufs']],
        'rice' => ['name' => 'riz', 'category' => 'Cuisine & Recettes', 'keywords' => ['rice', 'riz']],
        'pasta' => ['name' => 'pates', 'category' => 'Cuisine & Recettes', 'keywords' => ['pasta', 'pates', 'pâtes']],
        'pizza' => ['name' => 'pizza', 'category' => 'Cuisine & Recettes', 'keywords' => ['pizza']],
        'burger' => ['name' => 'burger', 'category' => 'Cuisine & Recettes', 'keywords' => ['burger', 'hamburger']],
    ];

    foreach ($foods as $key => $entry) {
        foreach ($entry['keywords'] as $keyword) {
            if (strpos($fileName, $keyword) !== false) {
                return ['key' => $key, 'name' => $entry['name'], 'category' => $entry['category']];
            }
        }
    }

    return null;
}

function guessImageSubject(array $analysis, ?array $foodContext = null): string
{
    if ($foodContext !== null && !empty($foodContext['name'])) {
        return 'une ' . $foodContext['name'];
    }

    $fileName = strtolower($analysis['file_name']);
    $keywords = [
        'salade' => 'une salade fraîche',
        'fruit' => 'un fruit sain',
        'pomme' => 'une pomme',
        'avocat' => 'un avocat',
        'smoothie' => 'un smoothie nutritif',
        'repas' => 'un repas équilibré',
        'cuisine' => 'une création culinaire',
        'jus' => 'une boisson detox',
        'vegetal' => 'un plat végétal',
        'pizza' => 'une pizza gourmande',
        'burger' => 'un burger savoureux',
        'poulet' => 'un plat de poulet',
        'salmon' => 'un poisson riche en oméga-3',
        'panier' => 'un panier de produits frais',
        'fleurs' => 'des fleurs colorées',
        'paysage' => 'un paysage apaisant',
        'montagne' => 'une montagne majestueuse',
        'plage' => 'une plage ensoleillée',
        'chat' => 'un chat mignon',
        'chien' => 'un chien adorable',
    ];

    foreach ($keywords as $needle => $label) {
        if (strpos($fileName, $needle) !== false) {
            return $label;
        }
    }

    if (!empty($analysis['metadata']['dominant_colors'])) {
        $colors = implode(', ', $analysis['metadata']['dominant_colors']);
        return 'une image dominée par les couleurs ' . $colors;
    }

    return 'un visuel captivant';
}
