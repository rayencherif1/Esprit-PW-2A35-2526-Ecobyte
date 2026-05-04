<?php

declare(strict_types=1);

/**
 * @param 'posts'|'add'|'edit'|'replies' $active
 */
function admin_layout_start(string $pageTitle, string $active = 'posts'): void
{
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — Ecobyte</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f1f5f9; color: #0f172a; margin: 0; padding: 0; min-height: 100vh; }
        .shell { max-width: 920px; margin: 0 auto; padding: 24px; }
        .nav {
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px 16px;
            padding: 12px 16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 20px;
        }
        .nav a {
            color: #475569; text-decoration: none; font-size: 0.95rem; padding: 6px 10px; border-radius: 8px;
        }
        .nav a:hover { background: #f1f5f9; color: #0f172a; }
        .nav a.active { background: #2563eb; color: #fff; }
        .nav .spacer { flex: 1; min-width: 8px; }
        h1 { font-size: 1.35rem; margin: 0 0 16px; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin: 14px 0 6px; }
        input[type="text"], input[type="date"], textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;
        }
        textarea { min-height: 320px; resize: vertical; font-family: inherit; line-height: 1.5; }
        .btn {
            display: inline-block; margin-top: 20px; padding: 10px 20px; border: none; border-radius: 8px;
            background: #2563eb; color: #fff; font-weight: 600; cursor: pointer; font-size: 1rem;
        }
        .btn:hover { background: #1d4ed8; }
        .btn-danger { background: #dc2626; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-ghost { background: #e2e8f0; color: #0f172a; }
        .btn-ghost:hover { background: #cbd5e1; }
        .ok { background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .err { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; }
        table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        th { color: #64748b; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em; }
        .muted { color: #64748b; font-size: 0.875rem; }
        .row-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .row-actions a, .row-actions button { font-size: 0.875rem; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; }
        .modal-overlay.active { display: flex; align-items: center; justify-content: center; }
        .modal-box { background: #fff; border-radius: 14px; padding: 28px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2); max-width: 420px; width: 90%; }
        .modal-title { font-size: 18px; font-weight: 800; margin: 0 0 12px; color: #0f172a; }
        .modal-message { color: #64748b; font-size: 15px; margin-bottom: 24px; line-height: 1.6; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
        .modal-btn { padding: 10px 20px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .modal-btn-cancel { background: #e2e8f0; color: #0f172a; }
        .modal-btn-cancel:hover { background: #cbd5e1; }
        .modal-btn-confirm { background: #dc2626; color: #fff; }
        .modal-btn-confirm:hover { background: #b91c1c; }
    </style>
</head>
<body>
    <div class="shell">
        <nav class="nav" aria-label="Administration" id="admin-nav">
            <a href="posts.php" class="<?= ($active === 'posts' || $active === 'edit') ? 'active' : '' ?>">Mes articles</a>
        </nav>
        <div style="margin: 18px 0 18px 2px; display: flex; gap: 18px; align-items: center; font-size: 1rem;">
            <a href="add_post.php" class="<?= $active === 'add' ? 'active' : '' ?>" style="color: #2563eb; text-decoration: underline; font-weight: 500;">Nouvel article</a>
            <a href="replies.php" class="<?= $active === 'replies' ? 'active' : '' ?>" style="color: #2563eb; text-decoration: underline; font-weight: 500;">Réponses</a>
        </div>
    <?php
}

function admin_layout_end(): void
{
    ?>
    </div>
    <!-- Modal de confirmation -->
    <div id="deleteModal" class="modal-overlay">
      <div class="modal-box">
        <h2 class="modal-title">Confirmer la suppression</h2>
        <p class="modal-message" id="deleteMessage">Êtes-vous sûr de vouloir supprimer cet élément ?</p>
        <div class="modal-actions">
          <button type="button" class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">Annuler</button>
          <button type="button" class="modal-btn modal-btn-confirm" onclick="confirmDelete()">Supprimer</button>
        </div>
      </div>
    </div>

    <script>
      let deleteForm = null;

      function openDeleteModal(message, form) {
        deleteForm = form;
        document.getElementById('deleteMessage').textContent = message;
        document.getElementById('deleteModal').classList.add('active');
      }

      function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
        deleteForm = null;
      }

      function confirmDelete() {
        if (deleteForm) {
          deleteForm.submit();
        }
      }

      // Fermer la modale en cliquant en dehors
      document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
          closeDeleteModal();
        }
      });

      // Clavier (Échap pour fermer)
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          closeDeleteModal();
        }
      });
    </script>
</body>
</html>
    <?php
}
