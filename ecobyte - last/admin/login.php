<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$error = '';

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: ../view/back%20office/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    if (hash_equals(ECOBYTE_ADMIN_PASSWORD, $password)) {
        $_SESSION['role'] = 'admin';
        header('Location: ../view/back%20office/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/index.html');
        exit;
    }
    $error = 'Mot de passe incorrect.';
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion admin — Ecobyte</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 28px; width: 100%; max-width: 360px; }
        h1 { font-size: 1.25rem; margin: 0 0 20px; }
        label { display: block; font-size: 0.875rem; margin-bottom: 6px; color: #94a3b8; }
        input[type="password"] { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #475569; background: #0f172a; color: #f8fafc; font-size: 1rem; }
        button { margin-top: 16px; width: 100%; padding: 10px; border: none; border-radius: 8px; background: #3b82f6; color: #fff; font-weight: 600; cursor: pointer; font-size: 1rem; }
        button:hover { background: #2563eb; }
        .err { background: #7f1d1d; color: #fecaca; padding: 10px; border-radius: 8px; font-size: 0.875rem; margin-bottom: 16px; }
        a { color: #93c5fd; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Administration — Blog</h1>
        <?php if ($error !== '') { ?>
            <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } ?>
        <form method="post" action="">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" autocomplete="current-password" autofocus>
            <button type="submit">Se connecter</button>
        </form>
        <p style="margin-top: 20px;"><a href="../blog.php">← Retour au blog</a></p>
    </div>
</body>
</html>
