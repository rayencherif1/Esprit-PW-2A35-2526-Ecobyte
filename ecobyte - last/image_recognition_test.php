<?php

/**
 * Testeur d'API de reconnaissance d'images
 */

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Testeur de reconnaissance d'images — Ecobyte</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; }
        .wrap { max-width: 1000px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin: 0 0 8px; }
        .lead { color: #64748b; font-size: 0.95rem; margin: 0 0 24px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin: 14px 0 6px; }
        input[type="file"], textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; font-family: inherit;
        }
        .btn {
            margin-top: 12px; padding: 10px 20px; border: none; border-radius: 8px;
            background: #2563eb; color: #fff; font-weight: 600; cursor: pointer; font-size: 1rem;
        }
        .btn:hover { background: #1d4ed8; }
        .loading { display: none; color: #64748b; font-style: italic; margin-top: 12px; }
        .result { margin-top: 16px; padding: 12px; border-radius: 8px; background: #f0f9ff; border: 1px solid #bfdbfe; }
        .error { background: #fee2e2; border-color: #fecaca; color: #991b1b; }
        .success { background: #dcfce7; border-color: #bbf7d0; color: #166534; }
        .warning { background: #fef3c7; border-color: #fcd34d; color: #92400e; }
        .top a { color: #2563eb; font-size: 0.9rem; text-decoration: none; }
        .result-section { margin-top: 16px; }
        .result-section h3 { margin: 12px 0 8px; font-size: 1rem; }
        .result-section ul { margin: 0; padding-left: 20px; }
        .result-section li { margin: 6px 0; font-size: 0.9rem; }
        .metadata-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-top: 12px; }
        .metadata-item { background: #f8fafc; padding: 12px; border-radius: 6px; border-left: 3px solid #2563eb; }
        .metadata-item strong { display: block; font-size: 0.875rem; color: #64748b; margin-bottom: 4px; }
        .color-preview { display: flex; gap: 4px; margin-top: 8px; }
        .color-box { width: 30px; height: 30px; border-radius: 4px; border: 1px solid #cbd5e1; }
        #preview { max-width: 100%; border-radius: 8px; margin-top: 12px; border: 1px solid #cbd5e1; }
    </style>
</head>
<body>
    <div class="wrap">
        <a href="blog.php" class="top">← Retour au blog</a>
        <h1>🖼️ Testeur de reconnaissance d'images</h1>
        <p class="lead">Téléchargez une image pour analyser ses propriétés, format, contenu et métadonnées.</p>

        <div class="card">
            <label for="image">Image *</label>
            <input type="file" id="image" accept="image/*" required>
            <div id="preview-container"></div>

            <button class="btn" onclick="analyzeImage()">Analyser l'image</button>
            <div id="loading" class="loading">⏳ Analyse en cours...</div>
            <div id="result"></div>
        </div>

        <div class="card">
            <h3>📋 Informations sur l'analyse</h3>
            <p>Cette API :</p>
            <ul>
                <li>✅ Valide le format MIME et l'intégrité du fichier</li>
                <li>✅ Extrait les métadonnées (dimensions, taille, couleurs)</li>
                <li>✅ Détecte les problèmes de qualité</li>
                <li>✅ Analyse la luminosité et saturation</li>
                <li>✅ Détecte les contenu suspects</li>
                <li>⏳ Peut être étendue avec Google Vision API pour plus de détails</li>
            </ul>
        </div>
    </div>

    <script>
        // Aperçu de l'image
        document.getElementById('image').addEventListener('change', function() {
            const preview = document.getElementById('preview-container');
            preview.innerHTML = '';

            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.id = 'preview';
                    img.src = e.target.result;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        async function analyzeImage() {
            const fileInput = document.getElementById('image');
            const resultDiv = document.getElementById('result');
            const loadingDiv = document.getElementById('loading');

            if (!fileInput.files || !fileInput.files[0]) {
                resultDiv.innerHTML = '<div class="result error">❌ Veuillez sélectionner une image.</div>';
                return;
            }

            const formData = new FormData();
            formData.append('image', fileInput.files[0]);

            loadingDiv.style.display = 'block';
            resultDiv.innerHTML = '';

            try {
                const response = await fetch('api/image_recognition.php', {
                    method: 'POST',
                    body: formData,
                });

                loadingDiv.style.display = 'none';

                if (!response.ok) {
                    const error = await response.json();
                    resultDiv.innerHTML = `<div class="result error">❌ ${error.error || 'Erreur inconnue'}</div>`;
                    return;
                }

                const data = await response.json();
                displayResults(data, resultDiv);

            } catch (err) {
                loadingDiv.style.display = 'none';
                resultDiv.innerHTML = `<div class="result error">❌ Erreur réseau: ${err.message}</div>`;
            }
        }

        function displayResults(data, container) {
            let html = '';

            if (!data.success) {
                html += `<div class="result error">❌ Analyse échouée: ${data.validation ? data.validation[0] : 'Erreur inconnue'}</div>`;
                container.innerHTML = html;
                return;
            }

            html += `<div class="result success">✅ Image valide et analysée avec succès</div>`;

            // Validation
            if (data.validation && data.validation.length > 0) {
                html += '<div class="result-section">';
                html += '<h3>✓ Validation</h3><ul>';
                data.validation.forEach(v => html += `<li>${v}</li>`);
                html += '</ul></div>';
            }

            // Métadonnées
            if (data.metadata) {
                html += '<div class="result-section"><h3>📊 Métadonnées</h3>';
                html += '<div class="metadata-grid">';
                html += `<div class="metadata-item"><strong>Dimensions</strong>${data.metadata.width}x${data.metadata.height}px</div>`;
                html += `<div class="metadata-item"><strong>Taille fichier</strong>${data.file_size_mb}MB</div>`;
                html += `<div class="metadata-item"><strong>Pixels totaux</strong>${(data.metadata.pixel_count / 1000000).toFixed(2)}MP</div>`;
                html += `<div class="metadata-item"><strong>Ratio</strong>${data.metadata.ratio}:1</div>`;
                html += `<div class="metadata-item"><strong>Bits/pixel</strong>${data.metadata.bits}</div>`;
                html += `<div class="metadata-item"><strong>Luminosité</strong>${data.metadata.brightness}/255</div>`;
                html += `<div class="metadata-item"><strong>Saturation</strong>${data.metadata.saturation}%</div>`;
                
                if (data.metadata.dominant_colors && data.metadata.dominant_colors.length > 0) {
                    html += '<div class="metadata-item"><strong>Couleurs dominantes</strong>';
                    html += '<div class="color-preview">';
                    data.metadata.dominant_colors.forEach(color => {
                        html += `<div class="color-box" style="background-color: ${color}" title="${color}"></div>`;
                    });
                    html += '</div></div>';
                }
                html += '</div></div>';
            }

            // Problèmes de qualité
            if (data.quality_issues && data.quality_issues.length > 0) {
                html += '<div class="result-section"><h3>⚠️ Problèmes de qualité</h3><ul>';
                data.quality_issues.forEach(issue => html += `<li>${issue}</li>`);
                html += '</ul></div>';
            }

            // Vérifications de sécurité
            if (data.safety_checks && data.safety_checks.length > 0) {
                html += '<div class="result-section"><h3>🔒 Vérifications de sécurité</h3><ul>';
                data.safety_checks.forEach(check => html += `<li>${check}</li>`);
                html += '</ul></div>';
            }

            // Verdict final
            const statusClass = data.is_safe ? 'success' : 'warning';
            const statusText = data.is_safe ? '✅ Image sûre' : '⚠️ Attention requise';
            html += `<div class="result ${statusClass}">${statusText}</div>`;

            container.innerHTML = html;
        }

        // Permettre d'analyser en pressant Enter
        document.getElementById('image').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') analyzeImage();
        });
    </script>
</body>
</html>
