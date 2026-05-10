<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ingredient Checker — AllergieScan AI</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">

    <!-- jsPDF pour export PDF côté client -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0a0a0f;
            --surface:   #12121a;
            --card:      #1a1a26;
            --border:    #2a2a3d;
            --accent:    #7c5cfc;
            --accent2:   #fc5c7d;
            --teal:      #2afadf;
            --text:      #e8e8f0;
            --muted:     #6b6b8a;
            --danger:    #ff4d6d;
            --warn:      #ffb830;
            --ok:        #00e5a0;
            --font-h:    'Syne', sans-serif;
            --font-b:    'DM Sans', sans-serif;
            --r:         16px;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--font-b);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            opacity: .18;
            pointer-events: none;
            z-index: 0;
        }
        .blob-1 { width: 600px; height: 600px; background: var(--accent); top: -150px; left: -200px; }
        .blob-2 { width: 500px; height: 500px; background: var(--accent2); bottom: -100px; right: -150px; }
        .blob-3 { width: 300px; height: 300px; background: var(--teal); top: 40%; left: 50%; transform: translateX(-50%); }

        .wrap { position: relative; z-index: 1; max-width: 860px; margin: 0 auto; padding: 0 24px 80px; }

        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 0 40px;
        }
        .logo {
            font-family: var(--font-h);
            font-size: 1.1rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--accent), var(--teal));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }
        .back-link {
            font-size: .8rem;
            color: var(--muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color .2s;
        }
        .back-link:hover { color: var(--text); }

        .hero { text-align: center; margin-bottom: 52px; }
        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(124,92,252,.15);
            border: 1px solid rgba(124,92,252,.3);
            border-radius: 100px;
            padding: 6px 16px;
            font-size: .75rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 20px;
        }
        .hero h1 {
            font-family: var(--font-h);
            font-size: clamp(2.2rem, 5vw, 3.6rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 16px;
        }
        .hero h1 span {
            background: linear-gradient(135deg, var(--accent2), var(--accent), var(--teal));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero p { color: var(--muted); font-size: 1.05rem; max-width: 540px; margin: 0 auto; line-height: 1.7; }

        .checker-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            margin-bottom: 32px;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            background: rgba(124,92,252,.05);
        }
        .card-header .dot { width: 10px; height: 10px; border-radius: 50%; }
        .dot-r { background: #ff5f57; }
        .dot-y { background: #ffbd2e; }
        .dot-g { background: #28c840; }
        .card-header-label {
            margin-left: 6px;
            font-size: .8rem;
            color: var(--muted);
            font-family: var(--font-h);
            letter-spacing: .05em;
        }

        .card-body { padding: 24px; }

        textarea {
            width: 100%;
            min-height: 180px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r);
            color: var(--text);
            font-family: var(--font-b);
            font-size: .95rem;
            line-height: 1.6;
            padding: 16px;
            resize: vertical;
            outline: none;
            transition: border-color .25s, box-shadow .25s;
        }
        textarea::placeholder { color: var(--muted); }
        textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(124,92,252,.15);
        }

        .hint-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            font-size: .78rem;
            color: var(--muted);
            flex-wrap: wrap;
        }
        .hint-chip {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 100px;
            padding: 3px 10px;
            cursor: pointer;
            transition: border-color .2s, color .2s;
            white-space: nowrap;
        }
        .hint-chip:hover { border-color: var(--accent); color: var(--accent); }

        .btn-analyze {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 20px;
            padding: 16px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border: none;
            border-radius: var(--r);
            color: #fff;
            font-family: var(--font-h);
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .03em;
            cursor: pointer;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 30px rgba(124,92,252,.35);
        }
        .btn-analyze:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 8px 40px rgba(124,92,252,.45); }
        .btn-analyze:active { transform: translateY(0); }
        .btn-analyze:disabled { opacity: .5; cursor: not-allowed; transform: none; }

        /* ── Export bar ── */
        .export-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--r);
            border: 1px solid;
            font-family: var(--font-h);
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: .03em;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
        }
        .btn-export-pdf {
            background: rgba(255,77,109,.1);
            border-color: rgba(255,77,109,.4);
            color: #ff8fa3;
        }
        .btn-export-pdf:hover {
            background: rgba(255,77,109,.2);
            border-color: rgba(255,77,109,.7);
            transform: translateY(-1px);
        }
        .btn-export-print {
            background: rgba(124,92,252,.1);
            border-color: rgba(124,92,252,.4);
            color: #b39dff;
        }
        .btn-export-print:hover {
            background: rgba(124,92,252,.2);
            border-color: rgba(124,92,252,.7);
            transform: translateY(-1px);
        }
        .btn-export-copy {
            background: rgba(42,250,223,.08);
            border-color: rgba(42,250,223,.3);
            color: var(--teal);
        }
        .btn-export-copy:hover {
            background: rgba(42,250,223,.15);
            border-color: rgba(42,250,223,.6);
            transform: translateY(-1px);
        }
        .btn-export:active { transform: translateY(0) scale(.98); }

        .export-label {
            font-size: .72rem;
            color: var(--muted);
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-family: var(--font-h);
        }

        /* ── Spinner ── */
        .spinner {
            width: 20px; height: 20px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        #results {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity .4s, transform .4s;
            display: none;
        }
        #results.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .result-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 16px;
        }

        .result-section-title {
            font-family: var(--font-h);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 14px;
        }

        .risk-banner {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 20px;
            border-radius: var(--r);
            margin-bottom: 24px;
            border: 1px solid;
        }
        .risk-banner.high   { background: rgba(255,77,109,.08); border-color: rgba(255,77,109,.3); }
        .risk-banner.medium { background: rgba(255,184,48,.08); border-color: rgba(255,184,48,.3); }
        .risk-banner.low    { background: rgba(0,229,160,.08);  border-color: rgba(0,229,160,.3);  }

        .risk-icon { font-size: 2.2rem; }
        .risk-label { font-family: var(--font-h); font-size: 1.1rem; font-weight: 800; }
        .risk-banner.high   .risk-label { color: var(--danger); }
        .risk-banner.medium .risk-label { color: var(--warn); }
        .risk-banner.low    .risk-label { color: var(--ok); }
        .risk-sub { font-size: .82rem; color: var(--muted); margin-top: 2px; }

        .pill-grid { display: flex; flex-wrap: wrap; gap: 8px; }
        .pill {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 100px;
            font-size: .82rem;
            font-weight: 500;
            border: 1px solid;
        }
        .pill-danger { background: rgba(255,77,109,.1); border-color: rgba(255,77,109,.35); color: #ff8fa3; }
        .pill-warn   { background: rgba(255,184,48,.1); border-color: rgba(255,184,48,.35); color: #ffd060; }
        .pill-ok     { background: rgba(0,229,160,.1);  border-color: rgba(0,229,160,.35);  color: #00e5a0; }

        .why-text {
            font-size: .9rem;
            line-height: 1.75;
            color: #c0c0d8;
            background: var(--surface);
            border-radius: var(--r);
            padding: 16px;
            border-left: 3px solid var(--accent);
        }

        .alt-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }
        .alt-item:last-child { border-bottom: none; padding-bottom: 0; }
        .alt-icon { font-size: 1.3rem; flex-shrink: 0; margin-top: 1px; }
        .alt-text { font-size: .88rem; line-height: 1.6; color: #c0c0d8; }
        .alt-text strong { color: var(--text); }

        .summary-box {
            background: linear-gradient(135deg, rgba(124,92,252,.1), rgba(42,250,223,.05));
            border: 1px solid rgba(124,92,252,.25);
            border-radius: var(--r);
            padding: 18px;
        }
        .summary-text { font-size: .9rem; line-height: 1.75; color: #c0c0d8; }

        .examples-section { margin-top: 40px; }
        .examples-title {
            font-family: var(--font-h);
            font-size: .7rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 14px;
        }
        .ex-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
        .ex-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 16px;
            cursor: pointer;
            transition: border-color .2s, transform .15s;
        }
        .ex-card:hover { border-color: var(--accent); transform: translateY(-2px); }
        .ex-card-icon { font-size: 1.6rem; margin-bottom: 8px; }
        .ex-card-name { font-family: var(--font-h); font-size: .85rem; font-weight: 700; margin-bottom: 4px; }
        .ex-card-desc { font-size: .75rem; color: var(--muted); line-height: 1.5; }

        footer {
            text-align: center;
            padding: 40px 0 0;
            font-size: .78rem;
            color: var(--muted);
        }

        .loading-bar {
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--accent2), var(--teal));
            background-size: 200% 100%;
            animation: slide 1.5s linear infinite;
            border-radius: 3px;
            margin-bottom: 20px;
        }
        @keyframes slide { from { background-position: 200% 0; } to { background-position: 0% 0; } }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 14px 20px;
            font-size: .85rem;
            color: var(--text);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(80px);
            opacity: 0;
            transition: all .3s cubic-bezier(.34,1.56,.64,1);
            box-shadow: 0 10px 40px rgba(0,0,0,.5);
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast-ok { border-left: 3px solid var(--ok); }
        .toast-err { border-left: 3px solid var(--danger); }

        @media (max-width: 600px) {
            .hero h1 { font-size: 2rem; }
            .hint-row { justify-content: center; }
            .export-bar { flex-direction: column; }
            .btn-export { justify-content: center; }
        }

        /* Print styles pour impression propre */
        @media print {
            body { background: #fff !important; color: #000 !important; }
            .blob, nav .back-link, .btn-analyze, .hint-row, .examples-section, footer, .export-bar, .export-label { display: none !important; }
            .checker-card { display: none !important; }
            .result-card { background: #fff !important; border: 1px solid #ddd !important; break-inside: avoid; }
            .risk-label { color: #000 !important; }
            .pill { border: 1px solid #ccc !important; color: #333 !important; background: #f5f5f5 !important; }
            .why-text { background: #f9f9f9 !important; color: #333 !important; border-left: 3px solid #7c5cfc !important; }
            .summary-box { background: #f0f0ff !important; border: 1px solid #ccc !important; }
            .summary-text, .alt-text { color: #333 !important; }
        }
    </style>
</head>
<body>

<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<div class="wrap">

    <nav>
        <a href="allergie_report.php" class="logo">⬡ AllergieScan</a>
        <a href="allergie_report.php" class="back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M5 12l7 7M5 12l7-7"/></svg>
            Retour au rapport
        </a>
    </nav>

    <div class="hero">
        <div class="hero-eyebrow">
            <span>✦</span> Powered by Groq AI
        </div>
        <h1>Vérifiez vos<br><span>ingrédients alimentaires</span></h1>
        <p>Collez la liste d'ingrédients de n'importe quel produit. Notre IA détecte instantanément les allergènes et évalue les risques pour votre santé.</p>
    </div>

    <div class="checker-card">
        <div class="card-header">
            <div class="dot dot-r"></div>
            <div class="dot dot-y"></div>
            <div class="dot dot-g"></div>
            <span class="card-header-label">ingredient_analyzer.ai</span>
        </div>
        <div class="card-body">
            <textarea id="ingredientsInput"
                placeholder="Collez ici la liste des ingrédients...&#10;&#10;Exemple : Farine de blé, lait entier, œufs, sucre, beurre, sel, émulsifiant (lécithine de soja), arôme vanille..."></textarea>

            <div class="hint-row">
                <span>Essayez :</span>
                <span class="hint-chip" onclick="loadExample('biscuit')">🍪 Biscuit</span>
                <span class="hint-chip" onclick="loadExample('glace')">🍦 Glace</span>
                <span class="hint-chip" onclick="loadExample('pain')">🥖 Pain</span>
                <span class="hint-chip" onclick="loadExample('cosmetic')">🧴 Cosmétique</span>
            </div>

            <button class="btn-analyze" id="analyzeBtn" onclick="analyzeIngredients()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                Analyser les ingrédients
            </button>
        </div>
    </div>

    <div id="results">
        <div id="loadingBar" class="loading-bar" style="display:none"></div>
        <div id="resultsInner"></div>
    </div>

    <div class="examples-section">
        <div class="examples-title">✦ Produits exemples — cliquez pour tester</div>
        <div class="ex-grid">
            <div class="ex-card" onclick="loadExample('biscuit')">
                <div class="ex-card-icon">🍪</div>
                <div class="ex-card-name">Biscuit chocolat</div>
                <div class="ex-card-desc">Gluten, lait, œufs, soja — cocktail classique d'allergènes</div>
            </div>
            <div class="ex-card" onclick="loadExample('glace')">
                <div class="ex-card-icon">🍦</div>
                <div class="ex-card-name">Glace vanille</div>
                <div class="ex-card-desc">Produits laitiers, œufs et traces de noix</div>
            </div>
            <div class="ex-card" onclick="loadExample('pain')">
                <div class="ex-card-icon">🥖</div>
                <div class="ex-card-name">Pain complet</div>
                <div class="ex-card-desc">Gluten de blé, seigle, sésame</div>
            </div>
            <div class="ex-card" onclick="loadExample('cosmetic')">
                <div class="ex-card-icon">🧴</div>
                <div class="ex-card-name">Crème visage</div>
                <div class="ex-card-desc">Allergènes cosmétiques : parfums, conservateurs</div>
            </div>
        </div>
    </div>

    <footer>
        ⚠️ AllergieScan AI est un outil d'aide à la décision. Consultez toujours votre médecin ou allergologue.
    </footer>
</div>

<!-- Toast notification -->
<div class="toast" id="toast"></div>

<script>
// ── Données globales ──────────────────────────────────────────────
let lastResult = null;
let lastIngredients = '';

const EXAMPLES = {
    biscuit: `Farine de blé (gluten), sucre, beurre (lait), œufs entiers, pépites de chocolat au lait (cacao, lait entier en poudre, sucre, lécithine de soja, arôme vanille), amidon de maïs, levure chimique (phosphates), sel, huile de palme, traces possibles de cacahuètes, noisettes, amandes.`,
    glace: `Lait entier, crème fraîche (lait), sucre, jaunes d'œufs, sirop de glucose, protéines de lait, arôme naturel de vanille, émulsifiant (lécithine de tournesol), stabilisants (farine de guar, carraghénane). Peut contenir des traces de noix, pistaches et noisettes.`,
    pain: `Farine de blé T65 (gluten), farine de seigle, eau, levain de blé, sel marin, graines de sésame, graines de tournesol, farine de malt d'orge, huile de colza. Fabriqué dans un atelier utilisant des noix et du lait.`,
    cosmetic: `Aqua, Glycerin, Cetearyl Alcohol, Caprylic/Capric Triglyceride, Prunus Amygdalus Dulcis Oil (Sweet Almond), Tocopheryl Acetate, Phenoxyethanol, Ethylhexylglycerin, Parfum (Fragrance), Linalool, Limonene, Citronellol, Benzyl Alcohol, Methylisothiazolinone, Butylphenyl Methylpropional.`
};

function loadExample(key) {
    document.getElementById('ingredientsInput').value = EXAMPLES[key];
}

// ── Toast ──────────────────────────────────────────────────────────
function showToast(msg, type = 'ok') {
    const toast = document.getElementById('toast');
    toast.className = `toast toast-${type} show`;
    toast.innerHTML = (type === 'ok' ? '✅ ' : '❌ ') + msg;
    setTimeout(() => { toast.className = 'toast'; }, 3000);
}

// ── Analyse IA ────────────────────────────────────────────────────
async function analyzeIngredients() {
    const text = document.getElementById('ingredientsInput').value.trim();
    if (!text) {
        document.getElementById('ingredientsInput').style.borderColor = 'var(--danger)';
        setTimeout(() => document.getElementById('ingredientsInput').style.borderColor = '', 1500);
        return;
    }

    lastIngredients = text;

    const btn = document.getElementById('analyzeBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Analyse en cours...';

    const resultsDiv = document.getElementById('results');
    const resultsInner = document.getElementById('resultsInner');
    const loadingBar = document.getElementById('loadingBar');

    loadingBar.style.display = 'block';
    resultsInner.innerHTML = '';
    resultsDiv.style.display = 'block';
    resultsDiv.style.opacity = '0';
    resultsDiv.style.transform = 'translateY(20px)';
    setTimeout(() => {
        resultsDiv.style.transition = 'opacity .4s, transform .4s';
        resultsDiv.style.opacity = '1';
        resultsDiv.style.transform = 'translateY(0)';
    }, 50);

    try {
        const prompt = `Tu es un expert en allergologie alimentaire et cosmétique. Analyse cette liste d'ingrédients et retourne UNIQUEMENT un JSON valide (sans markdown, sans backticks) avec cette structure :
{"risk_level":"high|medium|low","risk_label":"Risque élevé|Risque modéré|Risque faible","risk_description":"phrase courte","allergens_detected":[{"name":"allergène","ingredient":"source","severity":"high|medium|low","emoji":"emoji"}],"why_dangerous":"2-3 phrases","alternatives":[{"emoji":"✅","title":"titre","text":"conseil"}],"summary":"2 phrases"}

INGRÉDIENTS : ${text}

Réponds UNIQUEMENT avec le JSON, sans aucun texte avant ou après.`;

        const response = await fetch("groq_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ question: prompt })
        });

        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Erreur serveur');

        let result;
        try {
            const cleaned = data.response.replace(/```json|```/g, '').trim();
            result = JSON.parse(cleaned);
        } catch(e) {
            throw new Error('Réponse IA invalide — réessayez');
        }

        lastResult = result;
        loadingBar.style.display = 'none';
        renderResults(result);

    } catch (err) {
        loadingBar.style.display = 'none';
        resultsInner.innerHTML = `
            <div class="result-card" style="border-color: rgba(255,77,109,.3);">
                <div style="color: var(--danger); font-family: var(--font-h); font-weight:700; margin-bottom:8px;">⚠️ Erreur d'analyse</div>
                <div style="color: var(--muted); font-size:.88rem;">${err.message || 'Une erreur est survenue. Veuillez réessayer.'}</div>
            </div>`;
    }

    btn.disabled = false;
    btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> Analyser les ingrédients`;
}

// ── Rendu des résultats ───────────────────────────────────────────
function renderResults(r) {
    const riskClass = r.risk_level === 'high' ? 'high' : r.risk_level === 'medium' ? 'medium' : 'low';
    const riskEmoji = r.risk_level === 'high' ? '🚨' : r.risk_level === 'medium' ? '⚠️' : '✅';

    let allergensHTML = '';
    if (r.allergens_detected && r.allergens_detected.length > 0) {
        const pills = r.allergens_detected.map(a => {
            const pillClass = a.severity === 'high' ? 'pill-danger' : a.severity === 'medium' ? 'pill-warn' : 'pill-ok';
            return `<div class="pill ${pillClass}">${a.emoji || '⚠️'} <strong>${a.name}</strong><span style="opacity:.6;font-size:.75em"> — ${a.ingredient}</span></div>`;
        }).join('');
        allergensHTML = `
            <div class="result-card">
                <div class="result-section-title">🚫 Allergènes détectés</div>
                <div class="pill-grid">${pills}</div>
            </div>`;
    } else {
        allergensHTML = `
            <div class="result-card">
                <div class="result-section-title">✅ Allergènes</div>
                <div class="pill-grid"><div class="pill pill-ok">✅ Aucun allergène majeur détecté</div></div>
            </div>`;
    }

    const altsHTML = (r.alternatives || []).map(a => `
        <div class="alt-item">
            <div class="alt-icon">${a.emoji}</div>
            <div class="alt-text"><strong>${a.title}</strong><br>${a.text}</div>
        </div>`).join('');

    const exportBar = `
        <div class="export-label">✦ Exporter le rapport</div>
        <div class="export-bar">
            <button class="btn-export btn-export-pdf" onclick="exportPDF()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Télécharger PDF
            </button>
            <button class="btn-export btn-export-print" onclick="window.print()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimer
            </button>
            <button class="btn-export btn-export-copy" onclick="copyReport()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                Copier le texte
            </button>
        </div>`;

    const resultsInner = document.getElementById('resultsInner');
    resultsInner.innerHTML = `
        ${exportBar}

        <div class="result-card">
            <div class="result-section-title">📊 Évaluation du risque</div>
            <div class="risk-banner ${riskClass}">
                <div class="risk-icon">${riskEmoji}</div>
                <div>
                    <div class="risk-label">${r.risk_label || 'Niveau de risque'}</div>
                    <div class="risk-sub">${r.risk_description || ''}</div>
                </div>
            </div>
        </div>

        ${allergensHTML}

        <div class="result-card">
            <div class="result-section-title">📌 Pourquoi c'est important</div>
            <div class="why-text">${r.why_dangerous || ''}</div>
        </div>

        ${altsHTML ? `
        <div class="result-card">
            <div class="result-section-title">✅ Recommandations & alternatives</div>
            ${altsHTML}
        </div>` : ''}

        <div class="result-card">
            <div class="result-section-title">🧾 Résumé</div>
            <div class="summary-box">
                <div class="summary-text">${r.summary || ''}</div>
            </div>
        </div>

        <div style="text-align:center; color: var(--muted); font-size:.78rem; padding: 8px 0 0;">
            ⚕️ Cette analyse est fournie à titre informatif. Consultez un professionnel de santé pour toute décision médicale.
        </div>
    `;

    document.getElementById('results').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Export PDF ────────────────────────────────────────────────────
function exportPDF() {
    if (!lastResult) { showToast('Aucun résultat à exporter', 'err'); return; }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });

    const r = lastResult;
    const now = new Date().toLocaleDateString('fr-FR', { day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit' });
    const pageW = 210;
    const margin = 18;
    const contentW = pageW - margin * 2;
    let y = 0;

    // ── Helpers ────────────────────────────────────────
    const addPage = () => { doc.addPage(); y = 20; };

    const checkY = (needed = 20) => { if (y + needed > 275) addPage(); };

    const wrapText = (text, x, yStart, maxW, lineH = 5.5) => {
        const lines = doc.splitTextToSize(text || '', maxW);
        lines.forEach(line => {
            checkY(lineH + 2);
            doc.text(line, x, y);
            y += lineH;
        });
        return y;
    };

    const filledRect = (x, ry, w, h, r2, fillColor, strokeColor) => {
        doc.setFillColor(...fillColor);
        if (strokeColor) doc.setDrawColor(...strokeColor);
        doc.roundedRect(x, ry, w, h, r2, r2, strokeColor ? 'FD' : 'F');
    };

    // ── Couleurs par niveau ────────────────────────────
    const riskColors = {
        high:   { fill: [255, 77, 109], light: [255, 240, 243], text: [180, 20, 50] },
        medium: { fill: [255, 184, 48], light: [255, 249, 230], text: [150, 100, 0] },
        low:    { fill: [0, 200, 140],  light: [230, 255, 245], text: [0, 120, 80] }
    };
    const rc = riskColors[r.risk_level] || riskColors.low;

    // ══════════════════════════════════════════════════
    // PAGE 1 — EN-TÊTE
    // ══════════════════════════════════════════════════

    // Bande header
    filledRect(0, 0, pageW, 38, 0, [15, 10, 30]);
    doc.setTextColor(200, 190, 255);
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.text('AllergieScan AI — Rapport d\'analyse allergénique', margin, 13);
    doc.text(now, pageW - margin, 13, { align: 'right' });

    // Titre principal
    doc.setFontSize(22);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(255, 255, 255);
    doc.text('Rapport d\'analyse des ingrédients', margin, 28);

    y = 50;

    // ── Bandeau niveau de risque ──────────────────────
    const riskLabel = r.risk_level === 'high' ? 'RISQUE ELEVE' : r.risk_level === 'medium' ? 'RISQUE MODERE' : 'RISQUE FAIBLE';
    filledRect(margin, y, contentW, 22, 4, rc.light, rc.fill.map(v => Math.min(255, v + 40)));
    doc.setFillColor(...rc.fill);
    doc.roundedRect(margin, y, 5, 22, 2, 2, 'F');
    doc.setTextColor(...rc.text);
    doc.setFontSize(13);
    doc.setFont('helvetica', 'bold');
    doc.text(riskLabel, margin + 10, y + 8);
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.text(r.risk_description || '', margin + 10, y + 15);
    y += 30;

    // ── Ingrédients analysés ──────────────────────────
    checkY(30);
    doc.setFillColor(245, 245, 250);
    doc.roundedRect(margin, y, contentW, 8, 2, 2, 'F');
    doc.setTextColor(80, 80, 120);
    doc.setFontSize(7.5);
    doc.setFont('helvetica', 'bold');
    doc.text('INGRÉDIENTS ANALYSÉS', margin + 4, y + 5.5);
    y += 12;

    doc.setTextColor(60, 60, 90);
    doc.setFontSize(8.5);
    doc.setFont('helvetica', 'normal');
    wrapText(lastIngredients.substring(0, 400) + (lastIngredients.length > 400 ? '...' : ''), margin, y, contentW, 5);
    y += 8;

    // ── Séparateur ────────────────────────────────────
    doc.setDrawColor(220, 220, 235);
    doc.setLineWidth(0.4);
    doc.line(margin, y, pageW - margin, y);
    y += 10;

    // ── Allergènes détectés ───────────────────────────
    checkY(14);
    doc.setTextColor(80, 80, 120);
    doc.setFontSize(8);
    doc.setFont('helvetica', 'bold');
    doc.text('ALLERGÈNES DÉTECTÉS', margin, y);
    y += 7;

    if (r.allergens_detected && r.allergens_detected.length > 0) {
        let xPill = margin;
        const pillH = 8;
        const pillPadX = 5;

        r.allergens_detected.forEach(a => {
            const severityColor = a.severity === 'high'
                ? { bg: [255,235,238], border: [255,120,140], text: [160,20,50] }
                : a.severity === 'medium'
                    ? { bg: [255,248,225], border: [255,200,80], text: [140,90,0] }
                    : { bg: [225,255,245], border: [80,210,160], text: [0,110,70] };

            doc.setFontSize(8.5);
            const label = a.name + ' (' + a.ingredient + ')';
            const textW = doc.getTextWidth(label);
            const pillW = textW + pillPadX * 2 + 2;

            // wrap si dépasse
            if (xPill + pillW > pageW - margin) { xPill = margin; y += pillH + 4; }
            checkY(pillH + 4);

            doc.setFillColor(...severityColor.bg);
            doc.setDrawColor(...severityColor.border);
            doc.roundedRect(xPill, y - 5.5, pillW, pillH, 3, 3, 'FD');
            doc.setTextColor(...severityColor.text);
            doc.setFont('helvetica', 'bold');
            doc.text(label, xPill + pillPadX, y - 0.5);

            xPill += pillW + 5;
        });
        y += 14;
    } else {
        filledRect(margin, y, contentW, 10, 3, [225, 255, 245]);
        doc.setTextColor(0, 120, 80);
        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        doc.text('Aucun allergène majeur detecté dans ce produit.', margin + 4, y + 6.5);
        y += 18;
    }

    // ── Pourquoi dangereux ────────────────────────────
    checkY(20);
    doc.setDrawColor(220, 220, 235);
    doc.line(margin, y, pageW - margin, y);
    y += 10;

    doc.setTextColor(80, 80, 120);
    doc.setFontSize(8);
    doc.setFont('helvetica', 'bold');
    doc.text('ANALYSE DÉTAILLÉE', margin, y);
    y += 7;

    filledRect(margin, y, 3, 28, 0, [124, 92, 252]);
    doc.setTextColor(55, 55, 85);
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    wrapText(r.why_dangerous || '', margin + 7, y + 5, contentW - 7, 5.5);
    y += 8;

    // ── Alternatives ──────────────────────────────────
    if (r.alternatives && r.alternatives.length > 0) {
        checkY(20);
        doc.setDrawColor(220, 220, 235);
        doc.line(margin, y, pageW - margin, y);
        y += 10;

        doc.setTextColor(80, 80, 120);
        doc.setFontSize(8);
        doc.setFont('helvetica', 'bold');
        doc.text('RECOMMANDATIONS & ALTERNATIVES', margin, y);
        y += 8;

        r.alternatives.forEach((alt, idx) => {
            checkY(18);
            filledRect(margin, y - 4, contentW, 16, 3, [245, 245, 252]);
            doc.setFillColor(124, 92, 252);
            doc.circle(margin + 5, y + 3.5, 3, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(7.5);
            doc.setFont('helvetica', 'bold');
            doc.text(String(idx + 1), margin + 3.8, y + 5);

            doc.setTextColor(30, 30, 60);
            doc.setFontSize(9);
            doc.setFont('helvetica', 'bold');
            doc.text(alt.title || '', margin + 11, y + 1);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            doc.setTextColor(80, 80, 110);
            const altLines = doc.splitTextToSize(alt.text || '', contentW - 14);
            doc.text(altLines[0] || '', margin + 11, y + 7);
            y += 20;
        });
    }

    // ── Résumé ────────────────────────────────────────
    checkY(28);
    doc.setDrawColor(220, 220, 235);
    doc.line(margin, y, pageW - margin, y);
    y += 10;

    doc.setTextColor(80, 80, 120);
    doc.setFontSize(8);
    doc.setFont('helvetica', 'bold');
    doc.text('RÉSUMÉ', margin, y);
    y += 7;

    filledRect(margin, y, contentW, 30, 4, [240, 237, 255], [200, 190, 255]);
    doc.setTextColor(60, 45, 120);
    doc.setFontSize(9.5);
    doc.setFont('helvetica', 'italic');
    wrapText(r.summary || '', margin + 6, y + 7, contentW - 10, 5.5);
    y += 38;

    // ── Footer ────────────────────────────────────────
    const totalPages = doc.getNumberOfPages();
    for (let i = 1; i <= totalPages; i++) {
        doc.setPage(i);
        filledRect(0, 285, pageW, 12, 0, [15, 10, 30]);
        doc.setTextColor(120, 110, 160);
        doc.setFontSize(7.5);
        doc.setFont('helvetica', 'normal');
        doc.text('⚕ AllergieScan AI — Rapport généré le ' + now + ' — À titre informatif uniquement', margin, 292);
        doc.text('Page ' + i + ' / ' + totalPages, pageW - margin, 292, { align: 'right' });
    }

    // ── Sauvegarde ────────────────────────────────────
    const filename = 'AllergieScan_Rapport_' + new Date().toISOString().slice(0,10) + '.pdf';
    doc.save(filename);
    showToast('Rapport PDF téléchargé avec succès !', 'ok');
}

// ── Copier le rapport en texte ────────────────────────────────────
function copyReport() {
    if (!lastResult) { showToast('Aucun résultat à copier', 'err'); return; }
    const r = lastResult;
    const now = new Date().toLocaleString('fr-FR');

    const allergensList = (r.allergens_detected || [])
        .map(a => `  • ${a.name} (${a.ingredient}) — sévérité : ${a.severity}`)
        .join('\n') || '  Aucun allergène détecté';

    const altsList = (r.alternatives || [])
        .map(a => `  • ${a.title} : ${a.text}`)
        .join('\n') || '  Aucune alternative nécessaire';

    const text = `═══════════════════════════════════════
ALLERGIESCAN AI — RAPPORT D'ANALYSE
Généré le ${now}
═══════════════════════════════════════

NIVEAU DE RISQUE : ${(r.risk_label || '').toUpperCase()}
${r.risk_description || ''}

INGRÉDIENTS ANALYSÉS :
${lastIngredients}

ALLERGÈNES DÉTECTÉS :
${allergensList}

POURQUOI C'EST IMPORTANT :
${r.why_dangerous || ''}

RECOMMANDATIONS & ALTERNATIVES :
${altsList}

RÉSUMÉ :
${r.summary || ''}

═══════════════════════════════════════
⚕ Rapport informatif — Consultez votre médecin.
AllergieScan AI — https://allergiescan.ai
═══════════════════════════════════════`;

    navigator.clipboard.writeText(text)
        .then(() => showToast('Rapport copié dans le presse-papier !', 'ok'))
        .catch(() => showToast('Impossible de copier — essayez PDF', 'err'));
}

// ── Keyboard shortcut ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('ingredientsInput').addEventListener('keydown', e => {
        if (e.key === 'Enter' && e.ctrlKey) analyzeIngredients();
    });
});
</script>
</body>
</html>