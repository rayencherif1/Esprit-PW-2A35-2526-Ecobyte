// Assistant de substitutions IA
class IASubstitutions {
    constructor() {
        this.init();
    }
    
    async init() {
        const allergyId = this.getAllergyIdFromUrl();
        if (!allergyId) return;
        
        try {
            const response = await fetch(`api/ia-substitutions-api.php?id=${allergyId}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderSubstitutions(data);
            }
        } catch (error) {
            console.error('Erreur chargement substitutions:', error);
        }
    }
    
    getAllergyIdFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id');
    }
    
    renderSubstitutions(data) {
        const container = document.getElementById('iaSubstitutions');
        if (!container) return;
        
        if (data.substitutions && data.substitutions.length > 0) {
            container.innerHTML = `
                <div class="bg-white rounded-lg p-3">
                    <p class="text-sm text-gray-600 mb-2">
                        <span class="font-semibold text-green-700">🎯 Alternatives suggérées :</span>
                    </p>
                    <div class="flex flex-wrap gap-2">
                        ${data.substitutions.map(sub => `
                            <span class="bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-sm">
                                ✅ ${this.escapeHtml(sub)}
                            </span>
                        `).join('')}
                    </div>
                    <div class="mt-2 text-xs text-gray-400">
                        Source: ${data.source || 'Base de données IA'}
                    </div>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="bg-white rounded-lg p-3">
                    <p class="text-sm text-gray-600">
                        ℹ️ ${data.message || 'Aucune substitution spécifique trouvée'}
                    </p>
                </div>
            `;
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.substitutions = new IASubstitutions();
});