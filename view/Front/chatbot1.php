<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/allergie.Controller.php';
require_once __DIR__ . '/../../Controller/traitement.Controller.php';

// Initialisation des contrôleurs
$controller = new AllergieC();
$allergies = $controller->listAllergie();
$traitementController = new TraitementC();

if (!is_array($allergies)) {
    $allergies = [];
}

// Traitement de la requête AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = mb_strtolower(trim($input['message'] ?? ''), 'UTF-8');
    
    // ========== FONCTIONS ==========
    
    function extractAllergyName($message, $allergies) {
        foreach ($allergies as $allergie) {
            $nom = mb_strtolower($allergie['nom'], 'UTF-8');
            if (strpos($message, $nom) !== false) {
                return $allergie;
            }
        }
        return null;
    }
    
    function getTreatments($allergie, $traitementController) {
        if (!$allergie) return null;
        $traitements = $traitementController->listTraitementByAllergie($allergie['id_allergie']);
        return ['allergie' => $allergie, 'traitements' => $traitements];
    }
    
    function getSymptoms($allergie) {
        if (!$allergie) return null;
        return ['nom' => $allergie['nom'], 'symptomes' => $allergie['symptomes'] ?? 'Aucun symptôme'];
    }
    
    function getSeverity($allergie) {
        if (!$allergie) return null;
        $gravite = $allergie['gravite'] ?? 'non définie';
        $icons = ['grave' => '🔴', 'moyenne' => '🟠', 'faible' => '🟢'];
        return ['nom' => $allergie['nom'], 'gravite' => $gravite, 'icon' => $icons[$gravite] ?? '⚪'];
    }
    
    function listAllergiesSimple($allergies) {
        $names = [];
        foreach ($allergies as $a) {
            $names[] = "• " . $a['nom'];
        }
        return implode("\n", $names);
    }
    
    function listAllergiesDetail($allergies) {
        $response = "";
        foreach ($allergies as $a) {
            $icon = match(strtolower($a['gravite'] ?? '')) { 'grave' => '🔴', 'moyenne' => '🟠', 'faible' => '🟢', default => '⚪' };
            $response .= "$icon **" . $a['nom'] . "**\n";
            $response .= "   📝 " . substr($a['description'] ?? '', 0, 80) . "...\n\n";
        }
        return $response;
    }
    
    // ========== RECETTES ==========
    function getRecettes($allergie) {
        if (!$allergie) return null;
        
        $nom = mb_strtolower($allergie['nom'], 'UTF-8');
        
        if (strpos($nom, 'gluten') !== false) {
            return "🍳 RECETTES SANS GLUTEN\n\n" .
                   "🥞 PETIT-DÉJEUNER :\n" .
                   "• Pancakes à la farine de riz et sirop d'érable\n" .
                   "• Porridge de quinoa aux fruits rouges\n" .
                   "• Omelette aux légumes\n\n" .
                   "🍝 DÉJEUNER :\n" .
                   "• Pâtes de riz au pesto maison\n" .
                   "• Curry de pois chiches au lait de coco\n" .
                   "• Salade de quinoa, avocat, feta\n\n" .
                   "🍲 DÎNER :\n" .
                   "• Tarte à la farine de sarrasin (légumes/chèvre)\n" .
                   "• Poisson en papillote aux herbes\n" .
                   "• Risotto aux champignons\n\n" .
                   "🍰 DESSERT :\n" .
                   "• Fondant au chocolat (farine de châtaigne)\n" .
                   "• Mousse au chocolat sans farine\n" .
                   "• Crumble aux pommes (flocons de sarrasin)";
        }
        
        if (strpos($nom, 'lactose') !== false) {
            return "🍳 RECETTES SANS LACTOSE\n\n" .
                   "🥞 PETIT-DÉJEUNER :\n" .
                   "• Porridge au lait d'amande et fruits\n" .
                   "• Smoothie bowl (lait de coco, fruits rouges)\n" .
                   "• Pain grillé avec purée d'amande\n\n" .
                   "🍝 DÉJEUNER :\n" .
                   "• Pâtes à la crème de cajou\n" .
                   "• Curry végétarien au lait de coco\n" .
                   "• Salade niçoise (sans fromage)\n\n" .
                   "🍲 DÎNER :\n" .
                   "• Purée maison (lait d'avoine, margarine)\n" .
                   "• Quinoa aux légumes rôtis\n" .
                   "• Riz sauté crevettes/légumes\n\n" .
                   "🍰 DESSERT :\n" .
                   "• Mousse au chocolat (lait de coco)\n" .
                   "• Sorbet fruits frais\n" .
                   "• Far breton au lait d'amande";
        }
        
        if (strpos($nom, 'arachides') !== false) {
            return "🍳 RECETTES SANS ARACHIDES\n\n" .
                   "🥞 PETIT-DÉJEUNER :\n" .
                   "• Porridge aux amandes effilées et miel\n" .
                   "• Smoothie banane, lait d'amande, graines de chia\n" .
                   "• Tartines beurre de noix de cajou\n\n" .
                   "🍝 DÉJEUNER :\n" .
                   "• Nouilles sautées (sauce tamari, sans saté)\n" .
                   "• Curry au lait de coco (sans pâte d'arachide)\n" .
                   "• Wok de légumes graines de sésame\n\n" .
                   "🍲 DÎNER :\n" .
                   "• Poulet rôti légumes rôtis\n" .
                   "• Saumon en croûte de graines\n" .
                   "• Dahl de lentilles coriandre\n\n" .
                   "🍰 DESSERT :\n" .
                   "• Cookies aux pépites de chocolat\n" .
                   "• Gâteau aux noisettes\n" .
                   "• Clafoutis aux cerises";
        }
        
        if (strpos($nom, 'pollen') !== false) {
            return "🌸 RECETTES ANTI-ALLERGIE SAISONNIÈRE\n\n" .
                   "🥗 ALIMENTS À PRIVILÉGIER :\n" .
                   "• Soupe de légumes au curcuma et gingembre\n" .
                   "• Saumon grillé, quinoa, brocoli vapeur\n" .
                   "• Poulet au citron, riz basmati, courgettes\n" .
                   "• Smoothie vert (épinards, pomme, gingembre)\n\n" .
                   "🌿 PLANTES RECOMMANDÉES :\n" .
                   "• Tisane de camomille\n" .
                   "• Infusion de thym\n" .
                   "• Jus de gingembre frais";
        }
        
        return null;
    }
    
    // ========== CONSEILS ALIMENTAIRES ==========
    function getDietaryAdvice($allergie) {
        if (!$allergie) return null;
        
        $nom = mb_strtolower($allergie['nom'], 'UTF-8');
        
        if (strpos($nom, 'gluten') !== false) {
            return "🍞 ALIMENTATION SANS GLUTEN\n\n" .
                   "❌ ALIMENTS À ÉVITER :\n" .
                   "• Blé, orge, seigle, avoine non certifiée\n" .
                   "• Pains, pâtes, pizzas, biscuits, viennoiseries\n" .
                   "• Bière, whisky, sauces (soja, teriyaki)\n\n" .
                   "✅ ALIMENTS AUTORISÉS :\n" .
                   "• Riz, maïs, quinoa, sarrasin, millet\n" .
                   "• Pommes de terre, patates douces\n" .
                   "• Légumes, fruits, légumineuses\n" .
                   "• Viandes, poissons, œufs nature\n" .
                   "• Farines sans gluten (riz, maïs, châtaigne)";
        }
        
        if (strpos($nom, 'lactose') !== false) {
            return "🥛 ALIMENTATION SANS LACTOSE\n\n" .
                   "❌ ALIMENTS À ÉVITER :\n" .
                   "• Lait, crème, yaourts, fromages frais\n" .
                   "• Glaces, chocolat au lait\n" .
                   "• Beurre, plats préparés (purée, soupes)\n\n" .
                   "✅ ALIMENTS AUTORISÉS :\n" .
                   "• Laits végétaux (amande, soja, avoine, riz, coco)\n" .
                   "• Fromages affinés (emmenthal, comté, parmesan)\n" .
                   "• Yaourts sans lactose\n" .
                   "• Beurre clarifié (ghee), margarine\n" .
                   "• Tous fruits, légumes, viandes, poissons";
        }
        
        if (strpos($nom, 'arachides') !== false) {
            return "🥜 ALIMENTATION SANS ARACHIDES\n\n" .
                   "❌ ALIMENTS À ÉVITER :\n" .
                   "• Cacahuètes, beurre de cacahuète\n" .
                   "• Pâtisseries, biscuits, barres énergétiques\n" .
                   "• Sauces asiatiques (sauce saté)\n" .
                   "• Chocolats, glaces, nougat, praliné\n\n" .
                   "✅ ALIMENTS AUTORISÉS :\n" .
                   "• Noix de cajou, amandes, noisettes\n" .
                   "• Beurre d'amande, beurre de noix de cajou\n" .
                   "• Graines (tournesol, courge, sésame, chia)\n" .
                   "• Fruits, légumes, viandes, poissons, œufs";
        }
        
        if (strpos($nom, 'pollen') !== false) {
            return "🌸 ALIMENTATION PENDANT LA SAISON DES POLLENS\n\n" .
                   "✅ ALIMENTS RECOMMANDÉS :\n" .
                   "• Fruits et légumes cuits (moins allergisants)\n" .
                   "• Thés verts, tisanes (anti-inflammatoires)\n" .
                   "• Aliments riches en oméga-3 (saumon, noix)\n" .
                   "• Miel local (peut aider l'immunité)\n\n" .
                   "🌿 PLANTES RECOMMANDÉES :\n" .
                   "• Tisane de camomille, infusion de thym\n" .
                   "• Jus de gingembre frais";
        }
        
        return null;
    }
    
    // ========== SYMPTÔMES DÉTAILLÉS ==========
    function getDetailedSymptoms($allergie) {
        if (!$allergie) return null;
        
        $nom = mb_strtolower($allergie['nom'], 'UTF-8');
        
        $symptomesData = [
            'arachides' => [
                'description' => "Urticaire, gonflement des lèvres et de la gorge, difficultés respiratoires, démangeaisons, choc anaphylactique possible",
                'exemples' => "• 'Je gonfle après avoir mangé des cacahuètes'\n• 'J'ai des boutons rouges qui grattent après avoir mangé des fruits secs'\n• 'Ma gorge se serre quand je mange des produits contenant des arachides'"
            ],
            'gluten' => [
                'description' => "Troubles digestifs (diarrhée, constipation), ballonnements, fatigue chronique, maux de tête, douleurs articulaires, perte de poids",
                'exemples' => "• 'Je suis fatigué et j'ai mal au ventre quand je mange du pain'\n• 'J'ai la diarrhée après avoir mangé des pâtes'\n• 'Je perds du poids sans raison apparente'"
            ],
            'lactose' => [
                'description' => "Ballonnements, diarrhée, douleurs abdominales, nausées, gaz intestinaux, crampes, bruits intestinaux",
                'exemples' => "• 'J'ai des ballonnements après avoir bu du lait'\n• 'J'ai mal au ventre quand je mange du fromage'\n• 'Je suis nauséeux après avoir mangé une glace'"
            ],
            'pollen de bouleau' => [
                'description' => "Éternuements en salve, nez qui coule abondamment, yeux qui piquent et qui pleurent, gorge irritée, fatigue, toux sèche",
                'exemples' => "• 'Au printemps je éternue beaucoup et j'ai le nez qui coule'\n• 'Mes yeux piquent et pleurent quand je sors'\n• 'Je tousse et j'ai mal à la gorge au printemps'"
            ]
        ];
        
        foreach ($symptomesData as $key => $data) {
            if (strpos($nom, $key) !== false) {
                return "🤧 SYMPTÔMES DE " . strtoupper($allergie['nom']) . "\n\n" .
                       "📋 " . $data['description'] . "\n\n" .
                       "💡 EXEMPLES DE DESCRIPTION :\n" . $data['exemples'] . "\n\n" .
                       "👉 Tapez 'Traitement pour " . $allergie['nom'] . "' pour voir les traitements";
            }
        }
        
        return null;
    }
    
    // ========== ANALYSE DES SYMPTÔMES ==========
    function findBestMatchingAllergy($message, $allergies) {
        $stopWords = ['je', 'tu', 'il', 'elle', 'on', 'nous', 'vous', 'ils', 'elles', 'me', 'te', 'se', 'que', 'qui', 'dont', 'le', 'la', 'les', 'un', 'une', 'des', 'et', 'ou', 'pour', 'avec', 'sans', 'dans', 'par', 'sur', 'sous', 'de', 'du', 'au', 'aux', 'j\'ai', 'je suis'];
        $words = explode(' ', $message);
        $keywords = array_filter($words, function($word) use ($stopWords) {
            $word = trim($word, '.,?!;:()');
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });
        
        if (empty($keywords)) return null;
        
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($allergies as $allergie) {
            $searchText = mb_strtolower(($allergie['symptomes'] ?? '') . ' ' . ($allergie['nom'] ?? ''), 'UTF-8');
            $score = 0;
            
            foreach ($keywords as $kw) {
                if (strpos($searchText, $kw) !== false) {
                    $score += 20;
                }
            }
            
            $nom = mb_strtolower($allergie['nom'], 'UTF-8');
            if ($nom == 'gluten' && (strpos($message, 'pain') !== false || strpos($message, 'blé') !== false)) $score += 30;
            if ($nom == 'lactose' && (strpos($message, 'lait') !== false || strpos($message, 'fromage') !== false)) $score += 30;
            if ($nom == 'arachides' && (strpos($message, 'cacahuète') !== false)) $score += 30;
            if ($nom == 'pollen de bouleau' && (strpos($message, 'printemps') !== false || strpos($message, 'éternue') !== false || strpos($message, 'nez qui coule') !== false)) $score += 30;
            
            if ($score > $bestScore && $score > 0) {
                $bestScore = $score;
                $bestMatch = $allergie;
            }
        }
        
        return $bestMatch ? ['allergie' => $bestMatch, 'score' => min(100, $bestScore)] : null;
    }
    
    // ========== TRAITEMENT PRINCIPAL ==========
    
    $response = "";
    $allergie = extractAllergyName($userMessage, $allergies);
    
    // === RECETTES ===
    if (preg_match('/recette|recettes|plat|cuisiner|repas|menu/i', $userMessage)) {
        $recettes = getRecettes($allergie);
        if ($recettes) {
            $response = $recettes;
        } else {
            $response = "🍳 Pour quelles recettes voulez-vous ?\n\nExemples :\n• 'Recettes pour allergie gluten'\n• 'Recettes pour allergie arachides'\n• 'Recettes pour allergie lactose'\n• 'Recettes pour allergie pollen'";
        }
    }
    
    // === CONSEILS ALIMENTAIRES ===
    elseif (preg_match('/alimentation|que manger|conseil alimentaire|quoi manger|peut manger|aliments autorisés|aliments à éviter|nourriture|sans gluten|sans lactose|sans arachides|sans pollen/i', $userMessage)) {
        $advice = getDietaryAdvice($allergie);
        if ($advice) {
            $response = $advice;
        } else {
            $response = "🍽️ CONSEILS ALIMENTAIRES\n\nPour quelle allergie voulez-vous des conseils ?\n\nExemples :\n• 'Alimentation sans gluten'\n• 'Alimentation sans lactose'\n• 'Alimentation sans arachides'\n• 'Alimentation sans pollen'";
        }
    }
    
    // === SYMPTÔMES DÉTAILLÉS ===
    elseif (preg_match('/symptôme|symptomes|signe|manifestation/i', $userMessage)) {
        $symptomsDetail = getDetailedSymptoms($allergie);
        if ($symptomsDetail) {
            $response = $symptomsDetail;
        } else {
            $response = "🤧 Pour quelle allergie voulez-vous les symptômes ?\n\nExemples :\n• 'Symptômes de gluten'\n• 'Symptômes de lactose'\n• 'Symptômes d\'arachides'\n• 'Symptômes de pollen'";
        }
    }
    
    // === TRAITEMENTS ===
    elseif (preg_match('/traitement|soigner|médicament|medicament|remède|guérir|prendre|epipen|antihistaminique|adrénaline/i', $userMessage)) {
        $data = getTreatments($allergie, $traitementController);
        if (!$data) {
            $response = "❓ Pour quelle allergie voulez-vous les traitements ?\n\nExemples :\n• 'Traitement pour Arachides'\n• 'Traitement pour Gluten'\n\nAllergies disponibles :\n" . listAllergiesSimple($GLOBALS['allergies']);
        } elseif (empty($data['traitements'])) {
            $response = "💊 Aucun traitement répertorié pour " . $data['allergie']['nom'] . ".";
        } else {
            $response = "💊 TRAITEMENTS POUR " . strtoupper($data['allergie']['nom']) . "\n\n";
            foreach ($data['traitements'] as $i => $t) {
                $response .= ($i+1) . ". " . $t['nom_traitement'] . "\n";
                if (!empty($t['conseils'])) $response .= "   → " . $t['conseils'] . "\n";
                if (!empty($t['interdiction'])) $response .= "   → " . $t['interdiction'] . "\n";
                $response .= "\n";
            }
        }
    }
    
    // === GRAVITÉ ===
    elseif (preg_match('/gravité|grave|dangereux|risque|sévérité/i', $userMessage)) {
        $data = getSeverity($allergie);
        if (!$data) {
            $response = "❓ Pour quelle allergie voulez-vous la gravité ?\n\nExemple : 'Gravité de Gluten'";
        } else {
            $msg = match($data['gravite']) {
                'grave' => "URGENCE - Cette allergie peut être mortelle. Ayez toujours votre EpiPen sur vous.",
                'moyenne' => "SURVEILLANCE - Gérable avec régime alimentaire adapté et suivi médical.",
                'faible' => "BÉNIN - Gérable avec des antihistaminiques en période pollinique.",
                default => "Niveau de gravité non défini."
            };
            $response = $data['icon'] . " GRAVITÉ DE " . strtoupper($data['nom']) . " : " . strtoupper($data['gravite']) . "\n\n" . $msg;
        }
    }
    
    // === LISTE DES ALLERGIES ===
    elseif (preg_match('/liste|tous|toutes|quelles allergies|afficher/i', $userMessage)) {
        $response = "📋 LISTE COMPLÈTE DES ALLERGIES (" . count($allergies) . ")\n\n" . listAllergiesDetail($allergies);
    }
    
    // === URGENCE ===
    elseif (preg_match('/urgence|alerte|15|112|pompiers|secours|appeler|hospitalisation/i', $userMessage)) {
        if ($allergie && strtolower($allergie['gravite'] ?? '') == 'grave') {
            $response = "🚨 URGENCE - ALLERGIE GRAVE 🚨\n\n" .
                       "ALERTE : L'allergie aux " . $allergie['nom'] . " est potentiellement mortelle !\n\n" .
                       "ACTION IMMÉDIATE :\n" .
                       "1. Appelez le 15 ou 112\n" .
                       "2. Injectez l'EpiPen (adrénaline) dans la cuisse\n" .
                       "3. Allongez la personne, jambes surélevées\n" .
                       "4. Desserrez les vêtements\n" .
                       "5. Surveillez la respiration\n\n" .
                       "NE LAISSEZ PAS LA PERSONNE SEULE";
        } else {
            $response = "🚨 CONDUITE À TENIR EN CAS D'URGENCE ALLERGIQUE 🚨\n\n" .
                       "SIGNES D'ALERTE (choc anaphylactique) :\n" .
                       "• Difficultés respiratoires\n" .
                       "• Gonflement de la langue/gorge\n" .
                       "• Chute de tension, vertiges, évanouissement\n" .
                       "• Urticaire géante\n\n" .
                       "PROTOCOLE D'URGENCE :\n" .
                       "1. APPELEZ LE 15 ou 112\n" .
                       "2. INJECTEZ L'ADRÉNALINE (EpiPen)\n" .
                       "3. ALLONGEZ la personne\n" .
                       "4. RESTEZ avec elle jusqu'aux secours";
        }
    }
    
    // === ANALYSE DES SYMPTÔMES (DESCRIPTION LIBRE) ===
    elseif (strlen($userMessage) > 15) {
        $match = findBestMatchingAllergy($userMessage, $allergies);
        if ($match) {
            $response = "🔍 ANALYSE DE VOS SYMPTÔMES\n\n";
            $response .= "D'après votre description, vous pourriez avoir une allergie aux " . $match['allergie']['nom'] . ".\n\n";
            $response .= "📊 Correspondance : " . $match['score'] . "%\n\n";
            $response .= "📋 Symptômes typiques : " . substr($match['allergie']['symptomes'] ?? '', 0, 200) . "\n\n";
            $response .= "💡 Que faire ?\n";
            $response .= "• Tapez 'Traitement pour " . $match['allergie']['nom'] . "'\n";
            $response .= "• Tapez 'Alimentation sans " . $match['allergie']['nom'] . "'\n";
            $response .= "• Tapez 'Recettes pour allergie " . $match['allergie']['nom'] . "'";
        } else {
            $response = "🔍 Je n'ai pas identifié d'allergie correspondant.\n\nAllergies disponibles :\n" . listAllergiesSimple($allergies) . "\n\n" .
                       "Exemples de descriptions :\n" .
                       "• 'Je gonfle après avoir mangé des cacahuètes' → Arachides\n" .
                       "• 'J'ai mal au ventre après avoir bu du lait' → Lactose\n" .
                       "• 'Je suis fatigué quand je mange du pain' → Gluten\n" .
                       "• 'Au printemps je éternue beaucoup' → Pollen de bouleau";
        }
    }
    
    // === RÉPONSE PAR DÉFAUT ===
    else {
        $response = "🤔 Je n'ai pas compris. Voici ce que je peux faire :\n\n" .
                   "🍳 RECETTES :\n" .
                   "• 'Recettes pour allergie gluten'\n" .
                   "• 'Recettes pour allergie arachides'\n" .
                   "• 'Recettes pour allergie lactose'\n" .
                   "• 'Recettes pour allergie pollen'\n\n" .
                   "🍽️ CONSEILS ALIMENTAIRES :\n" .
                   "• 'Alimentation sans gluten'\n" .
                   "• 'Alimentation sans lactose'\n" .
                   "• 'Alimentation sans arachides'\n" .
                   "• 'Alimentation sans pollen'\n\n" .
                   "🔍 ANALYSE DE SYMPTÔMES :\n" .
                   "• 'Je gonfle après avoir mangé des cacahuètes' → Arachides\n" .
                   "• 'Je suis fatigué quand je mange du pain' → Gluten\n" .
                   "• 'J'ai des ballonnements après avoir bu du lait' → Lactose\n" .
                   "• 'Au printemps je éternue beaucoup' → Pollen\n\n" .
                   "💊 AUTRES COMMANDES :\n" .
                   "• 'Traitement pour Arachides'\n" .
                   "• 'Symptômes de Lactose'\n" .
                   "• 'Gravité de Gluten'\n" .
                   "• 'Liste des allergies'\n" .
                   "• 'Urgence'";
    }
    
    echo json_encode(['response' => $response]);
    exit;
}

// ========== AFFICHAGE DE LA PAGE ==========
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AllergieBot Pro - Assistant Médical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        .typing-dot { animation: bounce 1.4s infinite ease-in-out; }
        .typing-dot:nth-child(1) { animation-delay: 0s; }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        
        .online-pulse { animation: pulse 2s infinite; }
        
        #chatMessages::-webkit-scrollbar { width: 6px; }
        #chatMessages::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        #chatMessages::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }
        
        .message-enter { animation: fadeInUp 0.3s ease-out; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .whitespace-pre-wrap {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .example-card {
            transition: all 0.2s ease;
        }
        .example-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="container mx-auto px-4 py-6">
    <div class="max-w-5xl mx-auto">
        
        <div class="text-center mb-6">
            <div class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-3 mb-3 shadow-lg">
                <span class="text-3xl">🤖</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">AllergieBot <span class="text-sm text-blue-500">PRO</span></h1>
            <p class="text-gray-500 mt-1">Assistant médical intelligent - Recettes et conseils personnalisés</p>
            <div class="flex justify-center items-center gap-2 mt-2">
                <span class="w-2 h-2 bg-green-500 rounded-full online-pulse"></span>
                <span class="text-xs text-gray-500">En ligne</span>
                <span class="text-xs text-gray-300 mx-2">|</span>
                <span class="text-xs text-gray-500"><?= count($allergies) ?> allergies</span>
            </div>
            <a href="allergy_report.php" class="inline-flex items-center gap-1 mt-3 text-sm text-blue-600 hover:text-blue-800 transition">
                ← Retour à la liste des allergies
            </a>
        </div>
        
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <span class="text-xl">🤖</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-white">AllergieBot Pro</h2>
                        <p class="text-xs text-blue-100">Recettes et conseils personnalisés</p>
                    </div>
                </div>
            </div>
            
            <div id="chatMessages" class="h-96 overflow-y-auto p-4 bg-gray-50 space-y-3">
                
                <!-- Message de bienvenue complet -->
                <div class="flex justify-start message-enter">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-2xl rounded-tl-none p-4 max-w-[90%] shadow-sm">
                        <p class="text-sm whitespace-pre-wrap">👋 Bonjour ! Je suis AllergieBot Pro.

🍳 RECETTES :
• "Recettes pour allergie gluten"
• "Recettes pour allergie arachides"
• "Recettes pour allergie lactose"
• "Recettes pour allergie pollen"

🍽️ CONSEILS ALIMENTAIRES :
• "Alimentation sans gluten"
• "Alimentation sans lactose"
• "Alimentation sans arachides"
• "Alimentation sans pollen"

🔍 ANALYSE DE SYMPTÔMES :
• "Je gonfle après avoir mangé des cacahuètes" → Arachides
• "Je suis fatigué quand je mange du pain" → Gluten
• "J'ai des ballonnements après avoir bu du lait" → Lactose
• "Au printemps je éternue beaucoup" → Pollen

💊 AUTRES COMMANDES :
• "Traitement pour Arachides"
• "Symptômes de Lactose"
• "Gravité de Gluten"
• "Liste des allergies"
• "Urgence"</p>
                    </div>
                </div>
                
                <!-- Boutons rapides -->
                <div class="flex justify-start message-enter">
                    <div class="bg-gray-100 rounded-2xl rounded-tl-none p-4 max-w-[95%]">
                        <p class="text-xs text-gray-500 mb-3 font-semibold">💡 CLIQUEZ POUR TESTER :</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3">
                            <div class="bg-white rounded-xl p-3 shadow-sm example-card">
                                <p class="text-xs font-bold text-red-600 mb-1">🔴 ARACHIDES</p>
                                <button onclick="sendQuick('Recettes pour allergie arachides')" class="text-xs text-left w-full text-gray-700 hover:text-blue-600 transition">🍳 Recettes sans arachides</button>
                                <button onclick="sendQuick('Alimentation sans arachides')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">🍽️ Alimentation sans arachides</button>
                                <button onclick="sendQuick('Symptômes de arachides')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">🤧 Symptômes arachides</button>
                                <button onclick="sendQuick('Traitement pour Arachides')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">💊 Traitement</button>
                            </div>
                            <div class="bg-white rounded-xl p-3 shadow-sm example-card">
                                <p class="text-xs font-bold text-orange-600 mb-1">🟠 GLUTEN</p>
                                <button onclick="sendQuick('Recettes pour allergie gluten')" class="text-xs text-left w-full text-gray-700 hover:text-blue-600 transition">🍳 Recettes sans gluten</button>
                                <button onclick="sendQuick('Alimentation sans gluten')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">🍽️ Alimentation sans gluten</button>
                                <button onclick="sendQuick('Symptômes de gluten')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">🤧 Symptômes gluten</button>
                                <button onclick="sendQuick('Gravité de Gluten')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">⚠️ Gravité</button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div class="bg-white rounded-xl p-3 shadow-sm example-card">
                                <p class="text-xs font-bold text-yellow-600 mb-1">🟡 LACTOSE</p>
                                <button onclick="sendQuick('Recettes pour allergie lactose')" class="text-xs text-left w-full text-gray-700 hover:text-blue-600 transition">🍳 Recettes sans lactose</button>
                                <button onclick="sendQuick('Alimentation sans lactose')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">🍽️ Alimentation sans lactose</button>
                                <button onclick="sendQuick('Symptômes de lactose')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">🤧 Symptômes lactose</button>
                                <button onclick="sendQuick('Traitement pour Lactose')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">💊 Traitement</button>
                            </div>
                            <div class="bg-white rounded-xl p-3 shadow-sm example-card">
                                <p class="text-xs font-bold text-green-600 mb-1">🟢 POLLEN</p>
                                <button onclick="sendQuick('Recettes pour allergie pollen')" class="text-xs text-left w-full text-gray-700 hover:text-blue-600 transition">🍳 Recettes anti-pollen</button>
                                <button onclick="sendQuick('Alimentation sans pollen')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">🍽️ Alimentation sans pollen</button>
                                <button onclick="sendQuick('Symptômes de pollen de bouleau')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">🤧 Symptômes pollen</button>
                                <button onclick="sendQuick('Je éternue beaucoup au printemps')" class="text-xs text-left w-full text-gray-500 hover:text-blue-600 transition">🔍 "J'éternue au printemps"</button>
                            </div>
                        </div>
                        
                        <div class="mt-3 pt-2 border-t border-gray-200 flex flex-wrap gap-2">
                            <button onclick="sendQuick('Liste des allergies')" class="text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full hover:bg-blue-200 transition">📋 Liste des allergies</button>
                            <button onclick="sendQuick('Urgence')" class="text-xs bg-red-100 text-red-700 px-3 py-1.5 rounded-full hover:bg-red-200 transition">🚨 Urgence</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-4 border-t border-gray-200 bg-white">
                <div class="flex gap-2">
                    <input type="text" id="chatInput" 
                           placeholder="Ex: Recettes pour allergie gluten, Alimentation sans lactose, Symptômes de arachides..."
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                           onkeypress="if(event.key==='Enter') sendMessage()">
                    <button onclick="sendMessage()" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl hover:shadow-lg transition font-semibold">
                        Envoyer
                    </button>
                </div>
                <p class="text-xs text-gray-400 text-center mt-2">
                    ⚠️ En cas d'urgence médicale (difficultés respiratoires, gonflement de la gorge), appelez le 15 ou 112
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function sendQuick(message) {
    document.getElementById('chatInput').value = message;
    sendMessage();
}

async function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message) return;
    
    addMessage(message, 'user');
    input.value = '';
    showTyping();
    
    try {
        const response = await fetch('chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ message: message })
        });
        const data = await response.json();
        removeTyping();
        addMessage(data.response, 'bot');
    } catch(error) {
        removeTyping();
        addMessage("❌ Erreur, veuillez réessayer.", 'bot');
    }
}

function addMessage(content, sender) {
    const messagesDiv = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'} mb-3 message-enter`;
    
    if (sender === 'user') {
        messageDiv.innerHTML = `<div class="bg-green-500 text-white rounded-2xl rounded-tr-none p-3 max-w-[80%]"><p class="text-sm whitespace-pre-wrap">${escapeHtml(content)}</p><span class="text-xs text-green-200 mt-1 block">👤 Vous</span></div>`;
    } else {
        messageDiv.innerHTML = `<div class="bg-white border border-gray-200 rounded-2xl rounded-tl-none p-3 max-w-[85%] shadow-sm"><p class="text-sm text-gray-800 whitespace-pre-wrap">${escapeHtml(content)}</p><span class="text-xs text-gray-400 mt-1 block">🤖 AllergieBot Pro</span></div>`;
    }
    
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showTyping() {
    const messagesDiv = document.getElementById('chatMessages');
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typingIndicator';
    typingDiv.className = 'flex justify-start mb-3';
    typingDiv.innerHTML = `<div class="bg-gray-200 rounded-2xl rounded-tl-none p-3"><div class="flex gap-1"><div class="w-2 h-2 bg-gray-500 rounded-full typing-dot"></div><div class="w-2 h-2 bg-gray-500 rounded-full typing-dot"></div><div class="w-2 h-2 bg-gray-500 rounded-full typing-dot"></div></div><span class="text-xs text-gray-500 mt-1 block">🤖 AllergieBot réfléchit...</span></div>`;
    messagesDiv.appendChild(typingDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function removeTyping() {
    const typing = document.getElementById('typingIndicator');
    if (typing) typing.remove();
}
</script>

</body>
</html>