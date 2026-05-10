<?php

/**
 * Système de résumé automatique gratuit
 * Utilise des algorithmes NLP simples mais efficaces
 */

class FreeSummarizer {

    /**
     * Génère un résumé automatique gratuit
     */
    public static function generateSummary(string $text, int $maxLength = 1000): string
    {
        // Nettoyer le texte
        $cleanText = self::cleanText($text);

        if (strlen($cleanText) < 50) {
            return self::createBasicSummary($cleanText);
        }

        // Extraire les phrases clés
        $sentences = self::extractSentences($cleanText);
        $keySentences = self::getKeySentences($sentences, 2);

        // Créer le résumé
        $summary = implode(' ', $keySentences);

        return trim($summary);
    }

    /**
     * Nettoie le texte des balises HTML et normalise
     */
    private static function cleanText(string $text): string
    {
        // Supprimer les balises HTML
        $text = strip_tags($text);

        // Supprimer les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);

        // Supprimer les caractères spéciaux inutiles
        $text = preg_replace('/[^\w\s.,!?:;()-]/u', '', $text);

        return trim($text);
    }

    /**
     * Crée un résumé basique pour les textes courts
     */
    private static function createBasicSummary(string $text): string
    {
        $words = explode(' ', $text);
        $wordCount = count($words);

        if ($wordCount <= 10) {
            return $text;
        }

        // Prendre les premiers mots
        $summaryWords = array_slice($words, 0, min(15, $wordCount));
        return implode(' ', $summaryWords) . '...';
    }

    /**
     * Extrait les phrases du texte
     */
    private static function extractSentences(string $text): array
    {
        // Diviser par les signes de ponctuation
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Nettoyer et filtrer
        $sentences = array_map('trim', $sentences);
        $sentences = array_filter($sentences, function($sentence) {
            return strlen($sentence) > 10; // Phrases trop courtes
        });

        return array_values($sentences);
    }

    /**
     * Identifie les phrases les plus importantes
     */
    private static function getKeySentences(array $sentences, int $count): array
    {
        if (count($sentences) <= $count) {
            return $sentences;
        }

        // Calculer le score de chaque phrase
        $scoredSentences = [];
        foreach ($sentences as $index => $sentence) {
            $score = self::calculateSentenceScore($sentence, $index, count($sentences));
            $scoredSentences[] = [
                'sentence' => $sentence,
                'score' => $score,
                'index' => $index
            ];
        }

        // Trier par score décroissant
        usort($scoredSentences, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Prendre les meilleures phrases
        $keySentences = array_slice($scoredSentences, 0, $count);

        // Remettre dans l'ordre original
        usort($keySentences, function($a, $b) {
            return $a['index'] <=> $b['index'];
        });

        return array_column($keySentences, 'sentence');
    }

    /**
     * Calcule le score d'importance d'une phrase
     */
    private static function calculateSentenceScore(string $sentence, int $position, int $totalSentences): float
    {
        $score = 0;

        // Mots-clés liés à la nutrition et écologie
        $keywords = [
            'nutrition', 'écologie', 'environnement', 'santé', 'alimentation',
            'durable', 'bio', 'organique', 'saison', 'local', 'carbone',
            'biodiversité', 'sol', 'eau', 'climat', 'pollution', 'déchet',
            'végétal', 'protéine', 'vitamine', 'minéral', 'antioxydant'
        ];

        $lowerSentence = mb_strtolower($sentence);

        // Score basé sur les mots-clés
        foreach ($keywords as $keyword) {
            if (strpos($lowerSentence, $keyword) !== false) {
                $score += 2;
            }
        }

        // Score basé sur la position (début et fin souvent plus importants)
        if ($position === 0) $score += 1; // Première phrase
        if ($position === $totalSentences - 1) $score += 1; // Dernière phrase

        // Score basé sur la longueur (phrases ni trop courtes ni trop longues)
        $wordCount = str_word_count($sentence);
        if ($wordCount >= 5 && $wordCount <= 25) {
            $score += 1;
        }

        // Score basé sur la présence de nombres ou pourcentages
        if (preg_match('/\d/', $sentence)) {
            $score += 0.5;
        }

        return $score;
    }
}

/**
 * Fonction principale pour générer un résumé gratuit
 */
function generateFreeSummary(string $postContent): string
{
    try {
        return FreeSummarizer::generateSummary($postContent);
    } catch (Exception $e) {
        // Fallback simple
        $cleanContent = strip_tags($postContent);
        return substr($cleanContent, 0, 200) . '...';
    }
}

?>