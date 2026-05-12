<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoByte — Hub Nutrition & Santé</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; min-height: 100vh; }

        /* HEADER */
        header {
            background: #fff;
            padding: 14px 60px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            position: sticky; top: 0; z-index: 1000;
        }
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; font-size: 1.4rem; font-weight: 800; color: #1a1a2e; }
        .logo span.eco  { color: #4caf50; }
        .logo span.byte { color: #ff6b35; }
        .avatar {
            width: 42px; height: 42px;
            background: #4db6ac;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 1rem;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* HERO */
        .hero {
            background: #1e293b;
            color: white; text-align: center; 
            padding: 80px 20px 140px;
            position: relative;
        }
        .hero h1 { font-size: 3.2rem; font-weight: 800; margin-bottom: 15px; }
        .hero h1 span { color: #4caf50; }
        .hero p { font-size: 1.1rem; color: #94a3b8; max-width: 600px; margin: 0 auto; line-height: 1.6; }

        /* MODULES SECTION */
        .modules-section { max-width: 1200px; margin: -80px auto 60px; padding: 0 20px; }
        .modules-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

        .card {
            background: #fff;
            border-radius: 30px; padding: 45px 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            border: 2px solid transparent;
            overflow: hidden;
        }
        .card:hover { transform: translateY(-12px); box-shadow: 0 20px 60px rgba(0,0,0,0.1); }

        .icon-box {
            width: 80px; height: 80px;
            margin: 0 auto 25px;
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem;
        }

        .card h3 { font-size: 1.3rem; font-weight: 700; margin-bottom: 15px; color: #1e293b; }
        .card p { font-size: 0.9rem; color: #64748b; line-height: 1.7; margin-bottom: 35px; min-height: 70px; }

        .btn {
            display: inline-block; width: 100%; padding: 16px; border-radius: 50px;
            font-size: 1rem; font-weight: 700; text-decoration: none;
            transition: all 0.3s ease;
        }

        /* CARD THEMES */
        /* Cuisine */
        .card-cuisine { border-color: #dcfce7; background: #f0fff4; }
        .card-cuisine .icon-box { background: #c6f6d5; }
        .card-cuisine .btn { background: #10b981; color: white; }
        .card-cuisine .btn:hover { background: #059669; }

        /* Fitness */
        .card-fitness { border-color: #ffedd5; }
        .card-fitness .icon-box { background: #ffedd5; }
        .card-fitness .btn { background: linear-gradient(135deg, #ff7e5f, #feb47b); color: white; }
        .card-fitness .btn:hover { opacity: 0.9; }

        /* Sante */
        .card-sante { border-color: #fee2e2; background: #fff5f5; }
        .card-sante .icon-box { background: #fed7d7; }
        .card-sante .btn { background: #f43f5e; color: white; }
        .card-sante .btn:hover { background: #e11d48; }

        /* Boutique */
        .card-boutique { border-color: #e0f2fe; background: #f0f9ff; }
        .card-boutique .icon-box { background: #bae6fd; }
        .card-boutique .btn { background: #0ea5e9; color: white; }

        /* Blog */
        .card-blog { border-color: #e0fffb; background: #f0fffd; }
        .card-blog .icon-box { background: #b2f5ea; }
        .card-blog .btn { background: #14b8a6; color: white; }

        /* IA */
        .card-ia { border-color: #f3e8ff; background: #faf5ff; }
        .card-ia .icon-box { background: #e9d5ff; }
        .card-ia .btn { background: #8b5cf6; color: white; }

        @media (max-width: 992px) { .modules-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .modules-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<header>
    <a href="/2int/index.php" class="logo">
        <span>🌿</span> <span class="eco">ECO</span> <span class="byte">BYTE</span>
    </a>
    <a href="#" class="avatar">U</a>
</header>

<div class="hero">
    <h1>Bienvenue sur <span>EcoByte</span> 🌱</h1>
    <p>Votre plateforme tout-en-un pour la nutrition, la santé, le sport et bien plus.</p>
</div>

<div class="modules-section">
    <div class="modules-grid">
        <!-- CUISINE -->
        <div class="card card-cuisine">
            <div class="icon-box">🥗</div>
            <h3>Cuisine & Recettes</h3>
            <p>Découvrez des centaines de recettes saines adaptées à vos besoins nutritionnels.</p>
            <a href="/2int/view/front/front.php" class="btn">Recettes</a>
        </div>

        <!-- FITNESS -->
        <div class="card card-fitness">
            <div class="icon-box">🏋️</div>
            <h3>Fitness & Sport</h3>
            <p>Programmes d'entraînement personnalisés et suivi d'exercices quotidiens.</p>
            <a href="/2int/public/index.php?action=home" class="btn">Catalogue Sport →</a>
        </div>

        <!-- SANTE -->
        <div class="card card-sante">
            <div class="icon-box">⚠️</div>
            <h3>Santé & Allergies</h3>
            <p>Gérez vos allergies, analysez vos aliments avec l'IA et restez en sécurité.</p>
            <a href="/2int/view/Front/allergy_report.php" class="btn">Rapports d'Allergies</a>
        </div>

        <!-- BOUTIQUE -->
        <div class="card card-boutique">
            <div class="icon-box">🛒</div>
            <h3>Boutique Bio</h3>
            <p>Achetez des produits frais, bio et sains directement depuis notre plateforme.</p>
            <a href="/2int/boutique.php" class="btn">Boutique</a>
        </div>

        <!-- BLOG -->
        <div class="card card-blog">
            <div class="icon-box">📝</div>
            <h3>Blog & Actu</h3>
            <p>Lisez les derniers articles sur la nutrition et partagez avec la communauté.</p>
            <a href="/2int/view/front/blog.php" class="btn">Blog</a>
        </div>

        <!-- IA -->
        <div class="card card-ia">
            <div class="icon-box">🤖</div>
            <h3>IA Assistant</h3>
            <p>Posez vos questions à notre IA pour obtenir des conseils nutritionnels instantanés.</p>
            <a href="/2int/view/Front/chatbot.php" class="btn">IA Assistant</a>
        </div>
    </div>
</div>

</body>
</html>
