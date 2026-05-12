<?php
/**
 * Hub EcoByte - Front Office
 */

$pageTitle = "EcoByte — Hub Nutrition & Santé";
require __DIR__ . '/layout_header.php'; 
?>

<style>
    .hero { background: #1e293b; color: white; text-align: center; padding: 80px 20px 140px; }
    .hero h1 { font-size: 3.2rem; font-weight: 800; margin-bottom: 15px; }
    .hero h1 span { color: #4caf50; }
    .hero p { font-size: 1.1rem; color: #94a3b8; max-width: 600px; margin: 0 auto; line-height: 1.6; }

    .modules-section { max-width: 1200px; margin: -80px auto 60px; padding: 0 20px; }
    .modules-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

    .card {
        background: #fff; border-radius: 30px; padding: 45px 30px;
        text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.04);
        transition: all 0.4s; position: relative; border: 2px solid transparent;
        display: flex; flex-direction: column; height: 100%;
        text-decoration: none; color: inherit;
    }
    .card:hover { transform: translateY(-12px); box-shadow: 0 20px 60px rgba(0,0,0,0.1); }

    .icon-box {
        width: 80px; height: 80px; margin: 0 auto 25px;
        border-radius: 20px; display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem;
    }

    .card h3 { font-size: 1.3rem; font-weight: 700; margin-bottom: 15px; color: #1e293b; }
    .card p { font-size: 0.9rem; color: #64748b; line-height: 1.7; margin-bottom: 35px; flex-grow: 1; }

    .btn-link {
        display: inline-block; width: 100%; padding: 16px; border-radius: 50px;
        font-size: 1rem; font-weight: 700; text-decoration: none;
        transition: all 0.3s ease; text-align: center;
        cursor: pointer;
    }

    /* CARD THEMES */
    .card-cuisine { border-color: #dcfce7; background: #f0fff4; }
    .card-cuisine .icon-box { background: #c6f6d5; }
    .card-cuisine .btn-link { background: #10b981; color: white; }

    .card-fitness { border-color: #ffedd5; background: #fffcf9; }
    .card-fitness .icon-box { background: #ffedd5; }
    .card-fitness .btn-link { background: linear-gradient(135deg, #ff7e5f, #feb47b); color: white; }

    .card-sante { border-color: #fee2e2; background: #fff5f5; }
    .card-sante .icon-box { background: #fed7d7; }
    .card-sante .btn-link { background: #f43f5e; color: white; }

    .card-boutique { border-color: #e0f2fe; background: #f0f9ff; }
    .card-boutique .icon-box { background: #bae6fd; }
    .card-boutique .btn-link { background: #0ea5e9; color: white; }

    .card-blog { border-color: #e0fffb; background: #f0fffd; }
    .card-blog .icon-box { background: #b2f5ea; }
    .card-blog .btn-link { background: #14b8a6; color: white; }

    .card-ia { border-color: #f3e8ff; background: #faf5ff; }
    .card-ia .icon-box { background: #e9d5ff; }
    .card-ia .btn-link { background: #8b5cf6; color: white; }

    @media (max-width: 992px) { .modules-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .modules-grid { grid-template-columns: 1fr; } }
</style>

<div class="hero">
    <h1>Bienvenue sur <span>EcoByte</span> 🌱</h1>
    <p>Votre plateforme tout-en-un pour la nutrition, la santé, le sport et bien plus.</p>
</div>

<div class="modules-section">
    <div class="modules-grid">
        <a href="index.php?section=front&action=kitchen" class="card card-cuisine">
            <div class="icon-box">🥗</div>
            <h3>Cuisine & Recettes</h3>
            <p>Découvrez des centaines de recettes saines adaptées à vos besoins nutritionnels.</p>
            <span class="btn-link">Explorer</span>
        </a>

        <a href="index.php?section=front&action=fitness" class="card card-fitness">
            <div class="icon-box">🏋️</div>
            <h3>Fitness & Sport</h3>
            <p>Programmes d'entraînement personnalisés et suivi d'exercices quotidiens.</p>
            <span class="btn-link">S'entraîner</span>
        </a>

        <a href="index.php?section=front&action=health" class="card card-sante">
            <div class="icon-box">⚠️</div>
            <h3>Santé & Allergies</h3>
            <p>Gérez vos allergies, analysez vos aliments avec l'IA et restez en sécurité.</p>
            <span class="btn-link">Vérifier</span>
        </a>

        <a href="index.php?section=front&action=shop" class="card card-boutique">
            <div class="icon-box">🛒</div>
            <h3>Boutique Bio</h3>
            <p>Achetez des produits frais, bio et sains directement depuis notre plateforme.</p>
            <span class="btn-link">Acheter</span>
        </a>

        <a href="index.php?section=front&action=blog" class="card card-blog">
            <div class="icon-box">📝</div>
            <h3>Blog & Actu</h3>
            <p>Lisez les derniers articles sur la nutrition et partagez avec la communauté.</p>
            <span class="btn-link">Lire</span>
        </a>

        <a href="index.php?section=front&action=ai" class="card card-ia">
            <div class="icon-box">🤖</div>
            <h3>IA Assistant</h3>
            <p>Posez vos questions à notre IA pour obtenir des conseils nutritionnels instantanés.</p>
            <span class="btn-link">Discuter</span>
        </a>
    </div>
</div>

<?php require __DIR__ . '/layout_footer.php'; ?>
