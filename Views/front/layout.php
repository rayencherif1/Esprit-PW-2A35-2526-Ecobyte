<?php
/**
 * Layout front-office — Fitness & Sport (selem)
 * Header simplifié : Logo EcoByte + icône user
 * Navigation en cards/boxes
 */
$pageTitle = $pageTitle ?? 'Nutrition & Santé';
$fm = rtrim(URL_FOODMART, '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle) ?> — EcoByte</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="<?= e($fm) ?>/css/vendor.css" />
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            margin: 0;
        }

        /* ── HEADER ─────────────────────────────────────────────── */
        .eco-header {
            background: #fff;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            position: sticky;
            top: 0;
            z-index: 200;
        }
        .eco-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .eco-logo-icon { font-size: 1.8rem; }
        .eco-logo-text {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
        }
        .eco-logo-text .eco  { color: #4caf50; }
        .eco-logo-text .byte { color: #ff6b35; }
        .eco-logo-sub {
            font-size: 0.65rem;
            color: #9ca3af;
            font-weight: 500;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .header-right { display: flex; align-items: center; gap: 12px; }
        .hub-link {
            font-size: 0.8rem;
            color: #6b7280;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            background: #f3f4f6;
            transition: background .2s;
        }
        .hub-link:hover { background: #e5e7eb; color: #374151; }
        .user-avatar {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, #4caf50, #2196f3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 0.9rem;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(76,175,80,0.3);
        }

        /* ── MODULE BANNER ───────────────────────────────────────── */
        .module-banner {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
            color: white;
            padding: 32px 32px 24px;
            position: relative;
            overflow: hidden;
        }
        .module-banner::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(circle at 80% 50%, rgba(255,107,53,0.2) 0%, transparent 60%);
        }
        .module-banner-content {
            position: relative; z-index: 1;
            display: flex; align-items: center; gap: 16px;
        }
        .module-badge {
            background: rgba(255,107,53,0.2);
            border: 1px solid rgba(255,107,53,0.4);
            color: #ff8c42;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .module-banner h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        .module-banner p {
            margin: 0;
            opacity: 0.7;
            font-size: 0.875rem;
        }

        /* ── NAV CARDS ───────────────────────────────────────────── */
        .nav-cards-bar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 32px;
        }
        .nav-cards {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .nav-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }
        .nav-card-icon {
            font-size: 1.2rem;
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px;
        }
        .nav-card.active {
            background: linear-gradient(135deg, #ff6b35, #ff8c42);
            color: white;
            box-shadow: 0 4px 15px rgba(255,107,53,0.3);
        }
        .nav-card.active .nav-card-icon { background: rgba(255,255,255,0.2); }

        .nav-card.green  { background:#f0fdf4; color:#16a34a; border-color:#bbf7d0; }
        .nav-card.green:hover { background:#dcfce7; }
        .nav-card.green .nav-card-icon { background:#dcfce7; }

        .nav-card.blue   { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
        .nav-card.blue:hover { background:#dbeafe; }
        .nav-card.blue .nav-card-icon { background:#dbeafe; }

        .nav-card.purple { background:#faf5ff; color:#7c3aed; border-color:#ddd6fe; }
        .nav-card.purple:hover { background:#ede9fe; }
        .nav-card.purple .nav-card-icon { background:#ede9fe; }

        /* ── MAIN CONTENT ────────────────────────────────────────── */
        .eco-main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 24px 60px;
        }

        /* ── Bootstrap overrides ─────────────────────────────────── */
        .btn-nf {
            background: linear-gradient(135deg, #ff6b35, #ff8c42);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all .2s;
        }
        .btn-nf:hover {
            background: linear-gradient(135deg, #e55a25, #ff7a30);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255,107,53,0.35);
        }
        .nf-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .nf-card:hover {
            box-shadow: 0 6px 24px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            transition: all .25s;
        }

        /* ── FOOTER ──────────────────────────────────────────────── */
        .eco-footer {
            background: #1a1a2e;
            color: #6b7280;
            text-align: center;
            padding: 20px;
            font-size: 0.8rem;
        }
        .eco-footer a { color: #9ca3af; text-decoration: none; }
        .eco-footer a:hover { color: #fff; }
    </style>
</head>
<body>

<!-- ── HEADER ────────────────────────────────────────────────────── -->
<header class="eco-header">
    <a href="/2int/index.php" class="eco-logo">
        <span class="eco-logo-icon">🌿</span>
        <div>
            <div class="eco-logo-text">
                <span class="eco">ECO</span><span class="byte">BYTE</span>
            </div>
            <div class="eco-logo-sub">Fitness & Sport</div>
        </div>
    </a>
    <div class="header-right">
        <a href="/2int/index.php" class="hub-link">
            🏠 Accueil Hub
        </a>
        <div class="user-avatar" title="Utilisateur">U</div>
    </div>
</header>

<!-- ── MODULE BANNER ─────────────────────────────────────────────── -->
<div class="module-banner">
    <div class="module-banner-content">
        <div>
            <span class="module-badge">🏋️ Module Selem</span>
            <h2 style="margin-top:8px;">Fitness & Sport</h2>
            <p>Programmes d'entraînement personnalisés et suivi d'exercices quotidiens.</p>
        </div>
    </div>
</div>

<!-- ── NAV CARDS ─────────────────────────────────────────────────── -->
<div class="nav-cards-bar">
    <div class="nav-cards">
        <a href="<?= e(BASE_URL) ?>/index.php?action=home" class="nav-card active">
            <span class="nav-card-icon">📋</span>
            Mes Programmes
        </a>
        <a href="<?= e(BASE_URL) ?>/index.php?action=front_program_new" class="nav-card green">
            <span class="nav-card-icon">➕</span>
            Créer un Programme
        </a>
        <a href="<?= e(BASE_URL) ?>/index.php?action=user_program_list" class="nav-card blue">
            <span class="nav-card-icon">📅</span>
            Catalogue Public
        </a>
        <a href="<?= e(BASE_URL) ?>/index.php?action=recommend_ai" class="nav-card purple">
            <span class="nav-card-icon">🤖</span>
            Suggestion IA
        </a>
    </div>
</div>

<!-- ── MAIN CONTENT ───────────────────────────────────────────────── -->
<div class="eco-main">
    <?= $slot ?? '' ?>
</div>

<!-- ── FOOTER ─────────────────────────────────────────────────────── -->
<footer class="eco-footer">
    <p>© <?= date('Y') ?> EcoByte — Esprit School Project • Groupe 2A35 •
        <a href="/2int/index.php">← Retour au Hub</a>
    </p>
</footer>

<script src="<?= e($fm) ?>/js/jquery-1.11.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<?= $footerScripts ?? '' ?>
</body>
</html>
