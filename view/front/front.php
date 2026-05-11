<?php
declare(strict_types=1);
require_once __DIR__ . '/../../controller/RecetteController.php';

$controller = new RecetteController();
$recettes   = $controller->afficherRecettes();

// Simple local search/filter — no external API, instant load
$search = trim($_GET['search'] ?? '');
$type   = trim($_GET['type'] ?? '');

if ($search !== '') {
    $recettes = array_values(array_filter($recettes, fn($r) =>
        stripos($r['nom'] ?? '', $search) !== false ||
        stripos($r['type'] ?? '', $search) !== false
    ));
}
if ($type !== '') {
    $recettes = array_values(array_filter($recettes, fn($r) =>
        strcasecmp($r['type'] ?? '', $type) === 0
    ));
}

// Get unique types for filter dropdown
$allTypes = array_unique(array_filter(array_column(
    $controller->afficherRecettes(), 'type'
)));
sort($allTypes);

// Difficulty badge color
function getDiffColor(string $diff): string {
    return match(strtolower(trim($diff))) {
        'facile'  => '#2ecc71',
        'moyen', 'moyenne' => '#f39c12',
        'difficile', 'élevée', 'elevee' => '#e74c3c',
        default   => '#95a5a6',
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuisine & Recettes — EcoByte</title>
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
        * { box-sizing: border-box; }
        body { font-family: 'Open Sans', sans-serif; background: #f4f6f9; margin: 0; }

        /* ═══ DARK TOPBAR (same as ilyess) ═══════════════════════════ */
        .ecobyte-topbar {
            background: var(--eco-dark);
            padding: 10px 32px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 2px 12px rgba(0,0,0,0.35);
        }
        .ecobyte-topbar .eco-logo {
            display: flex; align-items: center; gap: 8px;
            font-family: 'Nunito', sans-serif;
            font-size: 1.2rem; font-weight: 800; text-decoration: none;
        }
        .ecobyte-topbar .eco-logo .eco  { color: var(--eco-green); }
        .ecobyte-topbar .eco-logo .byte { color: var(--eco-orange); }
        .module-badge {
            background: rgba(94,114,228,.25);
            border: 1px solid rgba(94,114,228,.5);
            color: #a5b4fc; padding: 4px 14px;
            border-radius: 999px; font-size: .72rem; font-weight: 700; letter-spacing: .05em;
        }
        .topbar-right { display: flex; align-items: center; gap: 14px; }
        .hub-link {
            color: #aaa; text-decoration: none; font-size: .82rem; font-weight: 500;
            display: flex; align-items: center; gap: 5px; transition: color .2s;
        }
        .hub-link:hover { color: #fff; }
        .user-avatar {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #4caf50, #2196f3);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: .9rem;
            text-decoration: none; transition: transform .2s;
        }
        .user-avatar:hover { transform: scale(1.1); }

        /* ═══ WHITE HEADER (search bar) ════════════════════════════════ */
        .site-header {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 16px 32px;
            display: flex; align-items: center; justify-content: space-between; gap: 20px;
            flex-wrap: wrap;
        }
        .site-header .brand img { height: 44px; }
        .search-form {
            display: flex; align-items: center; gap: 0;
            background: #f8f9fa; border-radius: 50px;
            border: 2px solid #e0e0e0; overflow: hidden;
            flex: 1; max-width: 560px; transition: border-color .2s;
        }
        .search-form:focus-within { border-color: var(--eco-indigo); }
        .search-form select {
            border: none; background: transparent; padding: 10px 14px;
            font-size: .85rem; cursor: pointer; outline: none;
            border-right: 1px solid #e0e0e0;
        }
        .search-form input {
            border: none; background: transparent; padding: 10px 16px;
            font-size: .9rem; flex: 1; outline: none;
        }
        .search-form button {
            background: var(--eco-indigo); border: none; color: #fff;
            padding: 10px 20px; cursor: pointer; font-size: .9rem;
            transition: background .2s;
        }
        .search-form button:hover { background: #4a5fc4; }
        .header-actions { display: flex; align-items: center; gap: 14px; }
        .header-actions a {
            display: flex; align-items: center; gap: 6px;
            padding: 9px 18px; border-radius: 50px;
            font-size: .85rem; font-weight: 600; text-decoration: none;
            transition: all .2s;
        }
        .btn-admin {
            background: linear-gradient(135deg, var(--eco-indigo), #4a5fc4);
            color: #fff;
        }
        .btn-admin:hover { opacity: .9; color: #fff; transform: translateY(-1px); }
        .btn-favs {
            background: #fff0f0; color: #e53e3e; border: 1.5px solid #fed7d7;
        }
        .btn-favs:hover { background: #fed7d7; color: #c53030; }

        /* ═══ HERO BANNER ═══════════════════════════════════════════════ */
        .hero-section {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
            padding: 60px 32px;
            display: grid; grid-template-columns: 1fr 340px;
            gap: 40px; align-items: center;
        }
        .hero-content .label {
            color: var(--eco-green); font-size: .8rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .15em; margin-bottom: 12px;
        }
        .hero-content h1 {
            color: #fff; font-family: 'Nunito', sans-serif;
            font-size: 2.6rem; font-weight: 800; line-height: 1.2; margin-bottom: 16px;
        }
        .hero-content h1 span { color: var(--eco-green); }
        .hero-content p { color: #94a3b8; font-size: 1rem; line-height: 1.7; margin-bottom: 28px; }
        .hero-content .stats { display: flex; gap: 32px; margin-bottom: 28px; }
        .hero-content .stat strong {
            display: block; color: #fff;
            font-size: 1.6rem; font-weight: 800; font-family: 'Nunito', sans-serif;
        }
        .hero-content .stat small { color: #64748b; font-size: .78rem; }
        .btn-hero {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--eco-green); color: #fff;
            padding: 14px 28px; border-radius: 50px;
            font-weight: 700; text-decoration: none; font-size: .95rem;
            transition: all .3s; box-shadow: 0 8px 20px rgba(76,175,80,.35);
        }
        .btn-hero:hover { background: #388e3c; color: #fff; transform: translateY(-2px); }
        .hero-card {
            background: rgba(255,255,255,.06); backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,.12); border-radius: 24px;
            padding: 28px; text-align: center;
        }
        .hero-card .emoji { font-size: 5rem; display: block; margin-bottom: 12px; }
        .hero-card p { color: #94a3b8; font-size: .85rem; line-height: 1.6; margin: 0; }

        /* ═══ RECIPE SECTION ════════════════════════════════════════════ */
        .recipes-section { padding: 48px 32px; }
        .section-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 32px; flex-wrap: wrap; gap: 16px;
        }
        .section-title {
            font-family: 'Nunito', sans-serif;
            font-size: 1.6rem; font-weight: 800; color: #1e293b; margin: 0;
        }
        .filter-pills { display: flex; gap: 8px; flex-wrap: wrap; }
        .pill {
            padding: 6px 16px; border-radius: 999px; font-size: .82rem; font-weight: 600;
            text-decoration: none; transition: all .2s;
            border: 1.5px solid #e0e0e0; color: #64748b; background: #fff;
        }
        .pill:hover, .pill.active {
            background: var(--eco-indigo); border-color: var(--eco-indigo); color: #fff;
        }

        /* ═══ RECIPE CARDS ══════════════════════════════════════════════ */
        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 24px;
        }
        .recipe-card {
            background: #fff; border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,.07);
            overflow: hidden; transition: all .35s cubic-bezier(.175,.885,.32,1.275);
            display: flex; flex-direction: column;
        }
        .recipe-card:hover { transform: translateY(-8px); box-shadow: 0 16px 40px rgba(0,0,0,.12); }
        .recipe-card .card-img {
            height: 180px; overflow: hidden; position: relative;
            background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
        }
        .recipe-card .card-img img {
            width: 100%; height: 100%; object-fit: cover; transition: transform .4s;
        }
        .recipe-card:hover .card-img img { transform: scale(1.06); }
        .recipe-card .card-img .no-img {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            font-size: 4rem;
        }
        .badge-type {
            position: absolute; top: 12px; left: 12px;
            background: rgba(94,114,228,.9); color: #fff;
            padding: 3px 10px; border-radius: 999px;
            font-size: .7rem; font-weight: 700; text-transform: capitalize;
            backdrop-filter: blur(4px);
        }
        .badge-diff {
            position: absolute; top: 12px; right: 12px;
            padding: 3px 10px; border-radius: 999px;
            font-size: .7rem; font-weight: 700; color: #fff;
            backdrop-filter: blur(4px);
        }
        .card-body { padding: 18px; flex: 1; display: flex; flex-direction: column; gap: 10px; }
        .card-body h3 {
            font-family: 'Nunito', sans-serif;
            font-size: 1rem; font-weight: 800; color: #1e293b; margin: 0;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .card-meta { display: flex; gap: 14px; flex-wrap: wrap; }
        .meta-item { display: flex; align-items: center; gap: 4px; font-size: .78rem; color: #64748b; }
        .meta-item i { color: var(--eco-indigo); }
        .card-footer-actions {
            margin-top: auto; display: flex; gap: 8px; padding-top: 10px;
            border-top: 1px solid #f1f5f9;
        }
        .btn-detail {
            flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 14px; border-radius: 50px;
            font-size: .82rem; font-weight: 700; text-decoration: none;
            background: linear-gradient(135deg, var(--eco-indigo), #4a5fc4); color: #fff;
            transition: all .2s; border: none; cursor: pointer;
        }
        .btn-detail:hover { opacity: .88; color: #fff; transform: translateY(-1px); }
        .btn-like {
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 1.5px solid #fecdd3; background: #fff0f0;
            cursor: pointer; transition: all .2s; color: #e53e3e; font-size: 1rem;
        }
        .btn-like:hover, .btn-like.active { background: #e53e3e; color: #fff; border-color: #e53e3e; }

        /* ═══ EMPTY STATE ═══════════════════════════════════════════════ */
        .empty-state {
            text-align: center; padding: 80px 20px; color: #94a3b8;
        }
        .empty-state .icon { font-size: 4rem; margin-bottom: 16px; }
        .empty-state h3 { color: #64748b; font-family: 'Nunito', sans-serif; }

        /* ═══ FOOTER ════════════════════════════════════════════════════ */
        .site-footer {
            background: var(--eco-dark); color: #64748b;
            text-align: center; padding: 24px;
            font-size: .85rem; margin-top: 60px;
        }
        .site-footer a { color: var(--eco-green); text-decoration: none; }

        @media (max-width: 768px) {
            .hero-section { grid-template-columns: 1fr; }
            .hero-card { display: none; }
            .hero-content h1 { font-size: 1.8rem; }
            .site-header { padding: 12px 16px; }
            .recipes-section { padding: 32px 16px; }
        }
    </style>
</head>
<body>

<!-- ═══ DARK ECOBYTE TOPBAR ══════════════════════════════════════════════ -->
<nav class="ecobyte-topbar">
    <a href="/2int/index.php" class="eco-logo">
        <span>🌿</span>
        <span class="eco">ECO</span><span class="byte">BYTE</span>
    </a>
    <span class="module-badge">🍲 Cuisine & Recettes</span>
    <div class="topbar-right">
        <a href="/2int/index.php" class="hub-link">← Hub</a>
        <a href="#" class="user-avatar" title="Mon compte">U</a>
    </div>
</nav>

<!-- ═══ WHITE HEADER WITH SEARCH ════════════════════════════════════════ -->
<header class="site-header">
    <div class="brand">
        <span style="font-family:'Nunito',sans-serif;font-size:1.5rem;font-weight:800;">
            <span style="color:var(--eco-green)">🥗</span>
            <span style="color:#1e293b">Eco</span><span style="color:var(--eco-orange)">Recettes</span>
        </span>
    </div>

    <form action="" method="GET" class="search-form">
        <select name="type">
            <option value="">Toutes catégories</option>
            <?php foreach ($allTypes as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= $type === $t ? 'selected' : '' ?>>
                    <?= htmlspecialchars(ucfirst($t)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="search"
               placeholder="Rechercher une recette..."
               value="<?= htmlspecialchars($search) ?>"
               autocomplete="off">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <div class="header-actions">
        <a href="/2int/view/front/favoris.php" class="btn-favs">
            <i class="fas fa-heart"></i> Favoris
        </a>
    </div>
</header>

<!-- ═══ HERO BANNER ══════════════════════════════════════════════════════ -->
<section class="hero-section">
    <div class="hero-content">
        <p class="label">🌱 Cuisine Saine & Naturelle</p>
        <h1>Découvrez nos<br><span>Recettes Healthy</span></h1>
        <p>Des recettes saines, savoureuses et adaptées à vos besoins nutritionnels. Calculez l'impact carbone de vos repas et cuisinez responsable.</p>
        <div class="stats">
            <div class="stat">
                <strong><?= count($controller->afficherRecettes()) ?></strong>
                <small>Recettes disponibles</small>
            </div>
            <div class="stat">
                <strong><?= count($allTypes) ?></strong>
                <small>Catégories</small>
            </div>
            <div class="stat">
                <strong>100%</strong>
                <small>Recettes vérifiées</small>
            </div>
        </div>
        <a href="#recettes" class="btn-hero">
            <i class="fas fa-utensils"></i> Voir les recettes
        </a>
    </div>
    <div class="hero-card">
        <span class="emoji">🥗</span>
        <p>Des recettes équilibrées avec instructions étape par étape</p>
    </div>
</section>

<!-- ═══ RECIPES SECTION ═══════════════════════════════════════════════════ -->
<section class="recipes-section" id="recettes">
    <div class="section-header">
        <h2 class="section-title">
            <?php if ($search !== ''): ?>
                Résultats pour "<em><?= htmlspecialchars($search) ?></em>"
                <small style="font-size:.9rem;color:#64748b;font-weight:400;margin-left:8px;">(<?= count($recettes) ?> recette<?= count($recettes) > 1 ? 's' : '' ?>)</small>
            <?php elseif ($type !== ''): ?>
                <?= htmlspecialchars(ucfirst($type)) ?>
            <?php else: ?>
                Toutes les recettes
            <?php endif; ?>
        </h2>

        <div class="filter-pills">
            <a href="?" class="pill <?= ($search === '' && $type === '') ? 'active' : '' ?>">Toutes</a>
            <?php foreach ($allTypes as $t): ?>
                <a href="?type=<?= urlencode($t) ?>" class="pill <?= $type === $t ? 'active' : '' ?>">
                    <?= htmlspecialchars(ucfirst($t)) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($recettes)): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <h3>Aucune recette trouvée</h3>
            <p>Essayez un autre terme de recherche.</p>
            <a href="?" class="btn-hero" style="display:inline-flex;margin-top:16px;">
                <i class="fas fa-arrow-left"></i> Voir toutes les recettes
            </a>
        </div>
    <?php else: ?>
        <div class="recipes-grid">
            <?php foreach ($recettes as $r): ?>
                <?php
                $nom      = htmlspecialchars($r['nom'] ?? 'Recette');
                $type_r   = htmlspecialchars($r['type'] ?? '');
                $cal      = (int)($r['calories'] ?? 0);
                $temps    = (int)($r['tempsPreparation'] ?? 0);
                $diff     = $r['difficulte'] ?? '';
                $impact   = $r['impactCarbone'] ?? '';
                $img      = $r['image'] ?? '';
                $rid      = (int)($r['id'] ?? 0);
                $diffColor = getDiffColor($diff);
                ?>
                <div class="recipe-card">
                    <div class="card-img">
                        <?php if ($img && file_exists(str_replace('/2int/', $_SERVER['DOCUMENT_ROOT'] . '/2int/', $img))): ?>
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= $nom ?>" loading="lazy">
                        <?php elseif ($img): ?>
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= $nom ?>" loading="lazy"
                                 onerror="this.parentElement.innerHTML='<div class=\'no-img\'>🥘</div>'">
                        <?php else: ?>
                            <div class="no-img">🥘</div>
                        <?php endif; ?>
                        <span class="badge-type"><?= $type_r ?></span>
                        <?php if ($diff): ?>
                            <span class="badge-diff" style="background:<?= $diffColor ?>aa;">
                                <?= htmlspecialchars(ucfirst($diff)) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3><?= $nom ?></h3>
                        <div class="card-meta">
                            <?php if ($cal > 0): ?>
                                <span class="meta-item"><i class="fas fa-fire"></i> <?= $cal ?> kcal</span>
                            <?php endif; ?>
                            <?php if ($temps > 0): ?>
                                <span class="meta-item"><i class="fas fa-clock"></i> <?= $temps ?> min</span>
                            <?php endif; ?>
                            <?php if ($impact): ?>
                                <span class="meta-item"><i class="fas fa-leaf"></i> <?= htmlspecialchars($impact) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer-actions">
                            <a href="/2int/view/front/recette-instructions.php?recette_id=<?= $rid ?>"
                               class="btn-detail">
                                <i class="fas fa-book-open"></i> Instructions
                            </a>
                            <button class="btn-like" title="Ajouter aux favoris"
                                    onclick="toggleFavori(<?= $rid ?>, this)">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ═══ FOOTER ════════════════════════════════════════════════════ -->
<footer class="site-footer">
    <p>© 2025 <a href="/2int/index.php">EcoByte</a> — Cuisine &amp; Recettes | Module Rayen</p>
</footer>

<script>
// Toggle favori (local, no API)
function toggleFavori(id, btn) {
    btn.classList.toggle('active');
    const key = 'fav_' + id;
    if (btn.classList.contains('active')) {
        localStorage.setItem(key, '1');
        btn.title = 'Retirer des favoris';
    } else {
        localStorage.removeItem(key);
        btn.title = 'Ajouter aux favoris';
    }
}
// Restore favori state from localStorage
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-like').forEach(function(btn) {
        const onclick = btn.getAttribute('onclick') || '';
        const match = onclick.match(/toggleFavori\((\d+)/);
        if (match) {
            const id = match[1];
            if (localStorage.getItem('fav_' + id)) {
                btn.classList.add('active');
            }
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
