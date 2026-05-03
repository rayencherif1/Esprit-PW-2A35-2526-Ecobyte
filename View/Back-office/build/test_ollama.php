<!DOCTYPE html>
<html>
<head>
    <title>Test API Ollama</title>
</head>
<body>
    <h2>Test de l'API Ollama</h2>
    <button onclick="testAPI()">Tester l'analyse</button>
    <pre id="result"></pre>

    <script>
    async function testAPI() {
        const resultDiv = document.getElementById('result');
        resultDiv.innerHTML = 'Chargement...';
        
        try {
            const response = await fetch('ollama_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_traitement: 13,  // ID de votre traitement
                    id_allergie: 1      // ID de votre allergie
                })
            });
            
            const data = await response.json();
            resultDiv.innerHTML = JSON.stringify(data, null, 2);
        } catch (error) {
            resultDiv.innerHTML = 'Erreur: ' + error.message;
        }
    }
    </script>
</body>
</html>