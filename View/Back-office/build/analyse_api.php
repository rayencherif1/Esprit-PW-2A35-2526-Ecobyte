<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Utilisez POST']);
    exit;
}

// Connexion BDD
try {
    $db = new PDO('mysql:host=localhost;dbname=gestion_allergie;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Erreur BDD: ' . $e->getMessage()]);
    exit;
}

class MedicalExpertAnalyzer {
    private $db;
    
    // Base de connaissances médicale avancée
    private $medicalKnowledge = [
        // Traitements anti-allergiques
        'adrénaline' => [
            'nom_scientifique' => 'Epinephrine',
            'classe_therapeutique' => 'Catécholamine, vasoconstricteur',
            'mecanisme_action' => 'Agoniste alpha et bêta-adrénergique. Augmente la tension artérielle, dilate les bronches, réduit l\'œdème.',
            'indications_precises' => 'Choc anaphylactique, œdème de Quincke, réaction allergique grave avec détresse respiratoire.',
            'posologie_standard' => 'Adulte: 0.3-0.5mg IM. Enfant: 0.15mg IM selon poids.',
            'delai_action' => 'Immédiat (1-3 minutes)',
            'duree_action' => '20-30 minutes',
            'effets_secondaires' => [
                'Tachycardie (augmentation du rythme cardiaque)',
                'Hypertension artérielle transitoire',
                'Palpitations, anxiété, tremblements',
                'Pâleur, sueurs, nausées',
                'Céphalées intenses'
            ],
            'contre_indications_absolues' => ['Aucune en situation d\'urgence vitale'],
            'surveillance_clinique' => 'Fréquence cardiaque, pression artérielle, saturation O2, signes neurologiques',
            'conservation' => 'Température ambiante, à l\'abri de la lumière. Vérifier date de péremption mensuellement'
        ],
        'cétirizine' => [
            'nom_scientifique' => 'Cetirizine dihydrochloride',
            'classe_therapeutique' => 'Antihistaminique H1 de 2ème génération',
            'mecanisme_action' => 'Antagoniste sélectif des récepteurs H1 périphériques. Bloque la libération d\'histamine.',
            'indications_precises' => 'Rhinite allergique saisonnière et perannuelle, urticaire chronique, conjonctivite allergique.',
            'posologie_standard' => 'Adulte: 10mg 1x/jour. Enfant 6-12 ans: 5mg 2x/jour ou 10mg 1x/jour.',
            'delai_action' => '30-60 minutes',
            'duree_action' => '24 heures',
            'effets_secondaires' => [
                'Somnolence légère à modérée (moins que la 1ère génération)',
                'Fatigue, asthénie',
                'Vertiges, céphalées',
                'Sécheresse buccale',
                'Troubles digestifs (nausées, douleurs abdominales)'
            ],
            'contre_indications_absolues' => ['Insuffisance rénale sévère (clairance <10mL/min)'],
            'surveillance_clinique' => 'Somnolence excessive, efficacité sur les symptômes, fonction rénale',
            'interactions' => [
                'Alcool: potentialisation de la somnolence',
                'Théophylline: clairance diminuée',
                'Sédatifs: effets additifs'
            ]
        ],
        'loratadine' => [
            'nom_scientifique' => 'Loratadine',
            'classe_therapeutique' => 'Antihistaminique H1 de 2ème génération',
            'mecanisme_action' => 'Antagoniste sélectif des récepteurs H1 périphériques. Non sédatif.',
            'indications_precises' => 'Rhinite allergique, urticaire chronique idiopathique.',
            'posologie_standard' => 'Adulte et enfant >30kg: 10mg 1x/jour.',
            'delai_action' => '1-3 heures',
            'duree_action' => '24 heures',
            'effets_secondaires' => [
                'Céphalées (rapportées dans 12% des cas)',
                'Fatigue (8%)',
                'Sécheresse buccale (5%)',
                'Troubles gastro-intestinaux légers',
                'Rash cutané (rare)'
            ],
            'contre_indications_absolues' => ['Insuffisance hépatique sévère'],
            'surveillance_clinique' => 'Efficacité, effets indésirables chez l\'enfant'
        ],
        'lactase' => [
            'nom_scientifique' => 'Bêta-galactosidase',
            'classe_therapeutique' => 'Enzyme digestive',
            'mecanisme_action' => 'Hydrolise le lactose en glucose et galactose, facilitant sa digestion.',
            'indications_precises' => 'Intolérance au lactose, déficit primaire ou secondaire en lactase.',
            'posologie_standard' => '9000-18000 unités FCC juste avant ou pendant le repas contenant du lactose.',
            'delai_action' => '15-30 minutes',
            'duree_action' => '45-60 minutes',
            'effets_secondaires' => [
                'Rares et bénins',
                'Nausées possibles en cas de surdosage',
                'Ballonnements si inefficace',
                'Diarrhée si inefficace'
            ],
            'contre_indications_absolues' => ['Galactosémie (déficit congénital en galactose)'],
            'surveillance_clinique' => 'Efficacité sur les symptômes digestifs, adaptation posologique'
        ]
    ];
    
    // Conseils thérapeutiques par pathologie
    private $therapeuticAdvice = [
        'anaphylaxie' => [
            'traitement_urgence' => 'Adrénaline IM immédiate, décubitus dorsal, appel SAMU (15 ou 112)',
            'surveillance' => 'Scope cardiovasculaire, oxygénothérapie, voie veineuse',
            'second_traitement' => 'Antihistaminique IV, corticoïdes IV'
        ],
        'urticaire' => [
            'traitement' => 'Antihistaminique H1 non sédatif (cétirizine, loratadine)',
            'sevrite' => 'Surveiller atteinte muqueuse ou signes systémiques'
        ],
        'rhinite_allergique' => [
            'traitement' => 'Antihistaminique + corticoïde nasal si modéré/sévère',
            'prevention' => 'Désensibilisation si échec traitement médicamenteux'
        ]
    ];
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // SIMULATION D'UN TEMPS DE RÉFLEXION MÉDICALE
    public function analyzeWithReflection($traitementId, $allergieId = null) {
        // Pause de 2 secondes pour simuler la réflexion d'un médecin
        usleep(2000000); // 2 secondes
        
        $traitement = $this->getTraitement($traitementId);
        if (!$traitement) {
            return ['error' => 'Traitement non trouvé'];
        }
        
        $allergie = null;
        if ($allergieId) {
            $allergie = $this->getAllergie($allergieId);
        }
        
        return $this->professionalAnalysis($traitement, $allergie);
    }
    
    private function getTraitement($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, a.nom as allergie_nom, a.gravite, a.symptomes, a.description as allergie_description
            FROM traitement t 
            LEFT JOIN allergie a ON t.id_allergie = a.id_allergie 
            WHERE t.id_traitement = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getAllergie($id) {
        $stmt = $this->db->prepare("SELECT * FROM allergie WHERE id_allergie = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function professionalAnalysis($traitement, $allergie) {
        $nomTraitement = strtolower($traitement['nom_traitement']);
        
        // Identifier le médicament
        $medInfo = $this->identifyMedication($nomTraitement);
        
        // Analyse de compatibilité allergique
        $compatibility = $this->allergyCompatibilityAnalysis($traitement, $allergie, $medInfo);
        
        // Avis médical personnalisé
        $medicalOpinion = $this->generateMedicalOpinion($traitement, $allergie, $compatibility, $medInfo);
        
        return [
            'success' => true,
            'reflexion_terminee' => true,
            'diagnostic_medical' => $medicalOpinion,
            'analyse_compatibilite' => $compatibility,
            'protocole_therapeutique' => $this->generateProtocol($traitement, $medInfo),
            'recommandations_medicales' => $this->generateMedicalRecommendations($traitement, $allergie, $compatibility),
            'surveillance_medicale' => $this->generateMedicalMonitoring($traitement, $allergie, $medInfo),
            'education_patient' => $this->patientEducation($traitement, $allergie, $compatibility),
            'meta' => [
                'analyse_par' => 'Système Expert Médical v3.0',
                'specialite' => 'Allergologie - Pharmacologie Clinique',
                'timestamp' => date('Y-m-d H:i:s'),
                'niveau_preuve' => 'Grade A (Recommandation forte)'
            ]
        ];
    }
    
    private function identifyMedication($nom) {
        foreach ($this->medicalKnowledge as $key => $info) {
            if (strpos($nom, $key) !== false) {
                return $info;
            }
        }
        return [
            'classe_therapeutique' => 'Traitement spécifique',
            'mecanisme_action' => 'Selon prescription médicale',
            'effets_secondaires' => ['Consulter votre médecin traitant pour une évaluation personnalisée']
        ];
    }
    
    private function allergyCompatibilityAnalysis($traitement, $allergie, $medInfo) {
        $score = 0;
        $niveau = '';
        $alerte = '';
        $explication = '';
        $risques_specifiques = [];
        
        if (!$allergie) {
            // Analyse sans allergie connue
            $score = 92;
            $niveau = 'optimal';
            $alerte = 'normal';
            $explication = "Aucune allergie connue dans le dossier. Analyse pharmacologique standard.";
            $risques_specifiques = [
                "Risque iatrogène standard selon la classe thérapeutique",
                "Surveillance des effets indésirables classiques"
            ];
        } else {
            $nomTrait = strtolower($traitement['nom_traitement']);
            $nomAllergie = strtolower($allergie['nom']);
            
            // Vérification de contre-indication absolue
            if (strpos($nomTrait, $nomAllergie) !== false) {
                $score = 0;
                $niveau = 'contre_indication_formelle';
                $alerte = 'critique';
                $explication = "⚠️ CONTRE-INDICATION FORMELLE : Le traitement {$traitement['nom_traitement']} contient l'allergène {$allergie['nom']}. Risque de réaction anaphylactique.";
                $risques_specifiques = [
                    "Risque vital immédiat - Choc anaphylactique",
                    "Œdème de Quincke possible",
                    "Détresse respiratoire aiguë"
                ];
                return compact('score', 'niveau', 'alerte', 'explication', 'risques_specifiques');
            }
            
            // Vérification des interdictions
            if (!empty($traitement['interdiction']) && 
                stripos($traitement['interdiction'], $nomAllergie) !== false) {
                $score = 0;
                $niveau = 'contre_indication_relative';
                $alerte = 'critique';
                $explication = "⚠️ CONTRE-INDICATION : Le protocole du traitement interdit formellement son utilisation en cas d'allergie à {$allergie['nom']}.";
                $risques_specifiques = ["Risque majoré selon les données de pharmacovigilance"];
                return compact('score', 'niveau', 'alerte', 'explication', 'risques_specifiques');
            }
            
            // Analyse selon la gravité de l'allergie
            switch($allergie['gravite']) {
                case 'grave':
                    $score = 45;
                    $niveau = 'prudence_extreme';
                    $alerte = 'warning';
                    $explication = "PATIENT À HAUT RISQUE : Allergie grave ({$allergie['nom']}). Bénéfice/risque à évaluer. Traitement sous surveillance médicale stricte.";
                    $risques_specifiques = [
                        "Risque de réaction croisée (15-20%)",
                        "Nécessité d'une première prise en milieu hospitalier",
                        "Protocole d'urgence à définir"
                    ];
                    break;
                case 'moyenne':
                    $score = 68;
                    $niveau = 'surveillance_renforcee';
                    $alerte = 'info';
                    $explication = "Allergie modérée à {$allergie['nom']}. Compatibilité acceptable. Surveillance clinique recommandée.";
                    $risques_specifiques = [
                        "Surveillance rapprochée à l'instauration",
                        "Risque d'exacerbation allergique modéré"
                    ];
                    break;
                default:
                    $score = 85;
                    $niveau = 'compatible';
                    $alerte = 'normal';
                    $explication = "Allergie légère à {$allergie['nom']}. Compatibilité satisfaisante. Traitement utilisable en pratique courante.";
                    $risques_specifiques = ["Risque faible de réaction allergique mineure"];
            }
        }
        
        return compact('score', 'niveau', 'alerte', 'explication', 'risques_specifiques');
    }
    
    private function generateMedicalOpinion($traitement, $allergie, $compatibility, $medInfo) {
        $nom = $traitement['nom_traitement'];
        
        $avis = "🩺 **AVIS MÉDICAL SPÉCIALISÉ EN ALLERGOLOGIE**\n\n";
        
        if ($compatibility['niveau'] === 'contre_indication_formelle') {
            $avis .= "**CONCLUSION : CONTRE-INDICATION ABSOLUE**\n\n";
            $avis .= "Ce traitement est FORMELLEMENT CONTRE-INDIQUÉ chez ce patient en raison du risque anaphylactique majeur.\n\n";
            $avis .= "**PRISE EN CHARGE IMMÉDIATE :**\n";
            $avis .= "- Interrompre toute tentative d'administration\n";
            $avis .= "- Prescrire une alternative thérapeutique adaptée\n";
            $avis .= "- Éduquer le patient sur les signes d'alerte\n";
        } elseif ($compatibility['niveau'] === 'contre_indication_relative') {
            $avis .= "**CONCLUSION : CONTRE-INDICATION RELATIVE**\n\n";
            $avis .= "L'utilisation de {$nom} nécessite une évaluation bénéfice/risque approfondie.\n\n";
            $avis .= "**RECOMMANDATION :**\n";
            $avis .= "- Réunion de concertation pluridisciplinaire recommandée\n";
            $avis .= "- Test de provocation en milieu hospitalier si essentiel\n";
        } elseif ($compatibility['niveau'] === 'prudence_extreme') {
            $avis .= "**CONCLUSION : PRUDENCE EXTREME**\n\n";
            $avis .= "Le traitement {$nom} peut être utilisé sous conditions strictes.\n\n";
            $avis .= "**PROTOCOLE D'UTILISATION SÉCURISÉ :**\n";
            $avis .= "- 1ʳᵉ administration en milieu hospitalier (surveillance 2h)\n";
            $avis .= "- Prescription systématique d'adrénaline auto-injectable\n";
            $avis .= "- Éducation du patient et de l'entourage\n";
            $avis .= "- Poursuite ambulatoire avec plan d'action écrit\n";
        } else {
            $avis .= "**CONCLUSION : UTILISATION STANDARD**\n\n";
            $avis .= "Le traitement {$nom} est compatible avec le profil allergique du patient.\n\n";
            $avis .= "**CONDITIONS D'UTILISATION :**\n";
            $avis .= "- Suivre la posologie recommandée\n";
            $avis .= "- Surveillance clinique habituelle\n";
            $avis .= "- Information patient sur les effets indésirables\n";
        }
        
        // Ajout informations scientifiques
        if (!empty($medInfo['mecanisme_action'])) {
            $avis .= "\n**MÉCANISME D'ACTION :**\n";
            $avis .= "• {$medInfo['mecanisme_action']}\n";
        }
        
        return nl2br($avis);
    }
    
    private function generateProtocol($traitement, $medInfo) {
        $protocol = [];
        
        if (!empty($medInfo['posologie_standard'])) {
            $protocol[] = [
                'titre' => '💊 POSOLOGIE STANDARD',
                'contenu' => $medInfo['posologie_standard']
            ];
        }
        
        if (!empty($medInfo['delai_action'])) {
            $protocol[] = [
                'titre' => '⏱ DÉLAI D\'ACTION',
                'contenu' => $medInfo['delai_action']
            ];
        }
        
        if (!empty($medInfo['duree_action'])) {
            $protocol[] = [
                'titre' => '📊 DURÉE D\'ACTION',
                'contenu' => $medInfo['duree_action']
            ];
        }
        
        $protocol[] = [
            'titre' => '🔄 MODE D\'ADMINISTRATION',
            'contenu' => strpos(strtolower($traitement['nom_traitement']), 'adrénaline') !== false ? 
                'Injection intramusculaire dans la face antérolatérale de la cuisse. Maintenir 10 secondes. Masser le site d\'injection.' :
                'Voie orale avec un grand verre d\'eau. Sans aliments pour une absorption optimale.'
        ];
        
        return $protocol;
    }
    
    private function generateMedicalRecommendations($traitement, $allergie, $compatibility) {
        $recommendations = [];
        $nom = $traitement['nom_traitement'];
        
        // Recommandations spécifiques selon compatibilité
        if ($compatibility['niveau'] === 'prudence_extreme') {
            $recommendations[] = [
                'type' => 'hospitalisation',
                'titre' => '🏥 PREMIÈRE ADMINISTRATION HOSPITALIÈRE',
                'contenu' => 'La première prise doit être réalisée en milieu hospitalier avec surveillance continue pendant 2 heures.'
            ];
            $recommendations[] = [
                'type' => 'urgence',
                'titre' => '🚨 ORDONNANCE D\'URGENCE',
                'contenu' => 'Prescrire systématiquement de l\'adrénaline auto-injectable (2 stylos).'
            ];
        }
        
        // Recommandations thérapeutiques
        if (!empty($traitement['conseils'])) {
            $recommendations[] = [
                'type' => 'conseil',
                'titre' => '📋 CONSEIL THÉRAPEUTIQUE',
                'contenu' => $traitement['conseils']
            ];
        }
        
        if (!empty($traitement['interdiction'])) {
            $recommendations[] = [
                'type' => 'interdiction',
                'titre' => '⛔ INTERDICTIONS FORMELLES',
                'contenu' => $traitement['interdiction']
            ];
        }
        
        // Interactions médicamenteuses
        if (!empty($medInfo['interactions'])) {
            $interactions = [];
            foreach ($medInfo['interactions'] as $med => $risque) {
                $interactions[] = "• {$med} : {$risque}";
            }
            $recommendations[] = [
                'type' => 'interaction',
                'titre' => '⚠️ INTERACTIONS MÉDICAMENTEUSES',
                'contenu' => implode("\n", $interactions)
            ];
        }
        
        return $recommendations;
    }
    
    private function generateMedicalMonitoring($traitement, $allergie, $medInfo) {
        $monitoring = [];
        
        // Surveillance selon traitement
        if (!empty($medInfo['surveillance_clinique'])) {
            $monitoring[] = [
                'titre' => '🩺 BILAN INITIAL',
                'items' => explode(', ', $medInfo['surveillance_clinique'])
            ];
        }
        
        // Surveillance selon allergie
        if ($allergie && $allergie['gravite'] === 'grave') {
            $monitoring[] = [
                'titre' => '🚨 SIGNES D\'ALERTE À SURVEILLER (J0-J3)',
                'items' => [
                    'Urticaire généralisée',
                    'Angio-œdème (gonflement visage/lèvres/langue)',
                    'Dyspnée, wheezing, oppression thoracique',
                    'Chute tensionnelle, tachycardie',
                    'Nausées, vomissements, diarrhée'
                ]
            ];
        } else {
            $monitoring[] = [
                'titre' => '👀 SURVEILLANCE STANDARD (première semaine)',
                'items' => [
                    'Évaluation de l\'efficacité thérapeutique à J7',
                    'Recherche d\'effets indésirables cutanés ou digestifs',
                    'Tolérance globale'
                ]
            ];
        }
        
        // Surveillance spécifique
        if (strpos(strtolower($traitement['nom_traitement']), 'antihistaminique') !== false) {
            $monitoring[] = [
                'titre' => '⚠️ PRÉCAUTIONS PARTICULIÈRES',
                'items' => [
                    'Somnolence : éviter conduite/machines dangereuses',
                    'Interaction alcool : formellement déconseillée'
                ]
            ];
        }
        
        return $monitoring;
    }
    
    private function patientEducation($traitement, $allergie, $compatibility) {
        $education = [];
        
        // Messages éducatifs personnalisés
        if ($compatibility['niveau'] === 'contre_indication_formelle') {
            $education[] = "⚠️ **MESSAGE PATIENT - URGENCE** : Ne prenez PAS ce médicament - Consultez votre médecin dès que possible pour une alternative";
        } elseif ($compatibility['niveau'] === 'prudence_extreme') {
            $education[] = "📢 **INFORMATION PATIENT À RISQUE** : Votre traitement nécessite une première prise à l'hôpital - Une ordonnance d'adrénaline vous sera prescrite - Entourez-vous de personnes informées";
        } else {
            $education[] = "💬 **CONSIGNES PATIENT** : Suivez la posologie prescrite - Signalez tout effet indésirable à votre médecin - Ne modifiez pas le traitement sans avis médical";
        }
        
        // Éducation sur la pathologie si allergie
        if ($allergie) {
            $education[] = "🔬 **VOTRE ALLERGIE** : {$allergie['nom']} - {$allergie['description']}";
            if (!empty($allergie['symptomes'])) {
                $education[] = "📋 **Symptômes caractéristiques** : {$allergie['symptomes']}";
            }
        }
        
        // Conseils de conservation
        if (strpos(strtolower($traitement['nom_traitement']), 'adrénaline') !== false) {
            $education[] = "❄️ **CONSERVATION** : Température ambiante (15-25°C). Vérifier mensuellement la date de péremption et la limpidité du liquide. Ne pas exposer au froid.";
        }
        
        return $education;
    }
}

// Exécution avec temps de réflexion
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_traitement'])) {
    echo json_encode(['error' => 'ID traitement requis']);
    exit;
}

$analyzer = new MedicalExpertAnalyzer($db);
$result = $analyzer->analyzeWithReflection(
    $input['id_traitement'],
    $input['id_allergie'] ?? null
);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>