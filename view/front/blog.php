<?php
/**
 * Page du Blog - Front Office
 */

$pageTitle = "Blog & Actu - EcoByte";
require __DIR__ . '/layout_header.php'; 
?>

<style>
    .blog-hero {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        color: white;
        padding: 60px 0;
        text-align: center;
        border-radius: 0 0 50px 50px;
        margin-bottom: 50px;
    }
    .post-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        transition: transform 0.3s ease;
        margin-bottom: 30px;
    }
    .post-card:hover {
        transform: translateY(-5px);
    }
    .post-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .post-content {
        padding: 25px;
    }
    .post-category {
        display: inline-block;
        padding: 4px 12px;
        background: #ecfeff;
        color: #0e7490;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-transform: uppercase;
    }
    .post-title {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 15px;
    }
    .btn-read {
        color: #06b6d4;
        font-weight: 700;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .search-section {
        background: white;
        padding: 20px;
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-top: -50px;
        position: relative;
        z-index: 2;
    }
</style>

<div class="blog-hero">
    <div class="container">
        <h1 class="fw-800 display-4">Blog & Communauté 🌱</h1>
        <p class="lead opacity-75">Restez informé des dernières tendances en nutrition et bien-être.</p>
    </div>
</div>

<div class="container">
    <div class="search-section mb-5">
        <form class="row g-3">
            <div class="col-md-8">
                <input type="text" class="form-control rounded-pill px-4" placeholder="Rechercher un article...">
            </div>
            <div class="col-md-4">
                <select class="form-select rounded-pill px-4">
                    <option value="">Toutes les catégories</option>
                    <option value="nutrition">Nutrition</option>
                    <option value="sport">Sport</option>
                    <option value="sante">Santé</option>
                </select>
            </div>
        </form>
    </div>

    <div class="row">
        <!-- Mockup of posts to show the style -->
        <div class="col-md-4">
            <div class="post-card">
                <img src="view/front/images/post-thumb-1.jpg" class="post-img" alt="Post">
                <div class="post-content">
                    <span class="post-category">Nutrition</span>
                    <h4 class="post-title">Les bienfaits du régime méditerranéen</h4>
                    <p class="text-muted small">Découvrez pourquoi ce régime est considéré comme l'un des plus sains au monde...</p>
                    <a href="#" class="btn-read">Lire la suite <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="post-card">
                <img src="view/front/images/post-thumb-2.jpg" class="post-img" alt="Post">
                <div class="post-content">
                    <span class="post-category">Sport</span>
                    <h4 class="post-title">Comment rester motivé en hiver</h4>
                    <p class="text-muted small">Conseils pratiques pour maintenir votre routine sportive malgré le froid...</p>
                    <a href="#" class="btn-read">Lire la suite <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="post-card">
                <img src="view/front/images/post-thumb-3.jpg" class="post-img" alt="Post">
                <div class="post-content">
                    <span class="post-category">Santé</span>
                    <h4 class="post-title">Comprendre les allergies saisonnières</h4>
                    <p class="text-muted small">Guide complet pour identifier et prévenir les réactions allergiques au printemps...</p>
                    <a href="#" class="btn-read">Lire la suite <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require __DIR__ . '/layout_footer.php'; 
?>
