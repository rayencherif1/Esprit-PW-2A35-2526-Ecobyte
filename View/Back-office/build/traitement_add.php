<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/traitement.php';
require_once __DIR__ . '/../../../Controller/traitement.Controller.php';

session_start();

$errors = [];
$success = false;

$id_allergie = $_GET['id_allergie'] ?? null;

if (!$id_allergie) {
    die("Erreur : allergie non sélectionnée");
}

// Récupérer la connexion PDO
$conn = config::getConnexion();

// Fonction pour vérifier si l'allergie existe
function verifierAllergieExiste($id_allergie, $db) {
    try {
        $stmt = $db->prepare("SELECT 1 FROM allergie WHERE id_allergie = ?");
        $stmt->execute([$id_allergie]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log('Erreur verifierAllergieExiste: ' . $e->getMessage());
        return false;
    }
}

// Fonction pour vérifier si le traitement existe déjà
function verifierTraitementExiste($id_allergie, $nom_traitement, $db) {
    try {
        $stmt = $db->prepare("SELECT 1 FROM traitement WHERE id_allergie = ? AND nom_traitement = ?");
        $stmt->execute([$id_allergie, $nom_traitement]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log('Erreur verifierTraitementExiste: ' . $e->getMessage());
        return false;
    }
}

// Vérifier si l'allergie existe
if (!$conn) {
    die("Erreur de connexion à la base de données");
}

$allergieExists = verifierAllergieExiste($id_allergie, $conn);
if (!$allergieExists) {
    die("Erreur : cette allergie n'existe pas");
}

// Générer token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$old = [
    'nom_traitement' => '',
    'conseils' => '',
    'interdiction' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['general'] = "Token de sécurité invalide";
    } else {
        
        $nom_traitement = trim($_POST['nom_traitement'] ?? '');
        $conseils = trim($_POST['conseils'] ?? '');
        $interdiction = trim($_POST['interdiction'] ?? '');

        $old = compact('nom_traitement', 'conseils', 'interdiction');

        // Validation Nom
        if (empty($nom_traitement)) {
            $errors['nom_traitement'] = "Le nom du traitement est obligatoire.";
        } elseif (mb_strlen($nom_traitement) < 3) {
            $errors['nom_traitement'] = "Le nom doit contenir au moins 3 caractères.";
        } elseif (mb_strlen($nom_traitement) > 255) {
            $errors['nom_traitement'] = "Le nom ne doit pas dépasser 255 caractères.";
        }

        // Validation Conseils
        if (empty($conseils)) {
            $errors['conseils'] = "Les conseils sont obligatoires.";
        } elseif (mb_strlen($conseils) > 1000) {
            $errors['conseils'] = "Les conseils ne doivent pas dépasser 1000 caractères.";
        }

        // Validation Interdiction
        if (empty($interdiction)) {
            $errors['interdiction'] = "Les interdictions sont obligatoires.";
        } elseif (mb_strlen($interdiction) > 1000) {
            $errors['interdiction'] = "Les interdictions ne doivent pas dépasser 1000 caractères.";
        }

        // Vérifier si un traitement existe déjà pour cette allergie
        if (empty($errors)) {
            $traitementExiste = verifierTraitementExiste($id_allergie, $nom_traitement, $conn);
            if ($traitementExiste) {
                $errors['nom_traitement'] = "Un traitement avec ce nom existe déjà pour cette allergie.";
            }
        }

        // Insertion
        if (empty($errors)) {
            try {
                $traitement = new Traitement(
                    null,
                    $nom_traitement,
                    $conseils,
                    $interdiction,
                    $id_allergie
                );

                $controller = new TraitementC();
                $result = $controller->addTraitement($traitement);
                
                if ($result) {
                    $success = true;
                    // Redirection avec message de succès
                    header("Location: traitement_list.php?id_allergie=" . $id_allergie . "&success=added");
                    exit();
                } else {
                    $errors['general'] = "Erreur lors de l'ajout du traitement";
                }
            } catch (Exception $e) {
                $errors['general'] = "Une erreur est survenue : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Ajouter Traitement</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; margin: 0; padding: 24px; }
        .wrap { max-width: 640px; margin: auto; }
        h1 { margin-bottom: 8px; color: #0f172a; }
        .lead { color: #64748b; margin-bottom: 20px; }
        
        label { font-weight: 600; display: block; margin-top: 14px; color: #334155; }
        
        input, textarea {
            width: 100%; padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin-top: 6px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        textarea { min-height: 100px; resize: vertical; }
        
        .btn {
            margin-top: 24px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
        }
        
        .btn:hover { background: #1d4ed8; }
        .btn:active { transform: translateY(1px); }
        
        .err { color: #dc2626; font-size: 0.85rem; margin-top: 4px; }
        .success { color: #059669; font-size: 0.85rem; margin-top: 4px; }
        .alert { background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #991b1b; }
        
        .card {
            background: white;
            padding: 28px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .info-badge {
            background: #e0e7ff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            color: #3730a3;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="wrap">
    <h1>💊 Ajouter un traitement</h1>
    <p class="lead">Associer un traitement à une allergie</p>
    
    <div class="card">
        <?php if (isset($errors['general'])): ?>
            <div class="alert"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>
        
        <div class="info-badge">
            🔍 ID Allergie : <?= htmlspecialchars($id_allergie) ?>
        </div>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <label>Nom traitement *</label>
            <input type="text" name="nom_traitement"
                   value="<?= htmlspecialchars($old['nom_traitement']) ?>"
                   placeholder="Ex: Antihistaminique H1">
            <?php if (isset($errors['nom_traitement'])): ?>
                <div class="err"><?= $errors['nom_traitement'] ?></div>
            <?php endif; ?>
            
            <label>Conseils *</label>
            <textarea name="conseils" 
                      placeholder="Conseils pour le patient..."><?= htmlspecialchars($old['conseils']) ?></textarea>
            <?php if (isset($errors['conseils'])): ?>
                <div class="err"><?= $errors['conseils'] ?></div>
            <?php endif; ?>
            
            <label>Interdictions *</label>
            <textarea name="interdiction"
                      placeholder="Ce qu'il faut éviter..."><?= htmlspecialchars($old['interdiction']) ?></textarea>
            <?php if (isset($errors['interdiction'])): ?>
                <div class="err"><?= $errors['interdiction'] ?></div>
            <?php endif; ?>
            
            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn">➕ Ajouter le traitement</button>
                <a href="traitement_list.php?id_allergie=<?= urlencode($id_allergie) ?>" 
                   style="margin-top: 24px; padding: 10px 20px; text-decoration: none; color: #64748b;">
                    ↩ Retour
                </a>
            </div>
        </form>
    </div>
</div>
</body>
</html>