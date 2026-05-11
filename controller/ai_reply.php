<?php

/**
 * Génère une réponse IA simplifiée basée sur le contenu du post et la question.
 */
function generateAiReplyText(string $postContent, string $question): string
{
    $questionLower = mb_strtolower($question, 'UTF-8');
    $response = 'D’après la recette, ';    

    if (strpos($questionLower, 'végétarien') !== false || strpos($questionLower, 'vegetarien') !== false) {
        $response .= 'cette préparation semble adaptée à un régime végétarien si les ingrédients ne contiennent pas de viande ou de poisson. Si la recette contient du bouillon animal ou du poisson, remplacez-les par des alternatives végétales.';
    } elseif (strpos($questionLower, 'sans sel') !== false || strpos($questionLower, 'régime sans sel') !== false || strpos($questionLower, 'pas de sel') !== false) {
        $response .= 'cette recette peut être rendue plus compatible avec un régime sans sel en limitant les ingrédients transformés et en remplaçant le sel par des herbes fraîches et des épices douces.';
    } elseif (strpos($questionLower, 'calories') !== false || strpos($questionLower, 'énergie') !== false) {
        $response .= 'l’apport calorique dépend des proportions et des ingrédients. Pour alléger la recette, réduisez les matières grasses et augmentez les légumes frais.';
    } elseif (strpos($questionLower, 'allergie') !== false || strpos($questionLower, 'intolérance') !== false) {
        $response .= 'vérifiez bien les allergènes comme les produits laitiers, les noix ou le gluten. Adaptez les ingrédients à votre profil d’allergie en remplaçant les ingrédients problématiques.';
    } else {
        $response .= 'la recette paraît raisonnable, mais adaptez les quantités et les ingrédients selon votre objectif nutritionnel.';
    }

    return $response . ' (Réponse générée automatiquement par l’IA Ecobyte.)';
}
