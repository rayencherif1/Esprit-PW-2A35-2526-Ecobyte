<?php

/**
 * Test de l'API de traduction
 */

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Testeur de traduction — Ecobyte</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; }
        .wrap { max-width: 800px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin: 0 0 8px; }
        .lead { color: #64748b; font-size: 0.95rem; margin: 0 0 24px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin: 14px 0 6px; }
        textarea, select, input[type="text"] {
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; font-family: inherit;
        }
        textarea { min-height: 150px; resize: vertical; }
        .btn {
            margin-top: 12px; padding: 10px 20px; border: none; border-radius: 8px;
            background: #2563eb; color: #fff; font-weight: 600; cursor: pointer; font-size: 1rem;
        }
        .btn:hover { background: #1d4ed8; }
        .loading { display: none; color: #64748b; font-style: italic; margin-top: 12px; }
        .result { margin-top: 16px; padding: 12px; border-radius: 8px; background: #f0f9ff; border: 1px solid #bfdbfe; }
        .error { background: #fee2e2; border-color: #fecaca; color: #991b1b; }
        .success { background: #dcfce7; border-color: #bbf7d0; color: #166534; }
        .top a { color: #2563eb; font-size: 0.9rem; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrap">
        <a href="blog.php" class="top">← Retour au blog</a>
        <h1>🌐 Traducteur Automatique</h1>
        <p class="lead">Testez l'API de traduction pour convertir des textes dans différentes langues.</p>

        <div class="card">
            <label for="text">Texte à traduire *</label>
            <textarea id="text" placeholder="Entrez le texte à traduire..." required></textarea>

            <label for="target_lang">Langue cible *</label>
            <select id="target_lang" required>
                <option value="">Choisir une langue...</option>
                <option value="fr">Français</option>
                <option value="en">Anglais</option>
                <option value="es">Espagnol</option>
                <option value="de">Allemand</option>
                <option value="it">Italien</option>
                <option value="pt">Portugais</option>
                <option value="ja">Japonais</option>
                <option value="zh">Chinois</option>
                <option value="ru">Russe</option>
                <option value="ar">Arabe</option>
            </select>

            <button class="btn" onclick="traduire()">Traduire</button>
            <div id="loading" class="loading">⏳ Traduction en cours...</div>
            <div id="result"></div>
        </div>

        <div class="card">
            <h3>📚 Exemples</h3>
            <p><strong>Texte:</strong> "This is a great post, very interesting!"<br>
            <strong>Langue cible:</strong> Français</p>
            <button class="btn" onclick="exempleFr()">Essayer</button>
        </div>
    </div>

    <script>
        async function traduire() {
            const text = document.getElementById('text').value.trim();
            const targetLang = document.getElementById('target_lang').value;
            const resultDiv = document.getElementById('result');
            const loadingDiv = document.getElementById('loading');

            if (!text) {
                resultDiv.innerHTML = '<div class="result error">❌ Entrez un texte à traduire.</div>';
                return;
            }

            if (!targetLang) {
                resultDiv.innerHTML = '<div class="result error">❌ Sélectionnez une langue cible.</div>';
                return;
            }

            loadingDiv.style.display = 'block';
            resultDiv.innerHTML = '';

            try {
                const response = await fetch('api/translate.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ text, target_lang: targetLang }),
                });

                loadingDiv.style.display = 'none';

                if (!response.ok) {
                    const error = await response.json();
                    resultDiv.innerHTML = `<div class="result error">❌ ${error.error || 'Erreur inconnue'}</div>`;
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="result success">
                            <strong>✅ Traduction réussie</strong><br>
                            <strong>Original:</strong> ${escapeHtml(data.original)}<br>
                            <strong>Traduit (${data.target_lang}):</strong> ${escapeHtml(data.translated)}
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<div class="result error">❌ ${data.error || 'Erreur inconnue'}</div>`;
                }
            } catch (err) {
                loadingDiv.style.display = 'none';
                resultDiv.innerHTML = `<div class="result error">❌ Erreur réseau: ${err.message}</div>`;
            }
        }

        function exempleFr() {
            document.getElementById('text').value = 'This is a great post, very interesting!';
            document.getElementById('target_lang').value = 'fr';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Permettre Enter pour traduire
        document.getElementById('text').addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.ctrlKey) traduire();
        });
    </script>
</body>
</html>
