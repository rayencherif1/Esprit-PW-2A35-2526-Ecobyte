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
    </style>
</head>
<body>
    <div class="shell">
        <nav class="nav" aria-label="Administration">
            <a href="posts.php" class="<?= ($active === 'posts' || $active === 'edit') ? 'active' : '' ?>">Mes articles</a>
            <a href="add_post.php" class="<?= $active === 'add' ? 'active' : '' ?>">Nouvel article</a>
            <a href="replies.php" class="<?= $active === 'replies' ? 'active' : '' ?>">Réponses</a>
            <span class="spacer"></span>
            <a href="../blog.php">Voir le blog public</a>
        </nav>
    <?php
}

function admin_layout_end(): void
{
    ?>
    </div>
</body>
</html>
    <?php
}
