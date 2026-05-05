<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/allergie.php';
require_once __DIR__ . '/../../Controller/allergie.Controller.php';
require_once __DIR__ . '/../../Controller/traitement.Controller.php';

$controller = new AllergieC();
$allergies = $controller->listAllergie();

if (!is_array($allergies)) {
    $allergies = [];
}

$selected_id = $_GET['id'] ?? null;
$allergie_detail = null;
$traitements = [];

if ($selected_id) {
    $allergie_detail = $controller->getAllergieById($selected_id);
    if ($allergie_detail) {
        $traitementController = new TraitementC();
        $traitements = $traitementController->listTraitementByAllergie($selected_id);
        if (!is_array($traitements)) {
            $traitements = [];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allergies - Guide complet</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * { font-family: 'Inter', sans-serif; }

        .card-allergy { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-allergy:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,.1); }

        .hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

        .modal { display: <?= $selected_id ? 'flex' : 'none' ?>; }

        .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .back-button { transition: all 0.3s ease; }
        .back-button:hover { transform: translateX(-5px); }

        /* ══════════════════════════════════════════
           LIEN AI — HEADER (coin droit)
        ══════════════════════════════════════════ */
        .ai-btn-header {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            border-radius: 14px;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 600;
            color: #fff;
            letter-spacing: 0.02em;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.25);
            backdrop-filter: blur(12px);
            background: rgba(0,0,0,0.25);
            transition: transform 0.25s cubic-bezier(.34,1.56,.64,1), box-shadow 0.25s ease, border-color 0.25s;
        }
        .ai-btn-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg,
                rgba(99,255,210,0.18) 0%,
                rgba(124,92,252,0.28) 50%,
                rgba(252,92,125,0.18) 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .ai-btn-header:hover::before { opacity: 1; }
        .ai-btn-header:hover {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 0 28px rgba(124,92,252,0.55), 0 0 8px rgba(42,250,223,0.3);
            border-color: rgba(124,92,252,0.7);
        }
        /* Particules orbitales */
        .ai-btn-header .orbit {
            position: absolute;
            width: 6px; height: 6px;
            border-radius: 50%;
            animation: orbit-spin 3s linear infinite;
            pointer-events: none;
        }
        .ai-btn-header .orbit-1 {
            background: #63ffd2;
            top: -3px; left: 50%;
            animation-duration: 2.8s;
            box-shadow: 0 0 6px #63ffd2;
        }
        .ai-btn-header .orbit-2 {
            background: #fc5c7d;
            bottom: -3px; right: 20%;
            animation-duration: 3.5s;
            animation-delay: -1.4s;
            box-shadow: 0 0 6px #fc5c7d;
        }
        .ai-btn-header .orbit-3 {
            background: #7c5cfc;
            top: 50%; right: -3px;
            animation-duration: 4s;
            animation-delay: -2s;
            box-shadow: 0 0 6px #7c5cfc;
        }
        @keyframes orbit-spin {
            0%   { transform: rotate(0deg)   translateX(34px) rotate(0deg); }
            100% { transform: rotate(360deg) translateX(34px) rotate(-360deg); }
        }
        /* Icône scan animée */
        .ai-btn-header .scan-icon {
            position: relative;
            width: 22px; height: 22px;
            flex-shrink: 0;
        }
        .ai-btn-header .scan-icon svg { width: 22px; height: 22px; }
        .ai-btn-header .scan-line {
            position: absolute;
            left: 2px; right: 2px;
            height: 1.5px;
            background: linear-gradient(90deg, transparent, #63ffd2, transparent);
            top: 0;
            animation: scan-move 1.8s ease-in-out infinite;
            border-radius: 2px;
        }
        @keyframes scan-move {
            0%   { top: 2px;  opacity: 0; }
            20%  { opacity: 1; }
            80%  { opacity: 1; }
            100% { top: 18px; opacity: 0; }
        }
        /* Texte shimmer */
        .ai-btn-header .btn-text {
            background: linear-gradient(90deg, #fff 0%, #63ffd2 40%, #fff 60%, #fc9fff 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: text-shimmer 4s linear infinite;
        }
        @keyframes text-shimmer {
            0%   { background-position: 200% center; }
            100% { background-position: -200% center; }
        }
        /* Flèche arrow bounce */
        .ai-btn-header .arrow { animation: arrow-pulse 1.5s ease-in-out infinite; }
        @keyframes arrow-pulse {
            0%, 100% { transform: translateX(0); opacity: 0.7; }
            50%       { transform: translateX(4px); opacity: 1; }
        }
        /* Badge AI pill */
        .ai-pill {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            background: rgba(124,92,252,0.9);
            color: #fff;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            padding: 2px 7px;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .ai-pill::before {
            content: '';
            width: 5px; height: 5px;
            border-radius: 50%;
            background: #63ffd2;
            animation: blink 1.2s ease-in-out infinite;
        }
        @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.2;} }

        /* ══════════════════════════════════════════
           BANNIÈRE AI — BAS DE PAGE
        ══════════════════════════════════════════ */
        .ai-banner {
            position: relative;
            display: block;
            margin: 0 auto;
            max-width: 680px;
            border-radius: 24px;
            overflow: hidden;
            text-decoration: none;
            padding: 2px;
            background: linear-gradient(135deg, #7c5cfc, #fc5c7d, #2afadf, #7c5cfc);
            background-size: 300% 300%;
            animation: border-flow 4s linear infinite;
            transition: transform 0.3s cubic-bezier(.34,1.56,.64,1), box-shadow 0.3s;
            box-shadow: 0 0 0 rgba(124,92,252,0);
        }
        .ai-banner:hover {
            transform: translateY(-4px) scale(1.015);
            box-shadow: 0 20px 60px rgba(124,92,252,0.45), 0 0 40px rgba(42,250,223,0.2);
        }
        @keyframes border-flow {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .ai-banner-inner {
            position: relative;
            background: #0d0d1a;
            border-radius: 22px;
            padding: 28px 36px;
            display: flex;
            align-items: center;
            gap: 28px;
            overflow: hidden;
        }
        /* Fond étoilé */
        .ai-banner-inner::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 50%, rgba(124,92,252,0.15) 0%, transparent 55%),
                radial-gradient(circle at 85% 50%, rgba(42,250,223,0.12) 0%, transparent 55%);
        }
        /* Grille de points */
        .ai-banner-inner::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        /* Icône centrale animée */
        .ai-banner-icon {
            position: relative;
            flex-shrink: 0;
            width: 64px; height: 64px;
            z-index: 2;
        }
        .ai-banner-icon .ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 1.5px solid;
            animation: ring-expand 2.5s ease-out infinite;
        }
        .ai-banner-icon .ring-1 { border-color: rgba(124,92,252,0.8); animation-delay: 0s; }
        .ai-banner-icon .ring-2 { border-color: rgba(42,250,223,0.6); animation-delay: 0.8s; }
        .ai-banner-icon .ring-3 { border-color: rgba(252,92,125,0.5); animation-delay: 1.6s; }
        @keyframes ring-expand {
            0%   { transform: scale(1);   opacity: 1; }
            100% { transform: scale(2.2); opacity: 0; }
        }
        .ai-banner-icon .core {
            position: absolute;
            inset: 10px;
            background: linear-gradient(135deg, #7c5cfc, #2afadf);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            animation: core-pulse 2s ease-in-out infinite;
        }
        @keyframes core-pulse {
            0%,100% { box-shadow: 0 0 0 0 rgba(124,92,252,0.4); }
            50%      { box-shadow: 0 0 0 10px rgba(124,92,252,0); }
        }
        /* Texte bannière */
        .ai-banner-text { flex: 1; z-index: 2; }
        .ai-banner-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #2afadf;
            margin-bottom: 8px;
        }
        .ai-banner-label-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #2afadf;
            animation: blink 1.2s infinite;
        }
        .ai-banner-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.3;
            margin-bottom: 6px;
        }
        .ai-banner-title span {
            background: linear-gradient(90deg, #fc5c7d, #7c5cfc, #2afadf);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: text-shimmer 3s linear infinite;
        }
        .ai-banner-sub {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.5);
            line-height: 1.5;
        }
        /* Flèche bannière */
        .ai-banner-arrow {
            flex-shrink: 0;
            z-index: 2;
            width: 44px; height: 44px;
            border-radius: 50%;
            background: rgba(124,92,252,0.2);
            border: 1px solid rgba(124,92,252,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            transition: background 0.3s, transform 0.3s;
        }
        .ai-banner:hover .ai-banner-arrow {
            background: rgba(124,92,252,0.5);
            transform: translateX(4px);
        }
        /* Particules flottantes bannière */
        .ai-banner-particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
            animation: float-particle linear infinite;
        }
        @keyframes float-particle {
            0%   { transform: translateY(0) rotate(0deg);   opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-80px) rotate(360deg); opacity: 0; }
        }

        /* ══════════════════════════════════════════
           TAGS FEATURES bannière
        ══════════════════════════════════════════ */
        .ai-feature-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
        }
        .ai-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.68rem;
            font-weight: 500;
            padding: 3px 9px;
            border-radius: 20px;
            border: 1px solid;
        }
        .ai-tag-danger { background: rgba(255,77,109,0.1); border-color: rgba(255,77,109,0.35); color: #ff8fa3; }
        .ai-tag-teal   { background: rgba(42,250,223,0.1); border-color: rgba(42,250,223,0.35); color: #2afadf; }
        .ai-tag-purple { background: rgba(124,92,252,0.1); border-color: rgba(124,92,252,0.35); color: #b39dff; }
    </style>
</head>

<!-- Chatbot flottant -->
<div class="fixed bottom-6 right-6 z-50">
    <a href="chatbot.php"
       class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-full p-4 shadow-lg hover:shadow-xl transition transform hover:scale-105 flex items-center gap-2 group">
        <span class="text-2xl">🤖</span>
        <span class="hidden group-hover:inline text-sm font-semibold mr-2">AllergieBot</span>
    </a>
</div>

<body class="bg-gray-50">

    <!-- Hero -->
    <div class="hero-section text-white py-16">
        <div class="container mx-auto px-4 text-center relative">

            <!-- Retour -->
            <div class="absolute top-0 left-0">
                <a href="index.html" class="back-button inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 rounded-lg px-4 py-2 text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à l'accueil
                </a>
            </div>

            <!-- ★ LIEN AI HEADER — FASCINANT ★ -->
            <div class="absolute top-0 right-0">
                <a href="food_checker.php" class="ai-btn-header">
                    <!-- Particules orbitales -->
                    <span class="orbit orbit-1"></span>
                    <span class="orbit orbit-2"></span>
                    <span class="orbit orbit-3"></span>
                    <!-- Icône scan -->
                    <span class="scan-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="1.5">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m21 21-4.35-4.35" stroke-linecap="round"/>
                            <path d="M8 11h6M11 8v6" stroke="rgba(99,255,210,0.9)" stroke-linecap="round"/>
                        </svg>
                        <span class="scan-line"></span>
                    </span>
                    <!-- Badge AI -->
                    <span class="ai-pill">AI</span>
                    <!-- Texte -->
                    <span class="btn-text">Analyser mes ingrédients</span>
                    <!-- Flèche -->
                    <svg class="arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>

            <div class="flex justify-center mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Guide complet des allergies</h1>
            <p class="text-lg md:text-xl opacity-90 max-w-2xl mx-auto">
                Découvrez les différentes allergies, leurs symptômes et les traitements associés
            </p>
            <div class="flex justify-center gap-4 mt-8">
                <div class="bg-white/20 rounded-full px-4 py-2 text-sm">
                    📊 <?= count($allergies) ?> allergies répertoriées
                </div>
                <div class="bg-white/20 rounded-full px-4 py-2 text-sm">
                    💊 Traitements disponibles
                </div>
            </div>
        </div>
    </div>

    <!-- Allergies -->
    <div class="container mx-auto px-4 py-12">

        <div class="mb-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-4">
                    <input type="text" id="searchInput"
                           placeholder="🔍 Rechercher une allergie..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="flex gap-3 items-center">
                    <label for="graviteFilter" class="text-gray-700 font-medium">Filtrer par gravité :</label>
                    <select id="graviteFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="all">🌍 Toutes les allergies</option>
                        <option value="faible">🟢 Gravité faible</option>
                        <option value="moyenne">🟠 Gravité moyenne</option>
                        <option value="grave">🔴 Gravité grave</option>
                    </select>
                    <div class="ml-auto text-sm text-gray-500">
                        <span id="resultCount"><?= count($allergies) ?></span> allergie(s) trouvée(s)
                    </div>
                </div>
            </div>
        </div>

        <?php if (count($allergies) == 0): ?>
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🌿</div>
                <p class="text-gray-500 text-lg">Aucune allergie disponible pour le moment.</p>
                <a href="allergie_add.php" class="text-blue-600 hover:underline mt-2 inline-block">Ajouter une allergie</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="allergiesGrid">
                <?php foreach ($allergies as $allergie):
                    $graviteValue = strtolower($allergie['gravite'] ?? 'non définie');
                ?>
                    <div class="card-allergy bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 allergy-card"
                         data-name="<?= strtolower(htmlspecialchars($allergie['nom'])) ?>"
                         data-gravite="<?= $graviteValue ?>">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-xl">🌿</span>
                                    </div>
                                    <h3 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($allergie['nom']) ?></h3>
                                </div>
                                <?php
                                    $graviteClass = match($graviteValue) {
                                        'faible' => 'bg-green-100 text-green-700',
                                        'moyenne' => 'bg-orange-100 text-orange-700',
                                        'grave'   => 'bg-red-100 text-red-700',
                                        default   => 'bg-gray-100 text-gray-600'
                                    };
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $graviteClass ?>">
                                    <?= ucfirst($graviteValue) ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 text-sm">
                                <?= htmlspecialchars(substr($allergie['description'] ?? '', 0, 100)) ?>
                                <?= isset($allergie['description']) && strlen($allergie['description']) > 100 ? '...' : '' ?>
                            </p>
                        </div>
                        <?php if (!empty($allergie['symptomes'])): ?>
                        <div class="px-4 pb-2">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>🤧</span>
                                <span class="truncate"><?= htmlspecialchars(substr($allergie['symptomes'], 0, 60)) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="p-4 pt-2">
                            <a href="?id=<?= $allergie['id_allergie'] ?>"
                               class="block w-full bg-blue-600 text-white py-2 rounded-lg font-semibold text-center hover:bg-blue-700 transition">
                                📖 Voir détails
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


        <!-- ★ BANNIÈRE AI BAS DE PAGE — FASCINANTE ★ -->
        <div class="mt-16 px-4">
            <p class="text-center text-gray-400 text-sm mb-5 tracking-wide uppercase" style="letter-spacing:0.1em;">
                Un doute sur un produit ?
            </p>

            <a href="food_checker.php" class="ai-banner">
                <div class="ai-banner-inner">

                    <!-- Particules flottantes -->
                    <span class="ai-banner-particle" style="width:4px;height:4px;background:#7c5cfc;left:10%;bottom:10%;animation-duration:4s;animation-delay:0s;"></span>
                    <span class="ai-banner-particle" style="width:3px;height:3px;background:#2afadf;left:25%;bottom:5%;animation-duration:5s;animation-delay:-2s;"></span>
                    <span class="ai-banner-particle" style="width:5px;height:5px;background:#fc5c7d;left:60%;bottom:15%;animation-duration:3.5s;animation-delay:-1s;"></span>
                    <span class="ai-banner-particle" style="width:3px;height:3px;background:#fff;left:75%;bottom:8%;animation-duration:6s;animation-delay:-3s;"></span>
                    <span class="ai-banner-particle" style="width:4px;height:4px;background:#2afadf;left:88%;bottom:12%;animation-duration:4.5s;animation-delay:-1.5s;"></span>

                    <!-- Icône pulsante -->
                    <div class="ai-banner-icon">
                        <span class="ring ring-1"></span>
                        <span class="ring ring-2"></span>
                        <span class="ring ring-3"></span>
                        <div class="core">🔬</div>
                    </div>

                    <!-- Texte -->
                    <div class="ai-banner-text">
                        <div class="ai-banner-label">
                            <span class="ai-banner-label-dot"></span>
                            Groq AI · Analyse instantanée
                        </div>
                        <div class="ai-banner-title">
                            Analysez vos ingrédients<br>
                            <span>avec l'intelligence artificielle</span>
                        </div>
                        <div class="ai-banner-sub">
                            Collez n'importe quelle étiquette — détection allergènes, risques et alternatives en secondes.
                        </div>
                        <div class="ai-feature-tags">
                            <span class="ai-tag ai-tag-danger">🚫 14 allergènes EU</span>
                            <span class="ai-tag ai-tag-teal">⚡ Analyse < 3s</span>
                            <span class="ai-tag ai-tag-purple">📄 Export PDF</span>
                        </div>
                    </div>

                    <!-- Flèche -->
                    <div class="ai-banner-arrow">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>

                </div>
            </a>
        </div>

    </div>

    <!-- Modal détails -->
    <?php if ($allergie_detail): ?>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">
                    <span class="text-2xl mr-2">🌿</span>
                    <?= htmlspecialchars($allergie_detail['nom']) ?>
                </h2>
                <a href="?" class="text-gray-400 hover:text-gray-600 transition text-2xl">✕</a>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <?php
                        $gravite = $allergie_detail['gravite'] ?? 'non définie';
                        $graviteBadgeClass = match(strtolower($gravite)) {
                            'faible' => 'bg-green-100 text-green-700',
                            'moyenne' => 'bg-orange-100 text-orange-700',
                            'grave'   => 'bg-red-100 text-red-700',
                            default   => 'bg-gray-100 text-gray-600'
                        };
                    ?>
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold <?= $graviteBadgeClass ?>">
                        Gravité : <?= ucfirst($gravite) ?>
                    </span>
                </div>
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-2 text-lg">📝 Description</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-600 leading-relaxed"><?= htmlspecialchars($allergie_detail['description'] ?? 'Aucune description disponible') ?></p>
                    </div>
                </div>
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-2 text-lg">🤧 Symptômes</h3>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-gray-600 leading-relaxed"><?= htmlspecialchars($allergie_detail['symptomes'] ?? 'Aucun symptôme répertorié') ?></p>
                    </div>
                </div>
                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-700 mb-3 text-lg">💊 Traitements associés</h3>
                    <?php if (count($traitements) > 0): ?>
                        <div class="space-y-3">
                            <?php foreach ($traitements as $index => $t): ?>
                                <div class="bg-white rounded-lg p-3 border border-gray-200 hover:shadow-md transition">
                                    <div class="flex items-start gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                                            <span class="text-white text-xs font-bold"><?= $index + 1 ?></span>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($t['nom_traitement']) ?></h4>
                                            <?php if (!empty($t['conseils'])): ?>
                                                <p class="text-sm text-gray-600 mt-1"><span class="font-medium text-green-700">📌 Conseils :</span> <?= htmlspecialchars($t['conseils']) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($t['interdiction'])): ?>
                                                <p class="text-sm text-red-600 mt-1"><span class="font-medium">🚫 Interdictions :</span> <?= htmlspecialchars($t['interdiction']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-gray-500 text-sm">💊 Aucun traitement répertorié pour cette allergie.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 mt-4">
                    <div class="flex justify-around text-center">
                        <div>
                            <p class="text-2xl font-bold text-blue-600"><?= count($traitements) ?></p>
                            <p class="text-xs text-gray-500">Traitements</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">1</p>
                            <p class="text-xs text-gray-500">Allergie</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="?" class="block w-full bg-gray-200 text-gray-700 py-2 rounded-lg font-semibold text-center hover:bg-gray-300 transition">Fermer</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        const graviteSelect  = document.getElementById('graviteFilter');
        const searchInput    = document.getElementById('searchInput');
        const allergyCards   = document.querySelectorAll('.allergy-card');
        const resultCountSpan = document.getElementById('resultCount');
        let currentSearch = '';

        function filterCards() {
            const selectedGravite = graviteSelect.value;
            let visibleCount = 0;
            allergyCards.forEach(card => {
                const graviteMatch = selectedGravite === 'all' || card.dataset.gravite === selectedGravite;
                const searchMatch  = !currentSearch || card.dataset.name.includes(currentSearch);
                card.style.display = (graviteMatch && searchMatch) ? '' : 'none';
                if (graviteMatch && searchMatch) visibleCount++;
            });
            if (resultCountSpan) resultCountSpan.textContent = visibleCount;

            const grid = document.getElementById('allergiesGrid');
            let noMsg = document.getElementById('noResultMessage');
            if (visibleCount === 0) {
                if (!noMsg) {
                    noMsg = document.createElement('div');
                    noMsg.id = 'noResultMessage';
                    noMsg.className = 'col-span-full text-center py-12';
                    noMsg.innerHTML = `
                        <div class="text-6xl mb-4">🔍</div>
                        <p class="text-gray-500 text-lg">Aucune allergie ne correspond à vos critères</p>
                        <button onclick="resetFilters()" class="mt-4 text-blue-600 hover:underline">Réinitialiser</button>`;
                    grid.appendChild(noMsg);
                }
            } else { if (noMsg) noMsg.remove(); }
        }

        window.resetFilters = function() {
            graviteSelect.value = 'all';
            if (searchInput) { searchInput.value = ''; currentSearch = ''; }
            filterCards();
        };

        graviteSelect?.addEventListener('change', filterCards);
        searchInput?.addEventListener('input', function() { currentSearch = this.value.toLowerCase(); filterCards(); });
        filterCards();
    </script>

</body>
</html>