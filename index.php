<?php
<<<<<<< HEAD
/**
 * Point d'entrée principal - Routeur de l'application
 * Gère le routage avec $_GET['action'] et $_GET['section']
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}




require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ProfilController.php';
require_once __DIR__ . '/controllers/AdminProfileController.php';

// Mettre à jour la date de dernière activité si l'utilisateur est connecté (Front ou Back)
$activityController = new UserController();
if (isset($_SESSION['user_id'])) {
    $activityController->updateLastActivity($_SESSION['user_id']);
}
if (isset($_SESSION['admin_id'])) {
    $activityController->updateLastActivity($_SESSION['admin_id']);
}

// Récupérer la section (front ou back)
$section = isset($_GET['section']) ? $_GET['section'] : 'back';  // Par défaut: back office (admin)

// Récupérer l'action demandée (par défaut: users)
$action = isset($_GET['action']) ? $_GET['action'] : 'users';

// Initialiser les variables pour les vues
$errors = [];
$success = '';
$users = [];
$user = null;

try {
    // Créer un contrôleur pour récupérer tous les profils
    $profilController = new ProfilController();
    $profils = [];
    
    if ($section === 'back') {
        // ========== BACK OFFICE (ADMIN) ==========
        
        switch ($action) {
            case 'users':
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=front&action=sign-in');
                    exit;
                }
                $userController = new UserController();
                $search = $_GET['search'] ?? null;
                $sort = $_GET['sort'] ?? 'date_creation';
                $order = $_GET['order'] ?? 'DESC';
                
                $users = $userController->listUsers($search, $sort, $order);
                
                if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
                    require __DIR__ . '/view/back/users_list_partial.php';
                    exit;
                }
                
                require __DIR__ . '/view/back/users.php';
                break;

            case 'exportPDF':
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=front&action=sign-in');
                    exit;
                }
                $userController = new UserController();
                $search = $_GET['search'] ?? null;
                $sort = $_GET['sort'] ?? 'date_creation';
                $order = $_GET['order'] ?? 'DESC';
                
                $users = $userController->listUsers($search, $sort, $order);
                require __DIR__ . '/view/back/export-users-pdf.php';
                break;

            case 'addUser':
            case 'editUser':
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=front&action=sign-in');
                    exit;
                }
                
                $userController = new UserController();
                $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
                $user = $userId ? $userController->getUser($userId) : null;

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if ($userId) {
                        $result = $userController->updateUser($userId, $_POST);
                    } else {
                        $result = $userController->createUser($_POST);
                    }

                    if ($result) {
                        header('Location: ?section=back&action=users');
                        exit;
                    } else {
                        $errors = $userController->getErrors();
                        require __DIR__ . '/view/back/add-user.php';
                    }
                } else {
                    require __DIR__ . '/view/back/add-user.php';
                }
                break;

            case 'deleteUser':
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=front&action=sign-in');
                    exit;
                }
                $userController = new UserController();
                $userController->deleteUser((int)$_GET['id']);
                header('Location: ?section=back&action=users');
                exit;

            case 'banUser':
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=front&action=sign-in');
                    exit;
                }
                $userController = new UserController();
                $userController->banUser((int)$_GET['id'], $_GET['duration'] ?? '1d');
                header('Location: ?section=back&action=users');
                exit;

            case 'logout':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $userController = new UserController();
                    $userController->logout(true); // admin
                }
                header('Location: ?section=front&action=sign-in');
                exit;

            case 'sign-in':
                // Traitement connexion admin
                $errors = [];
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $userController = new UserController();
                    $email = trim($_POST['email'] ?? '');
                    $password = trim($_POST['password'] ?? '');
                    $user = $userController->login($email, $password);
                    if ($user && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                        header('Location: ?section=back&action=users');
                        exit;
                    } else {
                        // Détruire toute session admin créée par erreur
                        if (isset($_SESSION['admin_logged_in'])) {
                            $userController->logout(true);
                        }
                        $errors = $userController->getErrors();
                        if (empty($errors)) {
                            $errors[] = "Accès refusé : identifiants invalides ou compte non administrateur.";
                        }
                    }
                }
                require __DIR__ . '/view/back/sign-in.php';
                exit;

            case 'home':
            default:
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=back&action=sign-in');
                    exit;
                }
                header('Location: ?section=back&action=users');
                exit;
        }
    } else {
        // ========== FRONT OFFICE (PUBLIC) ==========
        
        switch ($action) {
            case 'sign-in':
                // Page de connexion client
                require __DIR__ . '/view/front/sign-in.php';
                break;

            case 'signup':
                // Page d'inscription client
                require __DIR__ . '/view/front/signup.php';
                break;

            case 'activate':
                // Page d'activation
                require __DIR__ . '/view/front/activate.php';
                break;

            case 'forgot-password':
                // Page mot de passe oublié
                require __DIR__ . '/view/front/forgot-password.php';
                break;

            case 'verify-code':
                // Page de vérification du code
                require __DIR__ . '/view/front/verify-code.php';
                break;

            case 'change-password':
                // Page de changement de mot de passe
                require __DIR__ . '/view/front/change-password.php';
                break;

            case 'get-face-descriptors':
                header('Content-Type: application/json');
                $userController = new UserController();
                $descriptors = $userController->getAllFaceDescriptors();
                $result = [];
                foreach ($descriptors as $d) {
                    $arr = json_decode($d['webauthn_public_key'], true);
                    if (is_array($arr)) {
                        $result[] = [
                            'userId' => $d['id'],
                            'nom' => $d['prenom'] . ' ' . $d['nom'],
                            'descriptor' => $arr
                        ];
                    }
                }
                echo json_encode($result);
                exit;

            case 'webauthn-login':
                header('Content-Type: application/json');
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    $userId = $input['userId'] ?? null;
                    
                    if ($userId) {
                        $userController = new UserController();
                        $user = $userController->loginWithFaceId($userId);
                        if ($user) {
                            // Redirection basée sur le rôle de l'utilisateur connecté (jamais admin via Face ID)
                            $redirect = '?section=front&action=home';
                            echo json_encode(['success' => true, 'redirect' => $redirect]);
                            exit;
                        } else {
                            echo json_encode(['success' => false, 'message' => $userController->getErrors()[0] ?? 'Erreur']);
                            exit;
                        }
                    }
                }
                echo json_encode(['success' => false, 'message' => 'Requête invalide']);
                exit;

            case 'webauthn-register':
                header('Content-Type: application/json');
                if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
                    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
                    exit;
                }
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    $descriptor = $input['descriptor'] ?? null;
                    
                    if ($descriptor) {
                        $userController = new UserController();
                        if ($userController->registerFaceDescriptor($_SESSION['user_id'], json_encode($descriptor))) {
                            echo json_encode(['success' => true]);
                            exit;
                        }
                    }
                }
                echo json_encode(['success' => false, 'message' => 'Erreur d\'enregistrement']);
                exit;

            case 'logout':
                // Déconnexion Front Office (uniquement via POST pour éviter le prefetch)
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $userController = new UserController();
                    $userController->logout(false); // false = user
                }
                header('Location: ?section=front&action=home');
                exit;

            case 'profile':
                // Page de profil utilisateur
                require __DIR__ . '/view/front/profile.php';
                break;

            case 'kitchen':
                // Module Cuisine
                require __DIR__ . '/view/front/front.php';
                break;

            case 'fitness':
                // Module Fitness
                header('Location: /2int/public/index.php?action=home');
                exit;

            case 'health':
                // Module Santé
                require __DIR__ . '/view/front/allergy_report.php';
                break;

            case 'shop':
                // Redirection vers le Hub car la boutique est supprimée ou intégrée
                header('Location: ?section=front&action=home');
                exit;

            case 'blog':
                // Page Blog & Communauté
                require __DIR__ . '/view/front/blog.php';
                break;

            case 'ai':
                // Assistant IA
                require __DIR__ . '/view/front/chatbot.php';
                break;

            case 'home':
            default:
                // Hub principal
                require __DIR__ . '/view/front/hub.php';
                break;
        }
    }
} catch (Exception $e) {
    // Gestion des erreurs non prévues
    $errors[] = htmlspecialchars($e->getMessage());
    if ($section === 'back') {
        require __DIR__ . '/view/back/error.php';
    } else {
        require __DIR__ . '/view/front/error.php';
    }
}
?>
=======
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoByte — Hub Nutrition & Santé</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: #f0f4f8; min-height: 100vh; }

        /* ── HEADER ──────────────────────────────────────────────── */
        header {
            background: #fff;
            padding: 16px 40px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            position: sticky; top: 0; z-index: 100;
        }
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; font-size: 1.5rem; font-weight: 800; color: #1a1a2e; }
        .logo-icon { font-size: 1.8rem; }
        .logo span.eco  { color: #4caf50; }
        .logo span.byte { color: #ff6b35; }
        .header-right { display: flex; align-items: center; gap: 16px; }
        .avatar {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #4caf50, #2196f3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 1rem;
            text-decoration: none; cursor: pointer;
            box-shadow: 0 2px 8px rgba(33,150,243,0.3);
            transition: transform .2s;
        }
        .avatar:hover { transform: scale(1.1); }

        /* ── HERO ────────────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: white; text-align: center; padding: 60px 20px 80px;
            position: relative; overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 30% 50%, rgba(76,175,80,0.15) 0%, transparent 60%),
                        radial-gradient(circle at 70% 50%, rgba(255,107,53,0.15) 0%, transparent 60%);
        }
        .hero-content { position: relative; z-index: 1; }
        .hero h1 { font-size: 2.8rem; font-weight: 800; margin-bottom: 14px; line-height: 1.2; }
        .hero h1 .accent { color: #4caf50; }
        .hero p { font-size: 1.1rem; opacity: 0.8; max-width: 560px; margin: 0 auto; line-height: 1.6; }

        /* ── MODULE GRID ─────────────────────────────────────────── */
        .modules-section { max-width: 1100px; margin: -40px auto 60px; padding: 0 24px; }
        .modules-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

        .module-card {
            background: #fff;
            border-radius: 20px; padding: 36px 28px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative; overflow: hidden;
            border: 2px solid transparent;
            display: flex; flex-direction: column;
        }
        .module-card:hover { transform: translateY(-6px); box-shadow: 0 14px 40px rgba(0,0,0,0.13); }

        .module-icon-wrap {
            width: 72px; height: 72px; border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem; margin: 0 auto 20px;
        }
        .module-card h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 10px; }
        .module-card p { font-size: 0.875rem; color: #6b7280; line-height: 1.6; margin-bottom: 24px; flex-grow: 1; }

        .btn-module {
            display: inline-block; padding: 12px 28px; border-radius: 50px;
            font-size: 0.9rem; font-weight: 600; text-decoration: none;
            color: white; width: 100%; text-align: center;
            transition: all 0.25s ease; cursor: pointer;
        }
        .btn-module:hover { transform: scale(1.03); color: white; }

        /* Colors per module */
        #card-cuisine { border-color: #86efac; background: #f0fdf4; }
        #card-cuisine .module-icon-wrap { background: #bbf7d0; }
        #btn-cuisine { background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 4px 14px rgba(34,197,94,0.3); }

        #card-fitness { border-color: #ff8c42; background: #fffaf5; }
        #card-fitness .module-icon-wrap { background: #fff3ee; }
        #btn-fitness { background: linear-gradient(135deg, #ff6b35, #ff8c42); box-shadow: 0 4px 14px rgba(255,107,53,0.3); }

        #card-sante { border-color: #fca5a5; background: #fff1f2; }
        #card-sante .module-icon-wrap { background: #fecdd3; }
        #btn-sante { background: linear-gradient(135deg, #f43f5e, #e11d48); box-shadow: 0 4px 14px rgba(244,63,94,0.3); }

        #card-boutique { border-color: #93c5fd; background: #eff6ff; }
        #card-boutique .module-icon-wrap { background: #bfdbfe; }
        #btn-boutique { background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 4px 14px rgba(59,130,246,0.3); }

        #card-blog { border-color: #67e8f9; background: #ecfeff; }
        #card-blog .module-icon-wrap { background: #a5f3fc; }
        #btn-blog { background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 4px 14px rgba(6,182,212,0.3); }

        #card-ia { border-color: #c4b5fd; background: #faf5ff; }
        #card-ia .module-icon-wrap { background: #ddd6fe; }
        #btn-ia { background: linear-gradient(135deg, #8b5cf6, #7c3aed); box-shadow: 0 4px 14px rgba(139,92,246,0.3); }

        .status-bar { background: #1a1a2e; color: #aaa; text-align: center; padding: 20px; font-size: 0.8rem; }
        .status-item { display: inline-flex; align-items: center; gap: 6px; margin: 0 10px; }
        .dot { width: 8px; height: 8px; border-radius: 50%; background: #4caf50; }

        @media (max-width:900px) { .modules-grid { grid-template-columns: repeat(2,1fr); } }
        @media (max-width:600px) { .modules-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<header>
    <a href="/2int/index.php" class="logo">
        <span class="logo-icon">🌿</span>
        <span class="eco">ECO</span><span class="byte">BYTE</span>
    </a>
    <div class="header-right">
        <a href="#" class="avatar">U</a>
    </div>
</header>

<div class="hero">
    <div class="hero-content">
        <h1>Bienvenue sur <span class="accent">EcoByte</span> 🌱</h1>
        <p>Votre plateforme tout-en-un pour la nutrition, la santé, le sport et bien plus.</p>
    </div>
</div>

<div class="modules-section">
    <div class="modules-grid">
        <!-- CUISINE -->
        <div class="module-card" id="card-cuisine">
            <div class="module-icon-wrap">🥗</div>
            <h3>Cuisine & Recettes</h3>
            <p>Découvrez des centaines de recettes saines adaptées à vos besoins.</p>
            <a href="/2int/view/front/front.php" class="btn-module" id="btn-cuisine">Explorer</a>
        </div>

        <!-- FITNESS -->
        <div class="module-card" id="card-fitness">
            <div class="module-icon-wrap">🏋️</div>
            <h3>Fitness & Sport</h3>
            <p>Programmes d'entraînement personnalisés et suivi d'exercices.</p>
            <a href="/2int/public/index.php?action=home" class="btn-module" id="btn-fitness">Sport →</a>
        </div>

        <!-- SANTE -->
        <div class="module-card" id="card-sante">
            <div class="module-icon-wrap">⚠️</div>
            <h3>Santé & Allergies</h3>
            <p>Gérez vos allergies et analysez vos aliments avec l'IA.</p>
            <a href="/2int/view/front/allergy_report.php" class="btn-module" id="btn-sante">Rapports</a>
        </div>

        <!-- BOUTIQUE -->
        <div class="module-card" id="card-boutique">
            <div class="module-icon-wrap">🛒</div>
            <h3>Boutique Bio</h3>
            <p>Achetez des produits frais et sains directement en ligne.</p>
            <a href="/2int/boutique.php" class="btn-module" id="btn-boutique">Boutique</a>
        </div>

        <!-- BLOG -->
        <div class="module-card" id="card-blog">
            <div class="module-icon-wrap">📝</div>
            <h3>Communauté & Blog</h3>
            <p>Partagez vos astuces et lisez les derniers articles nutritionnels.</p>
            <a href="/2int/view/front/blog.php" class="btn-module" id="btn-blog">Voir le Blog</a>
        </div>

        <!-- IA -->
        <div class="module-card" id="card-ia">
            <div class="module-icon-wrap">🤖</div>
            <h3>IA Assistant</h3>
            <p>Posez vos questions à notre IA pour des conseils instantanés.</p>
            <a href="/2int/view/front/chatbot.php" class="btn-module" id="btn-ia">Discuter</a>
        </div>
    </div>
</div>

<footer class="status-bar">
    <div class="status-item"><div class="dot"></div> Blog — En ligne</div>
    <div class="status-item"><div class="dot"></div> Cuisine — En ligne</div>
    <p>© 2026 EcoByte — Standardized Integration</p>
</footer>

</body>
</html>
>>>>>>> blog
