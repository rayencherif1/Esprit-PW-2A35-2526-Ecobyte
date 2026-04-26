<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/allergie.php';
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';

$errors = [];
$old = [
    'nom' => '',
    'description' => '',
    'gravite' => '',
    'symptomes' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $gravite = trim($_POST['gravite'] ?? '');
    $symptomes = trim($_POST['symptomes'] ?? '');

    // Sauvegarder les anciennes valeurs
    $old = compact('nom', 'description', 'gravite', 'symptomes');

    // Validation Nom
    if (empty($nom)) {
        $errors['nom'] = "Le nom est obligatoire.";
    } elseif (mb_strlen($nom) < 3) {
        $errors['nom'] = "Le nom doit contenir au moins 3 caractères.";
    } elseif (mb_strlen($nom) > 255) {
        $errors['nom'] = "Le nom ne doit pas dépasser 255 caractères.";
    }

    // Validation Description
    if (empty($description)) {
        $errors['description'] = "La description est obligatoire.";
    } elseif (mb_strlen($description) > 1000) {
        $errors['description'] = "La description ne doit pas dépasser 1000 caractères.";
    }

    // Validation Gravité
    $gravitesValides = ['faible', 'moyenne', 'grave'];
    if (empty($gravite)) {
        $errors['gravite'] = "La gravité est obligatoire.";
    } elseif (!in_array($gravite, $gravitesValides, true)) {
        $errors['gravite'] = "Valeur de gravité invalide.";
    }

    // Validation Symptômes
    if (empty($symptomes)) {
        $errors['symptomes'] = "Les symptômes sont obligatoires.";
    } elseif (mb_strlen($symptomes) > 1000) {
        $errors['symptomes'] = "Les symptômes ne doivent pas dépasser 1000 caractères.";
    }

    // Si aucune erreur => insertion
    if (empty($errors)) {
        try {
            $allergie = new Allergie(null, $nom, $description, $gravite, $symptomes);
            $controller = new AllergieC();
            $result = $controller->addAllergie($allergie);
            
            if ($result) {
                header("Location: allergies_list.php?success=added");
                exit();
            } else {
                $errors['general'] = "Erreur lors de l'ajout de l'allergie. Veuillez réessayer.";
            }
        } catch (Exception $e) {
            $errors['general'] = "Une erreur est survenue : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajouter Allergie</title>

    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; }
        .wrap { max-width: 640px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin: 0 0 8px; color: #0f172a; }
        .lead { color: #64748b; font-size: 0.95rem; margin: 0 0 20px; }
        
        label { display: block; font-size: 0.875rem; font-weight: 600; margin: 14px 0 6px; color: #334155; }

        input[type="text"], textarea, select {
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; 
            font-size: 1rem; transition: all 0.2s;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        textarea { min-height: 200px; resize: vertical; font-family: inherit; line-height: 1.5; }
        textarea#symptomes { min-height: 100px; }

        .btn {
            margin-top: 20px; padding: 10px 20px; border: none; border-radius: 8px;
            background: #2563eb; color: #fff; font-weight: 600; cursor: pointer; font-size: 1rem;
            transition: background 0.2s;
        }
        .btn:hover { background: #1d4ed8; }

        .btn-ghost {
            background: #e2e8f0; color: #0f172a; margin-left: 8px;
            text-decoration: none; display: inline-block; padding: 10px 20px;
            border-radius: 8px; font-weight: 600; font-size: 0.95rem;
            transition: background 0.2s;
        }
        .btn-ghost:hover { background: #cbd5e1; }

        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }

        .top { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .top a { color: #2563eb; font-size: 0.9rem; text-decoration: none; }
        .top a:hover { text-decoration: underline; }

        .err { color: #dc2626; font-size: 0.85rem; margin-top: 4px; }
        .alert { background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #991b1b; }
        
        .info-text { font-size: 0.75rem; color: #64748b; margin-top: 4px; }
        .required::after { content: " *"; color: #dc2626; }
        
        .badge-example {
            background: #f1f5f9; padding: 10px 12px; border-radius: 8px; font-size: 0.8rem; 
            margin-top: 10px; color: #475569; border-left: 3px solid #2563eb;
        }
        
        .gravite-badge {
            display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.7rem; font-weight: 600;
        }
        
        .btn-group { display: flex; gap: 12px; margin-top: 20px; }
    </style>
</head>

<body>
<div class="wrap">

    <div class="top">
        <a href="allergies_list.php">← Retour à la liste</a>
    </div>

    <h1>➕ Ajouter une allergie</h1>
    <p class="lead">Remplissez le formulaire ci-dessous pour ajouter une nouvelle allergie.</p>

    <div class="card">
        
        <?php if (isset($errors['general'])): ?>
            <div class="alert">❌ <?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>
        
        <form method="post" action="" novalidate>

            <label for="nom" class="required">Nom</label>
            <input type="text" id="nom" name="nom" autofocus
                   value="<?= htmlspecialchars($old['nom'], ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Ex: Arachides, Lactose, Pollen de bouleau...">
            <?php if (isset($errors['nom'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['nom']) ?></div>
            <?php endif; ?>

            <label for="description" class="required">Description</label>
            <textarea id="description" name="description" 
                      placeholder="Décrivez l'allergie en détail..."><?= htmlspecialchars($old['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php if (isset($errors['description'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['description']) ?></div>
            <?php endif; ?>

            <label for="gravite" class="required">Gravité</label>
            <select id="gravite" name="gravite">
                <option value="">-- Choisir la gravité --</option>
                <option value="faible" <?= ($old['gravite'] == "faible") ? "selected" : "" ?>>🟢 Faible</option>
                <option value="moyenne" <?= ($old['gravite'] == "moyenne") ? "selected" : "" ?>>🟠 Moyenne</option>
                <option value="grave" <?= ($old['gravite'] == "grave") ? "selected" : "" ?>>🔴 Grave</option>
            </select>
            <?php if (isset($errors['gravite'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['gravite']) ?></div>
            <?php endif; ?>

            <label for="symptomes" class="required">Symptômes</label>
            <textarea id="symptomes" name="symptomes" rows="4"
                      placeholder="Ex: Urticaire, gonflement, difficultés respiratoires, nausées..."><?= htmlspecialchars($old['symptomes'], ENT_QUOTES, 'UTF-8') ?></textarea>
            <div class="info-text">💡 Séparez les symptômes par des virgules pour une meilleure lisibilité</div>
            <?php if (isset($errors['symptomes'])): ?>
                <div class="err">⚠️ <?= htmlspecialchars($errors['symptomes']) ?></div>
            <?php endif; ?>
            
            <div class="badge-example">
                <strong>📋 Exemples de symptômes :</strong><br>
                • Allergies alimentaires : Urticaire, gonflement des lèvres, difficultés respiratoires, vomissements<br>
                • Allergies respiratoires : Éternuements, nez qui coule, yeux qui piquent, asthme<br>
                • Allergies médicamenteuses : Éruptions cutanées, fièvre, choc anaphylactique
            </div>

            <div class="btn-group">
                <button type="submit" class="btn">✅ Ajouter l'allergie</button>
                <a href="allergies_list.php" class="btn-ghost">❌ Annuler</a>
            </div>

        </form>
    </div>

</div>
</body>
</html>