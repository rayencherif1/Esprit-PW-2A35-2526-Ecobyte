<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin - EcoBite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c5e2e 0%, #4a8b4c 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 28px;
            padding: 40px;
            width: 400px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .btn-login {
            background-color: #2c5e2e;
            color: white;
            border-radius: 40px;
            padding: 10px;
            width: 100%;
        }
        .btn-login:hover {
            background-color: #ffb347;
            color: #2c5e2e;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h2 class="fw-bold">🌿 EcoBite Admin</h2>
            <p class="text-muted">Accès réservé</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/marketplace/index.php?controller=auth&action=authenticate">
            <div class="mb-3">
                <label class="form-label">Identifiant</label>
                <input type="text" name="username" class="form-control rounded-pill" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control rounded-pill" required>
            </div>
            <button type="submit" class="btn btn-login mt-2">Se connecter</button>
        </form>
        
        <div class="text-center mt-3">
            <a href="/marketplace/view/front/index.php" class="text-decoration-none small">← Retour au site</a>
        </div>
    </div>
</body>
</html>