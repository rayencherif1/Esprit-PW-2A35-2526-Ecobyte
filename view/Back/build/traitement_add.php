<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/traitement.php';
require_once __DIR__ . '/../../../Controller/traitement.Controller.php';
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';

session_start();

$errors = [];
$id_allergie = $_GET['id_allergie'] ?? null;

$allergieController = new AllergieC();
$traitementController = new TraitementC();

// Charger toutes les allergies pour la liste déroulante si besoin
$allAllergies = $allergieController->listAllergie();

// Vérifier si l'ID passé existe
if ($id_allergie) {
    $allergieInfo = $allergieController->getAllergieById($id_allergie);
    if (!$allergieInfo) {
        $id_allergie = null; // Reset si l'ID n'existe pas
    }
}

// Générer token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$old = [
    'nom_traitement' => '',
    'posologie' => '',
    'duree' => '',
    'id_allergie' => $id_allergie
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['general'] = "Token de sécurité invalide";
    } else {
        $nom_traitement = trim($_POST['nom_traitement'] ?? '');
        $posologie = trim($_POST['posologie'] ?? '');
        $duree = trim($_POST['duree'] ?? '');
        $post_id_allergie = $_POST['id_allergie'] ?? $id_allergie;

        $old = compact('nom_traitement', 'posologie', 'duree', 'post_id_allergie');

        if (empty($nom_traitement)) $errors['nom_traitement'] = "Obligatoire";
        if (empty($post_id_allergie)) $errors['id_allergie'] = "Veuillez sélectionner une allergie";

        if (empty($errors)) {
            try {
                $traitement = new Traitement(null, $nom_traitement, $posologie, $duree, $post_id_allergie);
                if ($traitementController->addTraitement($traitement)) {
                    header("Location: traitement_list.php?success=added&id_allergie=" . $post_id_allergie);
                    exit();
                }
            } catch (Exception $e) {
                $errors['general'] = $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Traitement - EcoByte</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="m-0 font-sans text-base antialiased font-normal leading-default bg-gray-50 text-slate-500">
    
    <?php include 'sidebar.php'; ?>

    <main class="relative h-full max-h-screen transition-all duration-200 lg:ml-72 rounded-xl">
        <div class="absolute w-full bg-[#5e72e4] min-h-75 -z-10"></div>

        <div class="w-full px-10 py-10 mx-auto">
            <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl rounded-2xl bg-clip-border p-8">
                <div class="flex justify-between items-center mb-8 border-b pb-4">
                    <div>
                        <h2 class="text-slate-700 font-bold text-xl mb-1">Nouveau Traitement</h2>
                        <p class="text-slate-400 text-sm font-semibold">Associer une solution thérapeutique</p>
                    </div>
                    <a href="traitement_list.php<?= $id_allergie ? '?id_allergie='.$id_allergie : '' ?>" class="text-sm font-bold text-blue-600 hover:underline">
                        ← Retour
                    </a>
                </div>

                <form method="POST" class="max-w-2xl">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <?php if (isset($errors['general'])): ?>
                        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?= $errors['general'] ?></div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Allergie concernée</label>
                        <?php if ($id_allergie): ?>
                            <input type="hidden" name="id_allergie" value="<?= $id_allergie ?>">
                            <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg text-blue-700 font-semibold">
                                <?= htmlspecialchars($allergieInfo['nom'] ?? $allergieInfo['nom_allergie']) ?>
                            </div>
                        <?php else: ?>
                            <select name="id_allergie" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">-- Sélectionner une allergie --</option>
                                <?php foreach ($allAllergies as $all): ?>
                                    <option value="<?= $all['id_allergie'] ?>"><?= htmlspecialchars($all['nom'] ?? $all['nom_allergie']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nom du traitement</label>
                        <input type="text" name="nom_traitement" value="<?= htmlspecialchars($old['nom_traitement']) ?>" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Ex: Antihistaminique X">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Posologie</label>
                        <textarea name="posologie" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" rows="3"><?= htmlspecialchars($old['posologie']) ?></textarea>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Durée estimée</label>
                        <input type="text" name="duree" value="<?= htmlspecialchars($old['duree']) ?>" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Ex: 7 jours">
                    </div>

                    <button type="submit" class="bg-[#5e72e4] text-white px-8 py-3 rounded-lg font-bold shadow-md hover:-translate-y-px transition-all">
                        Enregistrer le traitement
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>