<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoByte — Plateforme Nutrition & Santé</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: #f0f4f8; min-height: 100vh; }

        /* ── HEADER ──────────────────────────────────────────────── */
        header {
            background: #fff;
            padding: 16px 40px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            position: sticky; top: 0; z-index: 100;
        }
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; font-size: 1.5rem; font-weight: 800; color: #1a1a2e; }
        .logo-icon { font-size: 1.8rem; }
        .logo span.eco  { color: #4caf50; }
        .logo span.byte { color: #ff6b35; }
        .header-right { display: flex; align-items: center; gap: 16px; }
        /* USER → /2int/auth/profile.php (rayen) */
        .avatar {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #4caf50, #2196f3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 1rem;
            text-decoration: none; cursor: pointer;
            box-shadow: 0 2px 8px rgba(33,150,243,0.3);
            transition: transform .2s, box-shadow .2s;
        }
        .avatar:hover { transform: scale(1.1); box-shadow: 0 4px 16px rgba(33,150,243,0.4); }

        /* ── HERO ────────────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: white; text-align: center; padding: 60px 20px 80px;
            position: relative; overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 30% 50%, rgba(76,175,80,0.15) 0%, transparent 60%),
                        radial-gradient(circle at 70% 50%, rgba(255,107,53,0.15) 0%, transparent 60%);
        }
        .hero-content { position: relative; z-index: 1; }
        .hero h1 { font-size: 2.8rem; font-weight: 800; margin-bottom: 14px; line-height: 1.2; }
        .hero h1 .accent { color: #4caf50; }
        .hero p { font-size: 1.1rem; opacity: 0.8; max-width: 560px; margin: 0 auto; line-height: 1.6; }

        /* ── MODULE GRID ─────────────────────────────────────────── */
        .modules-section { max-width: 1100px; margin: -40px auto 60px; padding: 0 24px; }
        .modules-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

        .module-card {
            border-radius: 20px; padding: 36px 28px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative; overflow: hidden;
            border: 2px solid transparent;
        }
        .module-card:hover { transform: translateY(-6px); box-shadow: 0 14px 40px rgba(0,0,0,0.13); }

        /* ── COULEURS PAR MODULE ─────────────────────────────────── */

        /* 1. Cuisine — Vert sauge */
        #card-cuisine {
            background: linear-gradient(145deg, #f0fdf4, #dcfce7);
            border-color: #86efac;
        }
        #card-cuisine .module-icon-wrap { background: #bbf7d0; }
        #card-cuisine h3 { color: #15803d; }
        #btn-cuisine { background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 4px 14px rgba(34,197,94,0.35); }
        #btn-cuisine:hover { box-shadow: 0 6px 22px rgba(34,197,94,0.5); }

        /* 2. Fitness — Orange (ACTIF) */
        #card-fitness {
            background: #fff;
            border-color: #ff6b35;
            box-shadow: 0 4px 24px rgba(255,107,53,0.18);
        }
        #card-fitness .module-icon-wrap { background: #fff3ee; }
        #card-fitness h3 { color: #c2410c; }
        #btn-fitness { background: linear-gradient(135deg, #ff6b35, #ff8c42); box-shadow: 0 4px 14px rgba(255,107,53,0.4); }
        #btn-fitness:hover { box-shadow: 0 6px 22px rgba(255,107,53,0.55); }

        /* 3. Santé — Rouge corail */
        #card-sante {
            background: linear-gradient(145deg, #fff1f2, #ffe4e6);
            border-color: #fca5a5;
        }
        #card-sante .module-icon-wrap { background: #fecdd3; }
        #card-sante h3 { color: #be123c; }
        #btn-sante { background: linear-gradient(135deg, #f43f5e, #e11d48); box-shadow: 0 4px 14px rgba(244,63,94,0.3); }
        #btn-sante:hover { box-shadow: 0 6px 22px rgba(244,63,94,0.45); }

        /* 4. Boutique — Bleu indigo */
        #card-boutique {
            background: linear-gradient(145deg, #eff6ff, #dbeafe);
            border-color: #93c5fd;
        }
        #card-boutique .module-icon-wrap { background: #bfdbfe; }
        #card-boutique h3 { color: #1d4ed8; }
        #btn-boutique { background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 4px 14px rgba(59,130,246,0.3); }
        #btn-boutique:hover { box-shadow: 0 6px 22px rgba(59,130,246,0.45); }

        /* 5. Blog — Cyan / teal */
        #card-blog {
            background: linear-gradient(145deg, #ecfeff, #cffafe);
            border-color: #67e8f9;
        }
        #card-blog .module-icon-wrap { background: #a5f3fc; }
        #card-blog h3 { color: #0e7490; }
        #btn-blog { background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 4px 14px rgba(6,182,212,0.3); }
        #btn-blog:hover { box-shadow: 0 6px 22px rgba(6,182,212,0.45); }

        /* 6. IA — Violet */
        #card-ia {
            background: linear-gradient(145deg, #faf5ff, #ede9fe);
            border-color: #c4b5fd;
        }
        #card-ia .module-icon-wrap { background: #ddd6fe; }
        #card-ia h3 { color: #6d28d9; }
        #btn-ia { background: linear-gradient(135deg, #8b5cf6, #7c3aed); box-shadow: 0 4px 14px rgba(139,92,246,0.3); }
        #btn-ia:hover { box-shadow: 0 6px 22px rgba(139,92,246,0.45); }

        /* ── BADGE DISPONIBLE (selem uniquement) ─────────────────── */
        .badge-active {
            position: absolute; top: 14px; right: 14px;
            background: #4caf50; color: white;
            font-size: 0.62rem; font-weight: 700;
            padding: 3px 10px; border-radius: 999px;
            text-transform: uppercase; letter-spacing: .05em;
        }

        /* ── ICON WRAP ───────────────────────────────────────────── */
        .module-icon-wrap {
            width: 72px; height: 72px; border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem; margin: 0 auto 20px;
        }
        .module-card h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 10px; }
        .module-card p { font-size: 0.875rem; color: #6b7280; line-height: 1.6; margin-bottom: 24px; }

        /* ── BUTTONS ─────────────────────────────────────────────── */
        .btn-module {
            display: inline-block; padding: 12px 28px; border-radius: 50px;
            font-size: 0.9rem; font-weight: 600; text-decoration: none;
            color: white; width: 100%; text-align: center;
            transition: all 0.25s ease; cursor: pointer;
        }
        .btn-module:hover { transform: scale(1.03); color: white; }

        /* ── STATUS BAR ──────────────────────────────────────────── */
        .status-bar { background: #1a1a2e; color: #aaa; text-align: center; padding: 20px; font-size: 0.8rem; }
        .status-bar .modules-status { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-bottom: 10px; }
        .status-item { display: flex; align-items: center; gap: 6px; }
        .dot { width: 8px; height: 8px; border-radius: 50%; }
        .dot-green  { background: #4caf50; }
        .dot-yellow { background: #22c55e; animation: pulse 2s infinite; }
        .dot-red    { background: #f43f5e; }
        .dot-blue   { background: #3b82f6; }
        .dot-cyan   { background: #06b6d4; }
        .dot-purple { background: #8b5cf6; }
        .dot-orange { background: #ff6b35; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

        @media (max-width:900px) { .modules-grid { grid-template-columns: repeat(2,1fr); } .hero h1 { font-size: 2rem; } }
        @media (max-width:600px) { .modules-grid { grid-template-columns: 1fr; } header { padding: 14px 20px; } }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <a href="/2int/index.php" class="logo">
        <span class="logo-icon">🌿</span>
        <span class="eco">ECO</span><span class="byte">BYTE</span>
    </a>
    <div class="header-right">
        <!-- USER → /2int/auth/profile.php (activer avec branche rayen) -->
        <a href="#" class="avatar" id="btn-user" title="Mon compte">U</a>
    </div>
</header>

<!-- HERO -->
<div class="hero">
    <div class="hero-content">
        <h1>Bienvenue sur <span class="accent">EcoByte</span> 🌱</h1>
        <p>Votre plateforme tout-en-un pour la nutrition, la santé, le sport et bien plus.</p>
    </div>
</div>

<!-- MODULES -->
<div class="modules-section">
    <div class="modules-grid">

        <!-- 1. CUISINE — Vert | branche: mohamed | URL: /2int/cuisine/index.php -->
        <div class="module-card" id="card-cuisine">
            <div class="module-icon-wrap">🥗</div>
            <h3>Cuisine & Recettes</h3>
            <p>Découvrez des centaines de recettes saines adaptées à vos besoins nutritionnels.</p>
            <a href="#" class="btn-module" id="btn-cuisine">Recettes</a>
        </div>

        <!-- 2. FITNESS — Orange ✅ ACTIF | branche: selem | URL: /2int/public/index.php?action=home -->
        <div class="module-card" id="card-fitness">
            <div class="module-icon-wrap">🏋️</div>
            <h3>Fitness & Sport</h3>
            <p>Programmes d'entraînement personnalisés et suivi d'exercices quotidiens.</p>
            <a href="/2int/public/index.php?action=home" class="btn-module" id="btn-fitness">Catalogue Sport →</a>
        </div>

        <!-- 3. SANTÉ — Rouge | branche: ilyess | URL: /2int/sante/index.php -->
        <div class="module-card" id="card-sante">
            <div class="module-icon-wrap">⚠️</div>
            <h3>Santé & Allergies</h3>
            <p>Gérez vos allergies, analysez vos aliments avec l'IA et restez en sécurité.</p>
            <a href="#" class="btn-module" id="btn-sante">Rapports d'Allergies</a>
        </div>

        <!-- 4. BOUTIQUE — Bleu ✅ ACTIF | branche: ilyess | URL: /2int/boutique.php -->
        <div class="module-card" id="card-boutique">
            <div class="module-icon-wrap">🛒</div>
            <h3>Boutique Bio</h3>
            <p>Achetez des produits frais, bio et sains directement depuis notre plateforme.</p>
            <a href="/2int/boutique.php" class="btn-module" id="btn-boutique">Boutique →</a>
        </div>

        <!-- 5. BLOG — Cyan | branche: blog | URL: /2int/blog/index.php -->
        <div class="module-card" id="card-blog">
            <div class="module-icon-wrap">📝</div>
            <h3>Blog & Actu</h3>
            <p>Lisez les derniers articles sur la nutrition et partagez avec la communauté.</p>
            <a href="#" class="btn-module" id="btn-blog">Blog</a>
        </div>

        <!-- 6. IA — Violet | branche: rayen | URL: /2int/ia/index.php -->
        <div class="module-card" id="card-ia">
            <div class="module-icon-wrap">🤖</div>
            <h3>IA Assistant</h3>
            <p>Posez vos questions à notre IA pour obtenir des conseils nutritionnels instantanés.</p>
            <a href="#" class="btn-module" id="btn-ia">Discuter avec l'IA</a>
        </div>

    </div>
</div>

<!-- STATUS BAR -->
<div class="status-bar">
    <div class="modules-status">
        <div class="status-item"><div class="dot dot-orange"></div> Fitness & Sport — En ligne</div>
        <div class="status-item"><div class="dot dot-green"></div> Cuisine & Recettes — En intégration</div>
        <div class="status-item"><div class="dot dot-red"></div> Santé & Allergies — En intégration</div>
        <div class="status-item"><div class="dot dot-blue"></div> Boutique Bio — En ligne</div>
        <div class="status-item"><div class="dot dot-cyan"></div> Blog & Actu — En intégration</div>
        <div class="status-item"><div class="dot dot-purple"></div> IA Assistant — En intégration</div>
    </div>
    <p>© <?php echo date('Y'); ?> EcoByte — Esprit School Project • Groupe 2A35</p>
</div>

</body>
</html>
