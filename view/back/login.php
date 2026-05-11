<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$error = '';

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: posts.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    if (hash_equals(ECOBYTE_ADMIN_PASSWORD, $password)) {
        $_SESSION['role'] = 'admin';
        header('Location: posts.php');
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
    <title>Connexion Admin — EcoByte</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Open Sans', sans-serif; background-color: #1a1a2e; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">
    <div class="w-full max-w-md p-8 glass rounded-3xl shadow-2xl">
        <div class="text-center mb-8">
            <span class="text-4xl mb-4 block">🔐</span>
            <h1 class="text-2xl font-bold text-white">Espace Administration</h1>
            <p class="text-slate-400 text-sm mt-2">Authentification requise pour gérer le blog</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-6 p-4 text-sm text-red-200 bg-red-900/30 rounded-xl border border-red-500/50">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="space-y-6">
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-300 mb-2">Mot de passe</label>
                <input type="password" id="password" name="password" 
                       class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                       placeholder="••••••••" required autofocus>
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg shadow-blue-900/20 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                Se connecter
            </button>
        </form>

    </div>
</body>
</html>
