<?php

/**
 * Testeur d'API de nutritionnelle
 */

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Analyseur nutritionnel — Ecobyte</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; }
        .wrap { max-width: 900px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin: 0 0 8px; }
        .lead { color: #64748b; font-size: 0.95rem; margin: 0 0 24px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin: 14px 0 6px; }
        input[type="file"], input[type="text"] {
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; font-family: inherit;
        }
        .btn {
            margin-top: 12px; padding: 10px 20px; border: none; border-radius: 8px;
            background: #2563eb; color: #fff; font-weight: 600; cursor: pointer; font-size: 1rem;
        }
        .btn:hover { background: #1d4ed8; }
        .loading { display: none; color: #64748b; font-style: italic; margin-top: 12px; }
        .result { margin-top: 16px; padding: 16px; border-radius: 8px; background: #f0f9ff; border: 1px solid #bfdbfe; }
        .error { background: #fee2e2; border-color: #fecaca; color: #991b1b; }
        .success { background: #dcfce7; border-color: #bbf7d0; color: #166534; }
        .top a { color: #2563eb; font-size: 0.9rem; text-decoration: none; }
        #preview { max-width: 100%; border-radius: 8px; margin-top: 12px; border: 1px solid #cbd5e1; max-height: 300px; }
        .nutrition-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .nutrition-table th, .nutrition-table td { padding: 10px; border: 1px solid #cbd5e1; text-align: left; }
        .nutrition-table th { background: #f1f5f9; font-weight: 600; }
        .nutrition-table tr:hover { background: #f8fafc; }
        .macros-chart { display: flex; gap: 8px; margin-top: 12px; height: 40px; border-radius: 8px; overflow: hidden; }
        .macro { display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 12px; }
        .protein { background: #f59e0b; }
        .fat { background: #ef4444; }
        .carbs { background: #3b82f6; }
        .health-tips { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 6px; padding: 12px; margin-top: 12px; }
        .health-tips h4 { margin: 0 0 8px; color: #92400e; }
        .health-tips ul { margin: 0; padding-left: 20px; color: #b45309; }
        .health-tips li { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="wrap">
        <a href="blog.php" class="top">← Retour au blog</a>
        <h1>🍎 Analyseur Nutritionnel</h1>
        <p class="lead">Uploadez une photo d'aliment pour connaître sa valeur nutritionnelle (calories, protéines, lipides, glucides).</p>

        <div class="card">
            <label for="image">Photo d'aliment *</label>
            <input type="file" id="image" accept="image/*" required>
            <div id="preview-container"></div>

            <label for="quantity" style="margin-top: 14px;">Quantité (en grammes) *</label>
            <input type="text" id="quantity" value="100" placeholder="Ex: 100">

            <button class="btn" onclick="analyzeFood()">Analyser</button>
            <div id="loading" class="loading">⏳ Analyse en cours...</div>
            <div id="result"></div>
        </div>

        <div class="card">
            <h3>📋 Aliments reconnus</h3>
            <p>Essayez avec: <strong>pomme, banane, pain, poulet, riz, carotte, broccoli, poisson, fromage, pizza, burger</strong>, etc.</p>
            <p><small>💡 Astuce: Renommez la photo avec le nom de l'aliment pour une meilleure reconnaissance</small></p>
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

        async function analyzeFood() {
            const fileInput = document.getElementById('image');
            const quantityInput = document.getElementById('quantity');
            const resultDiv = document.getElementById('result');
            const loadingDiv = document.getElementById('loading');

            if (!fileInput.files || !fileInput.files[0]) {
                resultDiv.innerHTML = '<div class="result error">❌ Veuillez sélectionner une image.</div>';
                return;
            }

            const quantity = parseInt(quantityInput.value) || 100;
            if (quantity <= 0 || quantity > 10000) {
                resultDiv.innerHTML = '<div class="result error">❌ La quantité doit être entre 1 et 10000g.</div>';
                return;
            }

            const formData = new FormData();
            formData.append('image', fileInput.files[0]);

            loadingDiv.style.display = 'block';
            resultDiv.innerHTML = '';

            try {
                const response = await fetch('api/nutrition_analyzer.php', {
                    method: 'POST',
                    body: formData,
                });

                loadingDiv.style.display = 'none';

                if (!response.ok) {
                    const error = await response.json();
                    resultDiv.innerHTML = `<div class="result error">❌ ${error.error || 'Erreur inconnue'}</div>`;
                    if (error.suggestions) {
                        resultDiv.innerHTML += `<p><strong>Suggestions:</strong> ${error.suggestions.slice(0, 10).join(', ')}</p>`;
                    }
                    return;
                }

                const data = await response.json();
                displayNutritionResults(data, quantity, resultDiv);

            } catch (err) {
                loadingDiv.style.display = 'none';
                resultDiv.innerHTML = `<div class="result error">❌ Erreur réseau: ${err.message}</div>`;
            }
        }

        function displayNutritionResults(data, quantity, container) {
            if (!data.success) {
                container.innerHTML = `<div class="result error">❌ ${data.error}</div>`;
                return;
            }

            const nutrition = data.nutrition;
            const macros = data.macros;
            const multiplier = quantity / 100;

            let html = `<div class="result success">✅ Aliment reconnu: <strong>${data.food_name}</strong></div>`;

            // Affichage principal
            html += `
                <div style="padding: 16px; background: #f1f5f9; border-radius: 8px; margin-top: 12px;">
                    <h3 style="margin-top: 0;">${data.food_name}</h3>
                    <p><strong>Quantité:</strong> ${quantity}g</p>
                    <p><strong style="font-size: 1.3rem; color: #2563eb;">${nutrition.calories} kcal</strong></p>
                </div>
            `;

            // Tableau nutritionnel
            html += `
                <table class="nutrition-table">
                    <thead>
                        <tr>
                            <th>Nutriment</th>
                            <th>Valeur</th>
                            <th>Pourcentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Protéines</td>
                            <td>${nutrition.protein}g</td>
                            <td>${macros.protein_percentage}%</td>
                        </tr>
                        <tr>
                            <td>Lipides</td>
                            <td>${nutrition.fat}g</td>
                            <td>${macros.fat_percentage}%</td>
                        </tr>
                        <tr>
                            <td>Glucides</td>
                            <td>${nutrition.carbs}g</td>
                            <td>${macros.carbs_percentage}%</td>
                        </tr>
                        <tr>
                            <td>Fibres</td>
                            <td>${nutrition.fiber}g</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>Sucres</td>
                            <td>${nutrition.sugar}g</td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            `;

            // Graphique des macros
            html += `
                <div style="margin-top: 12px;">
                    <p><strong>Répartition des macronutriments:</strong></p>
                    <div class="macros-chart">
                        <div class="macro protein" style="flex: ${macros.protein_percentage}%;">Protéines ${macros.protein_percentage}%</div>
                        <div class="macro fat" style="flex: ${macros.fat_percentage}%;">Lipides ${macros.fat_percentage}%</div>
                        <div class="macro carbs" style="flex: ${macros.carbs_percentage}%;">Glucides ${macros.carbs_percentage}%</div>
                    </div>
                </div>
            `;

            // Conseils santé
            if (data.health_info && data.health_info.length > 0) {
                html += `
                    <div class="health-tips">
                        <h4>💡 Informations santé:</h4>
                        <ul>
                            ${data.health_info.map(info => `<li>${info}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }

            container.innerHTML = html;
        }
    </script>
</body>
</html>
