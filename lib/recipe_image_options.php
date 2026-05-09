<?php

declare(strict_types=1);

/**
 * Images recettes disponibles dans public/image (chemins web, slash /).
 * Ajouter ici tout nouveau fichier .jpg pour qu'il apparaisse dans les listes.
 */
function recipe_image_options_list(): array
{
    $b = '/recette/public/image/';
    return [
        ['path' => $b . 'avocat.jpg', 'label' => 'Avocat'],
        ['path' => $b . 'citron.jpg', 'label' => 'Citron'],
        ['path' => $b . 'creme.jpg', 'label' => 'Crème'],
        ['path' => $b . 'curry.jpg', 'label' => 'Curry'],
        ['path' => $b . 'fruitrouge.jpg', 'label' => 'Fruits rouges'],
        ['path' => $b . 'legume.jpg', 'label' => 'Légumes'],
        ['path' => $b . 'mangue.jpg', 'label' => 'Mangue'],
        ['path' => $b . 'orange.jpg', 'label' => 'Orange'],
        ['path' => $b . 'pain.jpg', 'label' => 'Pain'],
        ['path' => $b . 'pancake.jpg', 'label' => 'Pancakes'],
        ['path' => $b . 'pasta.jpg', 'label' => 'Pasta'],
        ['path' => $b . 'pates.jpg', 'label' => 'Pâtes'],
        ['path' => $b . 'pizza.jpg', 'label' => 'Pizza'],
        ['path' => $b . 'riz.jpg', 'label' => 'Riz'],
        ['path' => $b . 'salade.jpg', 'label' => 'Salade'],
        ['path' => $b . 'smoothie.jpg', 'label' => 'Smoothie'],
        ['path' => $b . 'soupe.jpg', 'label' => 'Soupe'],
        ['path' => $b . 'tarte.jpg', 'label' => 'Tarte'],
        ['path' => $b . 'tomate.jpg', 'label' => 'Tomate'],
    ];
}

/**
 * Liste triée par libellé ; si la valeur en base n'est pas référencée, une entrée « Fichier actuel » est ajoutée.
 *
 * @return list<array{path: string, label: string}>
 */
function recipe_image_options_for_select(string $selectedPath): array
{
    $list = recipe_image_options_list();
    $known = array_fill_keys(array_column($list, 'path'), true);
    $selectedPath = trim($selectedPath);
    if ($selectedPath !== '' && !isset($known[$selectedPath])) {
        $list[] = ['path' => $selectedPath, 'label' => 'Fichier actuel'];
    }
    usort($list, static function (array $a, array $b): int {
        return strcasecmp($a['label'], $b['label']);
    });
    return $list;
}
