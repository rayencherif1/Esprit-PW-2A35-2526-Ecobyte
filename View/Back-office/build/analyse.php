<?php
/**
 * analyse.php - Interface d'analyse IA
 */

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Controller/traitement.Controller.php';
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';

$id_traitement = $_GET['id_traitement'] ?? null;
$id_allergie = $_GET['id_allergie'] ?? null;

if (!$id_traitement) {
    die('<div style="padding:20px;text-align:center;color:red;">ID du traitement manquant</div>');
}

$traitementController = new TraitementC();
$traitement = $traitementController->getTraitementById($id_traitement);

if (!$traitement) {
    die('<div style="padding:20px;text-align:center;color:red;">Traitement non trouvé</div>');
}

$allergieController = new AllergieC();
$allergie = null;
if ($id_allergie && $id_allergie !== 'null') {
    $allergie = $allergieController->getAllergieById($id_allergie);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>IA Medical Assistant</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 750px; margin: 0 auto; }
        
        .thinking {
            background: white;
            border-radius: 28px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        .brain-icon { font-size: 72px; animation: pulse 1.2s ease infinite; display: inline-block; }
        @keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.08)} }
        .thinking h2 { margin-top: 24px; font-size: 24px; color: #1f2937; }
        .thinking-sub { color: #6b7280; margin-top: 12px; font-size: 14px; }
        .progress-bar { width: 100%; height: 8px; background: #e5e7eb; border-radius: 10px; margin-top: 32px; overflow: hidden; }
        .progress-fill { width: 0%; height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); border-radius: 10px; transition: width 0.4s ease; }
        .step-text { margin-top: 16px; font-size: 13px; color: #667eea; font-weight: 500; }
        
        .results { display: none; }
        .card { background: white; border-radius: 20px; padding: 20px; margin-bottom: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.08); }
        .header-card { background: linear-gradient(135deg, #1e293b, #0f172a); color: white; text-align: center; padding: 28px 20px; }
        .ia-badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 11px; }
        .section-title { font-size: 18px; font-weight: 700; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #e5e7eb; display: flex; justify-content: space-between; }
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-warning { background: #fef3c7; color: #d97706; }
        .badge-info { background: #dbeafe; color: #2563eb; }
        .badge-success { background: #dcfce7; color: #16a34a; }
        
        .interaction-item, .population-item, .conseil-item, .alternative-item {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }
        .item-nom { font-weight: 600; color: #1e293b; }
        .item-detail { font-size: 13px; color: #64748b; margin-top: 4px; }
        .conseil-item { display: flex; align-items: center; gap: 12px; }
        
        .level-badge { padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .level-danger { background: #dc2626; color: white; }
        .level-warning { background: #f59e0b; color: white; }
        .level-info { background: #3b82f6; color: white; }
        
        .alert-critical { background: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; border-radius: 12px; margin-bottom: 16px; }
        .resume-card { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-radius: 16px; padding: 20px; margin-bottom: 16px; }
        .footer { background: #f8fafc; border-radius: 16px; padding: 16px; text-align: center; font-size: 11px; color: #94a3b8; }
        .close-btn { background: white; border: none; padding: 14px 24px; border-radius: 60px; font-weight: 600; cursor: pointer; margin-top: 16px; width: 100%; }
        .close-btn:hover { background: #f1f5f9; }
    </style>
</head>
<body>
<div class="container">
    
    <div id="thinkingScreen" class="thinking">
        <div class="brain-icon">🧠</div>
        <h2>IA Médicale en analyse<span>.</span><span>.</span><span>.</span></h2>
        <div class="thinking-sub">Modèle: phi3:mini</div>
        <div class="progress-bar"><div id="progressFill" class="progress-fill"></div></div>
        <div id="stepText" class="step-text">🔍 Connexion à l'IA locale...</div>
    </div>
    
    <div id="resultsScreen" class="results"></div>
    
</div>

<script>
const traitementId = <?= json_encode($id_traitement) ?>;
const allergieId = <?= json_encode($id_allergie) ?>;

let stepIndex = 0;
const steps = [
    "🔍 Analyse des données du traitement...",
    "🧬 Recherche des interactions médicamenteuses...",
    "🩺 Évaluation du contexte allergologique...",
    "📊 Génération des recommandations..."
];

const progressBar = document.getElementById('progressFill');
const stepTextEl = document.getElementById('stepText');

function nextStep() {
    if (stepIndex < steps.length) {
        stepTextEl.innerHTML = steps[stepIndex];
        const progress = ((stepIndex + 1) / steps.length) * 100;
        progressBar.style.width = progress + '%';
        stepIndex++;
        setTimeout(nextStep, 700);
    } else {
        callOllamaAPI();
    }
}

async function callOllamaAPI() {
    try {
        const response = await fetch('ollama_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_traitement: traitementId, id_allergie: allergieId })
        });
        
        const data = await response.json();
        
        if (data.error) throw new Error(data.error);
        displayResults(data);
        
    } catch (error) {
        document.getElementById('thinkingScreen').innerHTML = `
            <div class="brain-icon">⚠️</div>
            <h2>Erreur de connexion</h2>
            <div class="thinking-sub">Assurez-vous qu'Ollama tourne (ollama serve)</div>
            <button class="close-btn" style="margin-top:24px;" onclick="window.close()">Fermer</button>
        `;
    }
}

function displayResults(data) {
    const resultsScreen = document.getElementById('resultsScreen');
    const thinkingScreen = document.getElementById('thinkingScreen');
    
    let interactionsHtml = '';
    if (data.interactions && data.interactions.length > 0) {
        data.interactions.forEach(i => {
            let levelClass = i.niveau === 'danger' ? 'level-danger' : (i.niveau === 'warning' ? 'level-warning' : 'level-info');
            let levelText = i.niveau === 'danger' ? 'DANGER' : (i.niveau === 'warning' ? 'PRUDENCE' : 'INFO');
            interactionsHtml += `<div class="interaction-item"><div><div class="item-nom">⚠️ ${escapeHtml(i.nom)}</div><div class="item-detail">${escapeHtml(i.detail)}</div></div><span class="level-badge ${levelClass}">${levelText}</span></div>`;
        });
    }
    
    let populationsHtml = '';
    if (data.populations && data.populations.length > 0) {
        data.populations.forEach(p => {
            populationsHtml += `<div class="population-item"><div><div class="item-nom">👥 ${escapeHtml(p.groupe)}</div><div class="item-detail">${escapeHtml(p.conseil)}</div></div></div>`;
        });
    }
    
    let conseilsHtml = '';
    if (data.conseils && data.conseils.length > 0) {
        data.conseils.forEach(c => {
            conseilsHtml += `<div class="conseil-item"><span>✓</span><span>${escapeHtml(c)}</span></div>`;
        });
    }
    
    let alternativesHtml = '';
    if (data.alternatives && data.alternatives.length > 0) {
        data.alternatives.forEach(a => {
            alternativesHtml += `<div class="alternative-item"><div><div class="item-nom">💊 ${escapeHtml(a.nom)}</div><div class="item-detail">${escapeHtml(a.avantage)}</div></div></div>`;
        });
    }
    
    let alertHtml = '';
    if (data.alerte === 'critique') {
        alertHtml = `<div class="alert-critical"><div style="display:flex;align-items:center;gap:12px;"><span style="font-size:28px;">🚨</span><div><strong style="color:#dc2626;">ALERTE CRITIQUE</strong><p style="font-size:13px;">Consultation médicale OBLIGATOIRE</p></div></div></div>`;
    }
    
    resultsScreen.innerHTML = `
        <div class="card header-card"><div style="font-size:48px;">🤖</div><h2>Analyse IA — ${escapeHtml('<?= addslashes($traitement['nom_traitement']) ?>')}</h2><div class="ia-badge">phi3:mini • IA locale</div></div>
        ${alertHtml}
        <div class="card"><div class="section-title"><span>⚠️ Interactions à risque</span><span class="badge badge-warning">${data.interactions?.length || 0} détectée(s)</span></div>${interactionsHtml}</div>
        <div class="card"><div class="section-title"><span>👥 Populations à surveiller</span><span class="badge badge-info">${data.populations?.length || 0} groupe(s)</span></div>${populationsHtml}</div>
        <div class="card"><div class="section-title"><span>💡 Conseils renforcés IA</span><span class="badge badge-success">PERSONNALISÉ</span></div>${conseilsHtml}</div>
        <div class="card"><div class="section-title"><span>🔄 Alternatives suggérées</span><span class="badge badge-info">À DISCUTER</span></div>${alternativesHtml}</div>
        ${data.resume ? `<div class="resume-card"><div style="font-weight:600;margin-bottom:8px;">📋 Synthèse clinique IA</div><p style="font-size:14px;">${escapeHtml(data.resume)}</p></div>` : ''}
        <div class="footer"><div>🤖 Analyse générée par phi3:mini via Ollama</div><div style="font-size:10px;margin-top:8px;">100% local et confidentiel</div></div>
        <button class="close-btn" onclick="window.parent.hideAnalysisModal ? window.parent.hideAnalysisModal() : window.close()">Fermer</button>
    `;
    
    thinkingScreen.style.display = 'none';
    resultsScreen.style.display = 'block';
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

setTimeout(nextStep, 500);
</script>
</body>
</html>