/**
 * Script pour charger et afficher les résumés IA des posts
 */

document.addEventListener('DOMContentLoaded', function() {
    const summaryContainers = document.querySelectorAll('[data-post-summary-id]');
    
    summaryContainers.forEach(container => {
        const postId = container.getAttribute('data-post-summary-id');
        const existingSummary = container.getAttribute('data-existing-summary');
        
        // Si un résumé existe déjà, l'afficher directement
        if (existingSummary && existingSummary.trim() !== '') {
            displaySummary(container, existingSummary);
            return;
        }
        
        // Sinon, générer et charger le résumé via API
        loadAndDisplaySummary(postId, container);
    });
});

/**
 * Charge le résumé depuis l'API et l'affiche
 */
function loadAndDisplaySummary(postId, container) {
    const loader = document.createElement('p');
    loader.className = 'summary-loader';
    loader.textContent = '⏳ Génération du résumé...';
    container.appendChild(loader);
    
    fetch('api/get_summary.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        container.innerHTML = ''; // Vider le loader
        
        if (data.success && data.summary) {
            displaySummary(container, data.summary);
        } else {
            showError(container, data.error || 'Erreur lors de la génération du résumé');
        }
    })
    .catch(error => {
        container.innerHTML = ''; // Vider le loader
        showError(container, 'Erreur réseau: ' + error.message);
    });
}

/**
 * Affiche le résumé formaté
 */
function displaySummary(container, summary) {
    const summaryDiv = document.createElement('div');
    summaryDiv.className = 'ai-summary';
    summaryDiv.innerHTML = `
        <div class="summary-header">
            <span class="ai-badge">🤖 Résumé IA</span>
        </div>
        <p class="summary-content">${escapeHtml(summary)}</p>
    `;
    container.appendChild(summaryDiv);
}

/**
 * Affiche un message d'erreur
 */
function showError(container, errorMessage) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'summary-error';
    errorDiv.innerHTML = `<p>⚠️ ${escapeHtml(errorMessage)}</p>`;
    container.appendChild(errorDiv);
}

/**
 * Échappe les caractères HTML pour éviter les injections XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
