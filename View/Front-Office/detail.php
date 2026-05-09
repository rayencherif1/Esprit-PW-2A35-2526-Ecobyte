<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/allergie.Controller.php';
require_once __DIR__ . '/../../Controller/traitement.Controller.php';

$id_allergie = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_allergie <= 0) {
    header('Location: front_allergies.php');
    exit();
}

$allergieController = new AllergieC();
$allergie = $allergieController->getAllergieById($id_allergie);

if (!$allergie) {
    header('Location: front_allergies.php');
    exit();
}

$traitementController = new TraitementC();
$traitements = $traitementController->listTraitementByAllergie($id_allergie);

if (!is_array($traitements)) {
    $traitements = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($allergie['nom']) ?> - Détails</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .modal-partage {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .modal-partage.active {
            display: flex;
        }
        .modal-partage-content {
            background: white;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-partage-header {
            background: #10b981;
            color: white;
            padding: 20px;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-partage-body {
            padding: 25px;
        }
        .modal-partage-body input,
        .modal-partage-body textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .btn-envoyer {
            background: #10b981;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 10px;
            width: 100%;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-annuler {
            background: #e5e7eb;
            color: #374151;
            padding: 12px;
            border: none;
            border-radius: 10px;
            width: 100%;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 10px;
            color: white;
            z-index: 10000;
        }
        .toast.success { background: #10b981; }
        .toast.error { background: #ef4444; }
    </style>
</head>
<body class="bg-gray-100">

<?php if (isset($_SESSION['email_success'])): ?>
    <div class="toast success">✅ <?= $_SESSION['email_success'] ?></div>
    <?php unset($_SESSION['email_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['email_error'])): ?>
    <div class="toast error">❌ <?= $_SESSION['email_error'] ?></div>
    <?php unset($_SESSION['email_error']); ?>
<?php endif; ?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    
    <!-- Carte allergie avec BOUTON PARTAGER -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-white">🌿 <?= htmlspecialchars($allergie['nom']) ?></h1>
            
            <!-- BOUTON PARTAGER - UN SEUL POUR TOUTE L'ALLERGIE -->
            <button onclick="ouvrirModalPartage()" 
                    class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-5 rounded-lg transition flex items-center gap-2 shadow-md">
                📧 Partager cette allergie
            </button>
        </div>
        
        <div class="p-6">
            <div class="mb-4">
                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold 
                    <?php 
                        $g = strtolower($allergie['gravite'] ?? '');
                        if($g == 'grave') echo 'bg-red-100 text-red-700';
                        elseif($g == 'moyenne') echo 'bg-orange-100 text-orange-700';
                        else echo 'bg-green-100 text-green-700';
                    ?>">
                    ⚠️ Gravité : <?= htmlspecialchars($allergie['gravite'] ?? 'Non définie') ?>
                </span>
            </div>
            
            <div class="mb-4">
                <h2 class="font-bold text-lg text-gray-700">📝 Description</h2>
                <p class="text-gray-600 mt-1"><?= htmlspecialchars($allergie['description'] ?? 'Aucune description') ?></p>
            </div>
            
            <div class="mb-4">
                <h2 class="font-bold text-lg text-gray-700">🤧 Symptômes</h2>
                <p class="text-gray-600 mt-1"><?= htmlspecialchars($allergie['symptomes'] ?? 'Aucun symptôme') ?></p>
            </div>
        </div>
    </div>

    <!-- Section traitements -->
    <h2 class="text-2xl font-bold text-gray-800 mt-8 mb-4">💊 Traitements disponibles</h2>
    
    <?php if (count($traitements) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php foreach ($traitements as $t): ?>
                <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">💊 <?= htmlspecialchars($t['nom_traitement']) ?></h3>
                    <?php if (!empty($t['conseils'])): ?>
                        <p class="text-gray-600 text-sm mb-2"><span class="font-semibold text-green-600">📌 Conseils :</span> <?= htmlspecialchars($t['conseils']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($t['interdiction'])): ?>
                        <p class="text-gray-600 text-sm"><span class="font-semibold text-red-600">🚫 Interdictions :</span> <?= htmlspecialchars($t['interdiction']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl p-8 text-center">
            <p class="text-gray-500">Aucun traitement disponible pour cette allergie.</p>
        </div>
    <?php endif; ?>

    <div class="mt-8 text-center">
        <a href="front_allergies.php" class="text-blue-600 hover:text-blue-800">← Retour à la liste des allergies</a>
    </div>
</div>

<!-- MODALE DE PARTAGE -->
<div id="modalPartage" class="modal-partage">
    <div class="modal-partage-content">
        <div class="modal-partage-header">
            <h3 class="text-xl font-bold">📧 Partager cette allergie</h3>
            <button onclick="fermerModalPartage()" style="background:none; border:none; color:white; font-size:28px; cursor:pointer;">&times;</button>
        </div>
        <div class="modal-partage-body">
            <form method="POST" action="send_allergie_email.php">
                <input type="hidden" name="id_allergie" value="<?= $id_allergie ?>">
                
                <div style="background:#f0fdf4; padding:12px; border-radius:10px; margin-bottom:15px;">
                    <strong>🌿 Allergie :</strong> <?= htmlspecialchars($allergie['nom']) ?>
                </div>
                
                <input type="text" name="nom_proche" required 
                       placeholder="Votre prénom ou nom *">
                
                <input type="email" name="email_destinataire" required 
                       placeholder="Email du destinataire *">
                
                <textarea name="message_perso" rows="3" 
                          placeholder="Message personnel (optionnel)"></textarea>
                
                <button type="submit" class="btn-envoyer">
                    📤 Envoyer l'email
                </button>
                <button type="button" class="btn-annuler" onclick="fermerModalPartage()">
                    Annuler
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function ouvrirModalPartage() {
        document.getElementById('modalPartage').classList.add('active');
    }
    
    function fermerModalPartage() {
        document.getElementById('modalPartage').classList.remove('active');
    }
    
    window.onclick = function(event) {
        let modal = document.getElementById('modalPartage');
        if (event.target === modal) {
            fermerModalPartage();
        }
    }
    
    setTimeout(() => {
        document.querySelectorAll('.toast').forEach(t => t.remove());
    }, 4000);
</script>

</body>
</html>