<?php
/**
 * ollama_api.php - API pour Ollama avec phi3:mini
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id_traitement = $input['id_traitement'] ?? null;
$id_allergie = $input['id_allergie'] ?? null;

if (!$id_traitement) {
    echo json_encode(['error' => 'ID traitement manquant']);
    exit;
}

// Récupérer les données depuis votre base
require_once __DIR__ . '/../../../Controller/traitement.Controller.php';
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';

$traitementController = new TraitementC();
$traitement = $traitementController->getTraitementById($id_traitement);

if (!$traitement) {
    echo json_encode(['error' => 'Traitement non trouvé']);
    exit;
}

$allergieController = new AllergieC();
$allergie = null;
if ($id_allergie && $id_allergie !== 'null') {
    $allergie = $allergieController->getAllergieById($id_allergie);
}

// Construction du prompt pour phi3:mini
$prompt = "Tu es un assistant médical expert en pharmacologie. 
Analyse ce traitement et retourne UNIQUEMENT un objet JSON valide.

TRAITEMENT:
- Nom: " . $traitement['nom_traitement'] . "
- Conseils officiels: " . ($traitement['conseils'] ?? 'Non renseigné') . "
- Interdictions: " . ($traitement['interdiction'] ?? 'Non renseigné');

if ($allergie) {
    $prompt .= "
    
ALLERGIE:
- Nom: " . $allergie['nom'] . "
- Gravité: " . $allergie['gravite'] . "
- Symptômes: " . ($allergie['symptomes'] ?? 'Non renseigné');
}

$prompt .= "

Retourne EXACTEMENT ce format JSON:
{
    \"interactions\": [
        {\"nom\": \"Nom médicament\", \"detail\": \"Description risque\", \"niveau\": \"danger|warning|info\"}
    ],
    \"populations\": [
        {\"groupe\": \"Groupe risque\", \"conseil\": \"Recommandation\", \"niveau\": \"danger|warning|info\"}
    ],
    \"conseils\": [\"Conseil 1\", \"Conseil 2\"],
    \"alternatives\": [{\"nom\": \"Alternative\", \"avantage\": \"Avantage\"}],
    \"resume\": \"Résumé clinique court\",
    \"alerte\": \"critique|eleve|info\"
}

Si aucune interaction, mettre []. Sois précis.";

// Appel à Ollama
$ch = curl_init('http://localhost:11434/api/generate');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'phi3:mini',
        'prompt' => $prompt,
        'stream' => false,
        'options' => [
            'temperature' => 0.3,
            'num_predict' => 1500
        ]
    ]),
    CURLOPT_TIMEOUT => 120,
    CURLOPT_CONNECTTIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    echo json_encode(generateFallbackAnalysis($traitement, $allergie));
    exit;
}

$data = json_decode($response, true);
$text = $data['response'] ?? '';

// Nettoyage du JSON
$text = preg_replace('/```json\s*|\s*```/', '', $text);
$text = trim($text);

$result = json_decode($text, true);

if (!$result || !is_array($result)) {
    $result = generateFallbackAnalysis($traitement, $allergie);
}

echo json_encode($result);
exit;

// Fallback si Ollama ne répond pas
function generateFallbackAnalysis($traitement, $allergie) {
    $nom = strtolower($traitement['nom_traitement'] ?? '');
    
    $interactions = [];
    $populations = [];
    $conseils = [];
    $alternatives = [];
    $alerte = 'info';
    
    // Détection du type de médicament
    if (strpos($nom, 'cétirizine') !== false || strpos($nom, 'cetirizine') !== false) {
        $interactions = [
            ['nom' => 'Alcool', 'detail' => 'Sédation renforcée', 'niveau' => 'warning'],
            ['nom' => 'Benzodiazépines', 'detail' => 'Synergie sédative dangereuse', 'niveau' => 'danger']
        ];
        $populations = [
            ['groupe' => 'Insuffisance rénale', 'conseil' => 'Réduire la dose de moitié', 'niveau' => 'warning'],
            ['groupe' => 'Personnes âgées', 'conseil' => 'Risque de chutes', 'niveau' => 'warning']
        ];
        $conseils = [
            'Prise idéale : 21h pour éviter la somnolence',
            'Ne pas doubler la dose en cas d\'oubli'
        ];
        $alternatives = [
            ['nom' => 'Fexofénadine', 'avantage' => 'Non sédatif'],
            ['nom' => 'Loratadine', 'avantage' => 'Alternative non sédative']
        ];
    } elseif (strpos($nom, 'ibuprofène') !== false) {
        $interactions = [
            ['nom' => 'Aspirine', 'detail' => 'Risque hémorragique', 'niveau' => 'danger'],
            ['nom' => 'Anticoagulants', 'detail' => 'Risque hémorragique majoré', 'niveau' => 'danger']
        ];
        $populations = [
            ['groupe' => 'Ulcère gastrique', 'conseil' => 'Contre-indication', 'niveau' => 'danger'],
            ['groupe' => 'Asthme', 'conseil' => 'Risque de bronchospasme', 'niveau' => 'warning']
        ];
        $conseils = [
            'Prendre pendant le repas',
            'Durée max : 5 jours sans avis médical'
        ];
        $alternatives = [
            ['nom' => 'Paracétamol', 'avantage' => 'Moins d\'effets digestifs']
        ];
    } else {
        $conseils = [
            'Respecter la posologie prescrite',
            'Ne pas interrompre brutalement le traitement',
            'Consulter en cas d\'effets indésirables'
        ];
        $alternatives = [
            ['nom' => 'Consultation médicale', 'avantage' => 'Évaluation personnalisée']
        ];
        if (empty($interactions)) {
            $interactions = [['nom' => 'Aucune interaction majeure', 'detail' => 'Surveillance habituelle', 'niveau' => 'info']];
        }
        if (empty($populations)) {
            $populations = [['groupe' => 'Population générale', 'conseil' => 'Précautions standards', 'niveau' => 'info']];
        }
    }
    
    $resume = "Analyse de " . $traitement['nom_traitement'] . ". " . ($alerte === 'critique' ? 'Alerte critique - Consultation médicale obligatoire.' : 'Respecter les précautions d\'emploi.');
    
    return [
        'interactions' => $interactions,
        'populations' => $populations,
        'conseils' => $conseils,
        'alternatives' => $alternatives,
        'resume' => $resume,
        'alerte' => $alerte
    ];
}
?>