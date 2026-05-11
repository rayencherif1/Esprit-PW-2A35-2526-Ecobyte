<?php

/**
 * API de reconnaissance d'images
 * Analyse: format, contenu, métadonnées, dimensions
 * 
 * Paramètres:
 * - POST file: l'image à analyser
 * - GET method: 'analyze' ou 'validate'
 */

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée.']);
        exit;
    }

    // Vérifier si un fichier est uploadé
    if (!isset($_FILES['image'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Aucun fichier uploadé.']);
        exit;
    }

    $file = $_FILES['image'];

    // Vérifier les erreurs d'upload
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
        echo json_encode(['error' => $errors[$file['error']] ?? 'Erreur inconnue']);
        exit;
    }

    $tmpPath = $file['tmp_name'];
    $fileName = $file['name'];
    $fileSize = $file['size'];

    // Valider le fichier
    $analysis = analyzeImage($tmpPath, $fileName, $fileSize);

    http_response_code(200);
    echo json_encode($analysis);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}

/**
 * Analyse complète d'une image
 */
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

    // 1. Vérifier le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
    if (!in_array($mimeType, $allowedMimes)) {
        $result['validation'][] = "❌ Type MIME invalide: $mimeType";
        return $result;
    }
    $result['validation'][] = "✅ Type MIME valide: $mimeType";
    $result['metadata']['mime_type'] = $mimeType;

    // 2. Vérifier que c'est bien une image
    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
        $result['validation'][] = "❌ Fichier corrompu ou pas une image valide";
        return $result;
    }
    $result['validation'][] = "✅ Image valide";

    // 3. Extraire les métadonnées
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

    // 4. Vérifier la qualité de l'image
    $issues = [];

    // Taille minimum
    if ($width < 100 || $height < 100) {
        $issues[] = "⚠️ Image très petite (" . $width . "x" . $height . ")";
    }

    // Taille maximum
    if ($width > 10000 || $height > 10000) {
        $issues[] = "⚠️ Image très grande (" . $width . "x" . $height . ")";
    }

    // Résolution
    if ($result['metadata']['pixel_count'] > 50000000) {
        $issues[] = "⚠️ Image trop haute résolution";
    }

    // Taille du fichier
    $maxSizeMB = 10;
    if ($fileSize > $maxSizeMB * 1024 * 1024) {
        $issues[] = "⚠️ Fichier trop gros (" . round($fileSize / (1024 * 1024), 2) . " MB > " . $maxSizeMB . " MB)";
    }

    $result['quality_issues'] = $issues;

    // 5. Analyse basique du contenu (histogramme, couleurs dominantes)
    $colorAnalysis = analyzeImageColors($tmpPath, $mimeType);
    $result['metadata']['dominant_colors'] = $colorAnalysis['dominant_colors'];
    $result['metadata']['brightness'] = $colorAnalysis['brightness'];
    $result['metadata']['saturation'] = $colorAnalysis['saturation'];

    // 6. Détection de contenu inapproprié (basique)
    $isSafe = true;
    $safetyChecks = [];

    // Vérifier les dimensions suspects (trop petit = possible QR code/barcode)
    if ($width < 200 && $height < 200) {
        $safetyChecks[] = "⚠️ Dimensions anormales (QR/code-barres?)";
        $isSafe = false;
    }

    // Vérifier si l'image est presque entièrement noire (possible erreur)
    if ($colorAnalysis['brightness'] < 10) {
        $safetyChecks[] = "⚠️ Image trop sombre (presque noire)";
    }

    // Vérifier si l'image est presque entièrement blanche (possible erreur)
    if ($colorAnalysis['brightness'] > 240) {
        $safetyChecks[] = "⚠️ Image trop claire (presque blanche)";
    }

    $result['safety_checks'] = $safetyChecks;
    $result['is_safe'] = $isSafe;
    $result['success'] = true;

    return $result;
}

/**
 * Analyse les couleurs dominantes d'une image
 */
function analyzeImageColors(string $tmpPath, string $mimeType): array
{
    $result = [
        'dominant_colors' => [],
        'brightness' => 0,
        'saturation' => 0,
    ];

    try {
        $image = null;

        // Charger l'image selon le format
        if ($mimeType === 'image/jpeg') {
            $image = @imagecreatefromjpeg($tmpPath);
        } elseif ($mimeType === 'image/png') {
            $image = @imagecreatefrompng($tmpPath);
        } elseif ($mimeType === 'image/gif') {
            $image = @imagecreatefromgif($tmpPath);
        } elseif ($mimeType === 'image/webp') {
            $image = @imagecreatefromwebp($tmpPath);
        }

        if (!$image) {
            return $result;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Réduire l'image pour l'analyse (performance)
        $sampleSize = 10;
        $colors = [];
        $brightnessSum = 0;
        $sampleCount = 0;

        for ($y = 0; $y < $height; $y += $sampleSize) {
            for ($x = 0; $x < $width; $x += $sampleSize) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $brightness = (0.299 * $r + 0.587 * $g + 0.114 * $b);
                $brightnessSum += $brightness;
                $sampleCount++;

                $colorKey = sprintf("#%02x%02x%02x", $r, $g, $b);
                $colors[$colorKey] = ($colors[$colorKey] ?? 0) + 1;
            }
        }

        // Top 5 couleurs
        arsort($colors);
        $result['dominant_colors'] = array_slice(array_keys($colors), 0, 5);

        // Luminosité moyenne
        $result['brightness'] = (int) ($brightnessSum / max($sampleCount, 1));

        // Saturation (simple)
        $result['saturation'] = calculateSaturation($result['dominant_colors']);

        imagedestroy($image);

    } catch (Exception $e) {
        // Ignorer les erreurs d'analyse couleur
    }

    return $result;
}

/**
 * Calcule la saturation basée sur les couleurs
 */
function calculateSaturation(array $colors): int
{
    if (empty($colors)) {
        return 0;
    }

    $totalVariance = 0;

    foreach ($colors as $hex) {
        $r = hexdec(substr($hex, 1, 2));
        $g = hexdec(substr($hex, 3, 2));
        $b = hexdec(substr($hex, 5, 2));

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        if ($max === 0) {
            $saturation = 0;
        } else {
            $saturation = ($delta / $max) * 100;
        }

        $totalVariance += $saturation;
    }

    return (int) ($totalVariance / count($colors));
}
