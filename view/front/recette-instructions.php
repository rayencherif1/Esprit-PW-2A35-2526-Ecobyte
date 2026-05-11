<?php
declare(strict_types=1);
require_once __DIR__ . '/../../controller/RecetteController.php';
require_once __DIR__ . '/../../controller/InstructionController.php';

$recetteId = isset($_GET['recette_id']) ? (int)$_GET['recette_id'] : 0;
$recetteCtl = new RecetteController();
$instructionCtl = new InstructionController();

$recette = $recetteId > 0 ? $recetteCtl->getRecetteById($recetteId) : null;
$instruction = $recette ? $instructionCtl->getByRecetteId($recetteId) : null;

if ($recette && !$instruction) {
    // Basic sync if missing
    $instructionCtl->syncFromRecette($recette);
    $instruction = $instructionCtl->getByRecetteId($recetteId);
}

// Logic for Carbon Footprint (Eco-Score)
function getEcoGrade(string $impact): string {
    $impact = strtolower(trim($impact));
    if (str_contains($impact, 'faible') || str_contains($impact, 'bas')) return 'A';
    if (str_contains($impact, 'moyen')) return 'C';
    if (str_contains($impact, 'élevé') || str_contains($impact, 'haut')) return 'E';
    return 'B';
}

$grade = $recette ? getEcoGrade($recette['impactCarbone'] ?? '') : 'B';
$gradeColor = match($grade) {
    'A' => '#2ecc71',
    'B' => '#8bc34a',
    'C' => '#f1c40f',
    'D' => '#e67e22',
    'E' => '#e74c3c',
    default => '#95a5a6'
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $recette ? htmlspecialchars($recette['nom']) : 'Instructions' ?> — EcoByte</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --eco-green: #4caf50;
            --eco-orange: #ff6b35;
            --eco-dark: #1a1a2e;
            --eco-indigo: #5e72e4;
        }
        body { font-family: 'Open Sans', sans-serif; background: #f4f6f9; color: #1e293b; }
        
        /* ── TOPBAR ── */
        .ecobyte-topbar {
            background: var(--eco-dark); padding: 10px 32px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 12px rgba(0,0,0,0.3);
        }
        .eco-logo { display: flex; align-items: center; gap: 8px; font-family: 'Nunito', sans-serif; font-size: 1.2rem; font-weight: 800; text-decoration: none; }
        .eco-logo .eco { color: var(--eco-green); }
        .eco-logo .byte { color: var(--eco-orange); }
        .topbar-right { display: flex; align-items: center; gap: 14px; }
        .hub-link { color: #aaa; text-decoration: none; font-size: .82rem; }
        .hub-link:hover { color: #fff; }

        /* ── HEADER ── */
        .site-header { background: #fff; border-bottom: 1px solid #e9ecef; padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; }
        .btn-back { display: flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 50px; font-size: .85rem; font-weight: 600; text-decoration: none; background: #f1f5f9; color: #1e293b; border: 1.5px solid #e2e8f0; }

        /* ── HERO ── */
        .recipe-hero { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #fff; padding: 60px 32px; position: relative; }
        .recipe-hero h1 { font-family: 'Nunito', sans-serif; font-size: 2.8rem; font-weight: 800; margin-bottom: 20px; }
        .recipe-meta { display: flex; gap: 24px; flex-wrap: wrap; }
        .meta-item { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 12px; font-size: .9rem; }
        .meta-item i { color: var(--eco-green); }

        /* ── CONTENT ── */
        .main-content { max-width: 1000px; margin: -40px auto 60px; padding: 0 24px; position: relative; z-index: 10; }
        .card-recipe { background: #fff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); padding: 40px; }
        .eco-score { display: flex; align-items: center; gap: 12px; margin-bottom: 30px; padding: 15px; background: #f8fafc; border-radius: 16px; border-left: 5px solid <?= $gradeColor ?>; }
        .grade-badge { width: 40px; height: 40px; border-radius: 8px; background: <?= $gradeColor ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.4rem; }
        
        .section-title { font-family: 'Nunito', sans-serif; font-size: 1.3rem; font-weight: 800; margin: 30px 0 15px; display: flex; align-items: center; gap: 10px; }
        .section-title i { color: var(--eco-indigo); }
        
        .ingredients-list { columns: 2; column-gap: 30px; list-style: none; padding: 0; }
        .ingredients-list li { padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: .95rem; display: flex; align-items: center; gap: 10px; }
        .ingredients-list li::before { content: '•'; color: var(--eco-green); font-weight: 800; }

        .prep-steps { list-style: none; padding: 0; }
        .prep-steps li { margin-bottom: 20px; padding-left: 50px; position: relative; line-height: 1.6; }
        .prep-steps li .step-num { position: absolute; left: 0; top: 0; width: 34px; height: 34px; background: var(--eco-indigo); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .9rem; }

        .recipe-image-large { width: 100%; border-radius: 20px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        
        .site-footer { background: var(--eco-dark); color: #64748b; text-align: center; padding: 24px; font-size: .85rem; margin-top: 60px; }
    </style>
</head>
<body>

<nav class="ecobyte-topbar">
    <a href="/2int/index.php" class="eco-logo">
        <span>🌿</span> <span class="eco">ECO</span><span class="byte">BYTE</span>
    </a>
    <span class="module-badge">👨‍🍳 Mode Cuisine</span>
    <div class="topbar-right">
        <a href="/2int/index.php" class="hub-link">← Hub</a>
        <a href="#" class="avatar" style="width:32px;height:32px;background:#555;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:.8rem;">U</a>
    </div>
</nav>

<?php if ($recette): ?>
    <header class="site-header">
        <div class="brand"><h1><?= htmlspecialchars($recette['nom']) ?></h1></div>
        <div class="header-actions">
            <a href="/2int/view/front/front.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </header>

    <section class="recipe-hero">
        <div class="container">
            <h1><?= htmlspecialchars($recette['nom']) ?></h1>
            <div class="recipe-meta">
                <div class="meta-item"><i class="fas fa-clock"></i> <?= (int)$recette['tempsPreparation'] ?> min</div>
                <div class="meta-item"><i class="fas fa-fire"></i> <?= (int)$recette['calories'] ?> kcal</div>
                <div class="meta-item"><i class="fas fa-signal"></i> <?= htmlspecialchars($recette['difficulte']) ?></div>
                <div class="meta-item"><i class="fas fa-leaf"></i> Eco-Impact: <?= htmlspecialchars($recette['impactCarbone']) ?></div>
            </div>
        </div>
    </section>

    <main class="main-content">
        <div class="card-recipe">
            <?php if ($recette['image']): ?>
                <img src="<?= htmlspecialchars($recette['image']) ?>" class="recipe-image-large" alt="<?= htmlspecialchars($recette['nom']) ?>">
            <?php endif; ?>

            <div class="eco-score">
                <div class="grade-badge"><?= $grade ?></div>
                <div>
                    <strong>Eco-Score Nutritionnel</strong><br>
                    <small class="text-muted">Impact carbone évalué sur la base des ingrédients principaux.</small>
                </div>
            </div>

            <h2 class="section-title"><i class="fas fa-shopping-basket"></i> Ingrédients</h2>
            <ul class="ingredients-list">
                <?php 
                $ingStr = $instruction['ingredients'] ?? '';
                $ings = array_filter(array_map('trim', explode("\n", $ingStr)));
                if (empty($ings)) echo "<li>À compléter...</li>";
                foreach ($ings as $ing): ?>
                    <li><?= htmlspecialchars($ing) ?></li>
                <?php endforeach; ?>
            </ul>

            <h2 class="section-title"><i class="fas fa-utensils"></i> Préparation</h2>
            <ol class="prep-steps">
                <?php 
                $prepStr = $instruction['preparation'] ?? '';
                $steps = array_filter(array_map('trim', explode("\n", $prepStr)));
                if (empty($steps)) echo "<li>À compléter...</li>";
                foreach ($steps as $i => $step): ?>
                    <li>
                        <span class="step-num"><?= $i + 1 ?></span>
                        <?= htmlspecialchars($step) ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </main>

<?php else: ?>
    <div class="container py-5 text-center">
        <h1 class="display-4">Recette introuvable</h1>
        <p class="lead">La recette que vous cherchez n'existe pas ou a été supprimée.</p>
        <a href="/2int/view/front/front.php" class="btn btn-primary rounded-pill px-4">Retour aux recettes</a>
    </div>
<?php endif; ?>

<footer class="site-footer">
    <p>© 2026 EcoByte — Cuisine & Recettes | Esprit School Project</p>
</footer>

</body>
</html>
