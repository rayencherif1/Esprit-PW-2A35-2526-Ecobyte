<?php
declare(strict_types=1);
require_once __DIR__ . '/../../controller/RecetteController.php';

$controller = new RecetteController();
$allRecettes = $controller->afficherRecettes();

// Get unique types for potential navigation/filter consistency
$allTypes = array_unique(array_filter(array_column($allRecettes, 'type')));
sort($allTypes);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Favoris — EcoByte</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --eco-green: #4caf50;
            --eco-orange: #ff6b35;
            --eco-dark: #1a1a2e;
            --eco-indigo: #5e72e4;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Open Sans', sans-serif; background: #f4f6f9; min-height: 100vh; display: flex; flex-direction: column; }

        /* ═══ DARK TOPBAR ══════════════════════════════════════════════ */
        .ecobyte-topbar {
            background: var(--eco-dark); padding: 10px 32px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 2px 12px rgba(0,0,0,0.35);
        }
        .ecobyte-topbar .eco-logo {
            display: flex; align-items: center; gap: 8px;
            font-family: 'Nunito', sans-serif; font-size: 1.2rem; font-weight: 800; text-decoration: none;
        }
        .ecobyte-topbar .eco-logo .eco  { color: var(--eco-green); }
        .ecobyte-topbar .eco-logo .byte { color: var(--eco-orange); }
        .module-badge {
            background: rgba(229, 62, 62, 0.15); border: 1px solid rgba(229, 62, 62, 0.3);
            color: #fecdd3; padding: 4px 14px; border-radius: 999px; font-size: .72rem; font-weight: 700;
        }
        .topbar-right { display: flex; align-items: center; gap: 14px; }
        .hub-link {
            color: #aaa; text-decoration: none; font-size: .82rem; font-weight: 500;
            display: flex; align-items: center; gap: 5px;
        }
        .hub-link:hover { color: #fff; }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #4caf50, #2196f3); color: #fff; font-weight: 700; text-decoration: none;
        }

        /* ═══ WHITE HEADER ═══════════════════════════════════════════ */
        .site-header {
            background: #fff; border-bottom: 1px solid #e9ecef;
            padding: 16px 32px; display: flex; align-items: center; justify-content: space-between;
        }
        .site-header .brand h1 {
            font-family: 'Nunito', sans-serif; font-size: 1.5rem; font-weight: 800; margin: 0; color: #1e293b;
        }
        .header-actions { display: flex; align-items: center; gap: 14px; }
        .btn-back {
            display: flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 50px;
            font-size: .85rem; font-weight: 600; text-decoration: none;
            background: #f1f5f9; color: #1e293b; border: 1.5px solid #e2e8f0;
            transition: all .2s;
        }
        .btn-back:hover { background: #e2e8f0; }

        /* ═══ CONTENT SECTION ════════════════════════════════════════ */
        .content-section { padding: 48px 32px; flex: 1; }
        .section-header { margin-bottom: 32px; }
        .section-title { font-family: 'Nunito', sans-serif; font-size: 1.6rem; font-weight: 800; color: #1e293b; }

        /* ═══ CARD GRID ══════════════════════════════════════════════ */
        .recipes-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px;
        }
        .recipe-card {
            background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,.07);
            overflow: hidden; display: flex; flex-direction: column; transition: transform .3s;
        }
        .recipe-card:hover { transform: translateY(-5px); }
        .card-img { height: 180px; position: relative; overflow: hidden; background: #f8f9fa; }
        .card-img img { width: 100%; height: 100%; object-fit: cover; }
        .card-img .no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 4rem; }
        
        .badge-type {
            position: absolute; top: 12px; left: 12px; background: rgba(94,114,228,.9);
            color: #fff; padding: 3px 10px; border-radius: 999px; font-size: .7rem; font-weight: 700;
        }
        .card-body { padding: 18px; flex: 1; display: flex; flex-direction: column; gap: 10px; }
        .card-body h3 { font-family: 'Nunito', sans-serif; font-size: 1rem; font-weight: 800; color: #1e293b; margin: 0; }
        
        .card-footer-actions {
            margin-top: auto; display: flex; gap: 8px; padding-top: 10px; border-top: 1px solid #f1f5f9;
        }
        .btn-detail {
            flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 14px; border-radius: 50px; font-size: .82rem; font-weight: 700;
            background: linear-gradient(135deg, var(--eco-indigo), #4a5fc4); color: #fff; text-decoration: none;
        }
        .btn-remove {
            width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            background: #fff0f0; border: 1.5px solid #fecdd3; color: #e53e3e; cursor: pointer; transition: all .2s;
        }
        .btn-remove:hover { background: #e53e3e; color: #fff; border-color: #e53e3e; }

        /* ═══ EMPTY STATE ══════════════════════════════════════════════ */
        .empty-state { text-align: center; padding: 100px 20px; color: #94a3b8; }
        .empty-state .icon { font-size: 5rem; margin-bottom: 20px; }
        .empty-state h2 { color: #64748b; font-family: 'Nunito', sans-serif; margin-bottom: 16px; }

        /* ═══ FOOTER ═══════════════════════════════════════════════════ */
        .site-footer { background: var(--eco-dark); color: #64748b; text-align: center; padding: 24px; font-size: .85rem; }
    </style>
</head>
<body>

<nav class="ecobyte-topbar">
    <a href="/2int/index.php" class="eco-logo">
        <span>🌿</span> <span class="eco">ECO</span><span class="byte">BYTE</span>
    </a>
    <span class="module-badge">❤️ Mes Favoris</span>
    <div class="topbar-right">
        <a href="/2int/index.php" class="hub-link">← Hub</a>
        <a href="#" class="user-avatar">U</a>
    </div>
</nav>

<header class="site-header">
    <div class="brand"><h1>Mes Recettes Favorites</h1></div>
    <div class="header-actions">
        <a href="/2int/view/front/front.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour aux recettes
        </a>
    </div>
</header>

<main class="content-section">
    <div id="favoris-container">
        <!-- JS will inject cards here -->
        <div class="empty-state" id="empty-state">
            <div class="icon">❤️</div>
            <h2>Vous n'avez pas encore de favoris</h2>
            <p>Cliquez sur le cœur d'une recette pour l'ajouter à votre liste personnelle.</p>
            <a href="/2int/view/front/front.php" class="btn btn-primary rounded-pill px-4 py-2 mt-3" style="background:var(--eco-green); border:none;">
                Découvrir des recettes
            </a>
        </div>
        <div class="recipes-grid" id="favorites-grid" style="display:none;"></div>
    </div>
</main>

<footer class="site-footer">
    <p>© 2026 EcoByte — Cuisine & Recettes</p>
</footer>

<script>
const allRecettes = <?= json_encode(array_values($allRecettes)) ?>;

function renderFavorites() {
    const grid = document.getElementById('favorites-grid');
    const empty = document.getElementById('empty-state');
    grid.innerHTML = '';
    
    // Get favorites from localStorage
    const favorites = [];
    allRecettes.forEach(r => {
        if (localStorage.getItem('fav_' + r.id)) {
            favorites.push(r);
        }
    });

    if (favorites.length === 0) {
        grid.style.display = 'none';
        empty.style.display = 'block';
    } else {
        grid.style.display = 'grid';
        empty.style.display = 'none';
        
        favorites.forEach(r => {
            const card = document.createElement('div');
            card.className = 'recipe-card';
            card.innerHTML = `
                <div class="card-img">
                    ${r.image ? `<img src="${r.image}" alt="${r.nom}">` : '<div class="no-img">🥘</div>'}
                    <span class="badge-type">${r.type || 'Recette'}</span>
                </div>
                <div class="card-body">
                    <h3>${r.nom}</h3>
                    <div class="card-footer-actions">
                        <a href="/2int/view/front/recette-instructions.php?recette_id=${r.id}" class="btn-detail">
                            <i class="fas fa-book-open"></i> Instructions
                        </a>
                        <button class="btn-remove" onclick="removeFavori(${r.id})" title="Retirer des favoris">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });
    }
}

function removeFavori(id) {
    if (confirm('Voulez-vous retirer cette recette de vos favoris ?')) {
        localStorage.removeItem('fav_' + id);
        renderFavorites();
    }
}

document.addEventListener('DOMContentLoaded', renderFavorites);
</script>

</body>
</html>
