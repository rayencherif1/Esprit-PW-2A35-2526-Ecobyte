<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/traitement.php';
require_once __DIR__ . '/../../../Controller/traitement.Controller.php';

$controller = new TraitementC();
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: traitement_list.php');
    exit();
}

$traitementData = $controller->getTraitementById($id);
if (!$traitementData) {
    header('Location: traitement_list.php');
    exit();
}

$id_allergie = $_GET['id_allergie'] ?? $traitementData['id_allergie'] ?? null;

// Stocker les valeurs actuelles
$old = [
    'nom_traitement' => $traitementData['nom_traitement'],
    'conseils' => $traitementData['conseils'],
    'interdiction' => $traitementData['interdiction'],
    'id_allergie' => $traitementData['id_allergie']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nom_traitement = trim($_POST['nom_traitement'] ?? '');
    $conseils = trim($_POST['conseils'] ?? '');
    $interdiction = trim($_POST['interdiction'] ?? '');
    $new_id_allergie = trim($_POST['id_allergie'] ?? $traitementData['id_allergie']);

    // Mettre à jour les valeurs pour l'affichage
    $old = compact('nom_traitement', 'conseils', 'interdiction', 'new_id_allergie');

    // Validation Nom
    if (empty($nom_traitement)) {
        $errors['nom_traitement'] = "Le nom du traitement est obligatoire.";
    } elseif (mb_strlen($nom_traitement) < 3) {
        $errors['nom_traitement'] = "Le nom doit contenir au moins 3 caractères.";
    }

    // Validation Conseils
    if (empty($conseils)) {
        $errors['conseils'] = "Les conseils sont obligatoires.";
    }

    // Validation Interdictions
    if (empty($interdiction)) {
        $errors['interdiction'] = "Les interdictions sont obligatoires.";
    }

    // Validation ID Allergie
    if (!empty($new_id_allergie) && !is_numeric($new_id_allergie)) {
        $errors['id_allergie'] = "L'ID de l'allergie doit être un nombre valide.";
    }

    if (empty($errors)) {
        try {
            $traitement = new Traitement($id, $nom_traitement, $conseils, $interdiction, $new_id_allergie);
            $result = $controller->updateTraitement($traitement, $id);
            
            if ($result) {
                $redirect = 'traitement_list.php?success=updated';
                if ($id_allergie) {
                    $redirect .= '&id_allergie=' . urlencode($id_allergie);
                }
                header('Location: ' . $redirect);
                exit();
            } else {
                $errors['general'] = "Erreur lors de la modification du traitement.";
            }
        } catch (Exception $e) {
            $errors['general'] = "Une erreur est survenue : " . $e->getMessage();
        }
    }
}
?>

<head>
    <meta charset="UTF-8">
    <title>Modifier Traitement - EcoByte Santé</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="https://demos.creative-tim.com/argon-dashboard-tailwind/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-tailwind/assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="m-0 font-sans text-base antialiased font-normal leading-default bg-gray-50 text-slate-500">
    
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="relative h-full max-h-screen transition-all duration-200 lg:ml-72 rounded-xl">
        <!-- Blue Header Background (Indigo-600) -->
        <div class="absolute w-full bg-[#5e72e4] min-h-75 -z-10"></div>

        <div class="w-full px-10 py-10 mx-auto">
            
            <!-- Main Content Card -->
            <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl rounded-2xl bg-clip-border p-8">
                <div class="flex justify-between items-center mb-8 border-b pb-4">
                    <div>
                        <h2 class="text-slate-700 font-bold text-xl mb-1">Modifier le Traitement</h2>
                        <p class="text-slate-400 text-sm font-semibold italic">Édition : <?= htmlspecialchars($old['nom_traitement']) ?></p>
                    </div>
                    <a href="traitement_list.php?id_allergie=<?= urlencode($id_allergie) ?>" class="text-sm font-bold text-blue-600 hover:underline">
                        ← Retour aux traitements
                    </a>
                </div>

            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl rounded-2xl bg-clip-border">
                        
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent text-center lg:text-left">
                            <h5 class="mb-1 font-bold text-slate-700">✏️ Modifier : <?= htmlspecialchars($old['nom_traitement']) ?></h5>
                            <p class="text-sm leading-normal text-slate-500 font-semibold italic">ID Dossier : #<?= htmlspecialchars($id) ?></p>
                        </div>

                        <div class="flex-auto p-6">
                            <?php endif; ?>
                            <form method="POST" class="max-w-3xl mx-auto">
                
                <div class="mb-4">
                    <label for="nom_traitement" class="block mb-2 text-sm font-bold text-slate-700">Nom du traitement <span class="text-red-500">*</span></label>
                    <input type="text" id="nom_traitement" name="nom_traitement" 
                           value="<?= htmlspecialchars($old['nom_traitement'] ?? '') ?>"
                           placeholder="Ex: Antihistaminique H1"
                           class="focus:shadow-primary-outline text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none">
                    <?php if (isset($errors['nom_traitement'])): ?>
                        <p class="mt-1 text-xs text-red-500 font-semibold">⚠️ <?= htmlspecialchars($errors['nom_traitement']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="conseils" class="block mb-2 text-sm font-bold text-slate-700">Conseils cliniques <span class="text-red-500">*</span></label>
                    <textarea id="conseils" name="conseils" rows="4"
                              placeholder="Conseils pour le patient..."
                              class="focus:shadow-primary-outline text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"><?= htmlspecialchars($old['conseils'] ?? '') ?></textarea>
                    <?php if (isset($errors['conseils'])): ?>
                        <p class="mt-1 text-xs text-red-500 font-semibold">⚠️ <?= htmlspecialchars($errors['conseils']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="interdiction" class="block mb-2 text-sm font-bold text-slate-700">Contre-indications <span class="text-red-500">*</span></label>
                    <textarea id="interdiction" name="interdiction" rows="4"
                              placeholder="Ce qu'il faut éviter..."
                              class="focus:shadow-primary-outline text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"><?= htmlspecialchars($old['interdiction'] ?? '') ?></textarea>
                    <?php if (isset($errors['interdiction'])): ?>
                        <p class="mt-1 text-xs text-red-500 font-semibold">⚠️ <?= htmlspecialchars($errors['interdiction']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-6">
                    <label for="id_allergie" class="block mb-2 text-sm font-bold text-slate-700">Associer à une autre allergie (ID)</label>
                    <input type="number" id="id_allergie" name="id_allergie" 
                           value="<?= htmlspecialchars($old['id_allergie'] ?? '') ?>"
                           class="focus:shadow-primary-outline text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all focus:border-blue-500 focus:outline-none">
                    <?php if (isset($errors['id_allergie'])): ?>
                        <p class="mt-1 text-xs text-red-500 font-semibold">⚠️ <?= htmlspecialchars($errors['id_allergie']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="inline-block px-8 py-3 font-bold text-center text-white uppercase align-middle transition-all rounded-lg cursor-pointer bg-gradient-to-tl from-blue-600 to-cyan-400 leading-normal text-xs ease-in tracking-tight-rem shadow-xs hover:-translate-y-px active:opacity-85">
                        💾 Sauvegarder les modifications
                    </button>
                    <a href="traitement_list.php<?= $id_allergie ? '?id_allergie=' . urlencode($id_allergie) : '' ?>" 
                       class="inline-block px-8 py-3 font-bold text-center text-slate-500 uppercase align-middle transition-all bg-transparent border border-gray-300 rounded-lg cursor-pointer leading-normal text-xs ease-in tracking-tight-rem hover:bg-gray-100">
                        Annuler
                    </a>
                </div>

            </form>
                        </div>
                    </div>
                    </div> <!-- Fin de la carte blanche -->
                </div>
            </div>
        </div>
    </main>
</body>
</html>