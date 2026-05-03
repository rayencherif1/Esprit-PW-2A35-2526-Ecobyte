<?php

/**
 * Fonctions utilitaires pour valider et analyser les images
 */

/**
 * Valide une image uploadée
 */
function validateUploadedImage(string $tmpPath): array
{
    $result = [
        'valid' => false,
        'message' => '',
        'issues' => [],
    ];

    // Vérifier que c'est bien une image
    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
        $result['message'] = 'Fichier corrompu ou pas une image valide.';
        return $result;
    }

    // Vérifier le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
    if (!in_array($mimeType, $allowedMimes)) {
        $result['message'] = 'Type MIME non supporté: ' . $mimeType;
        return $result;
    }

    // Vérifier les dimensions
    $width = $imageInfo[0];
    $height = $imageInfo[1];

    if ($width < 50 || $height < 50) {
        $result['issues'][] = 'Image trop petite';
    }

    if ($width > 5000 || $height > 5000) {
        $result['issues'][] = 'Image trop grande';
    }

    // Vérifier la taille du fichier
    $fileSize = filesize($tmpPath);
    if ($fileSize > 10 * 1024 * 1024) {
        $result['issues'][] = 'Fichier trop volumineux (> 10 MB)';
    }

    if ($fileSize < 1000) {
        $result['issues'][] = 'Fichier trop petit (< 1 KB)';
    }

    $result['valid'] = true;
    $result['message'] = 'Image valide';

    return $result;
}
