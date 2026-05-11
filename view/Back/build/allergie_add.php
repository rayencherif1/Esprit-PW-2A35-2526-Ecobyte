<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/allergie.php';
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';

$errors = [];
$old = [
    'nom' => '',
    'description' => '',
    'gravite' => '',
    'symptomes' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $gravite = trim($_POST['gravite'] ?? '');
    $symptomes = trim($_POST['symptomes'] ?? '');

    // Sauvegarder les anciennes valeurs
    $old = compact('nom', 'description', 'gravite', 'symptomes');

    // Validation Nom
    if (empty($nom)) {
        $errors['nom'] = "Le nom est obligatoire.";
    } elseif (mb_strlen($nom) < 3) {
        $errors['nom'] = "Le nom doit contenir au moins 3 caractères.";
    } elseif (mb_strlen($nom) > 255) {
        $errors['nom'] = "Le nom ne doit pas dépasser 255 caractères.";
    }

    // Validation Description
    if (empty($description)) {
        $errors['description'] = "La description est obligatoire.";
    } elseif (mb_strlen($description) > 1000) {
        $errors['description'] = "La description ne doit pas dépasser 1000 caractères.";
    }

    // Validation Gravité
    $gravitesValides = ['faible', 'moyenne', 'grave'];
    if (empty($gravite)) {
        $errors['gravite'] = "La gravité est obligatoire.";
    } elseif (!in_array($gravite, $gravitesValides, true)) {
        $errors['gravite'] = "Valeur de gravité invalide.";
    }

    // Validation Symptômes
    if (empty($symptomes)) {
        $errors['symptomes'] = "Les symptômes sont obligatoires.";
    } elseif (mb_strlen($symptomes) > 1000) {
        $errors['symptomes'] = "Les symptômes ne doivent pas dépasser 1000 caractères.";
    }

    // Si aucune erreur => insertion
    if (empty($errors)) {
        try {
            $allergie = new Allergie(null, $nom, $description, $gravite, $symptomes);
            $controller = new AllergieC();
            $result = $controller->addAllergie($allergie);
            
            if ($result) {
                header("Location: allergies_list.php?success=added");
                exit();
            } else {
                $errors['general'] = "Erreur lors de l'ajout de l'allergie. Veuillez réessayer.";
            }
        } catch (Exception $e) {
            $errors['general'] = "Une erreur est survenue : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Ajouter Allergie - EcoByte Santé</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="https://demos.creative-tim.com/argon-dashboard-tailwind/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-tailwind/assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .form-control:focus { border-color: #5e72e4; box-shadow: 0 0 0 2px rgba(94, 114, 228, 0.2); }
    </style>
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
                        <h2 class="text-slate-700 font-bold text-xl mb-1">Nouvelle Allergie</h2>
                        <p class="text-slate-400 text-sm font-semibold">Remplissez les informations cliniques</p>
                    </div>
                    <a href="allergies_list.php" class="text-sm font-bold text-blue-600 hover:underline">
                        ← Annuler et retourner
                    </a>
                </div>

            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl rounded-2xl bg-clip-border">
                        
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent text-center lg:text-left">
                            <h5 class="mb-1 font-bold text-slate-700">📋 Formulaire d'Ajout</h5>
                            <p class="text-sm leading-normal text-slate-500">Veuillez renseigner les détails cliniques de l'allergie</p>
                        </div>

                        <div class="flex-auto p-6">
                            <?php if (isset($errors['general'])): ?>
                                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                                    ❌ <?= htmlspecialchars($errors['general']) ?>
                                </div>
                            <?php endif; ?>
            <form method="post" action="" novalidate class="max-w-3xl mx-auto">

                <div class="mb-4">
                    <label for="nom" class="block mb-2 text-sm font-bold text-slate-700">Nom de l'allergie <span class="text-red-500">*</span></label>
                    <input type="text" id="nom" name="nom" autofocus
                           value="<?= htmlspecialchars($old['nom'], ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ex: Arachides, Lactose, Pollen..."
                           class="focus:shadow-primary-outline text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none">
                    <?php if (isset($errors['nom'])): ?>
                        <p class="mt-1 text-xs text-red-500 font-semibold">⚠️ <?= htmlspecialchars($errors['nom']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="description" class="block mb-2 text-sm font-bold text-slate-700">Description <span class="text-red-500">*</span></label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Décrivez l'allergie et ses spécificités..."
                              class="focus:shadow-primary-outline text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"><?= htmlspecialchars($old['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <p class="mt-1 text-xs text-red-500 font-semibold">⚠️ <?= htmlspecialchars($errors['description']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="gravite" class="block mb-2 text-sm font-bold text-slate-700">Niveau de Gravité <span class="text-red-500">*</span></label>
                    <select id="gravite" name="gravite"
                            class="focus:shadow-primary-outline text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none">
                        <option value="">-- Choisir la gravité --</option>
                        <option value="faible" <?= ($old['gravite'] == "faible") ? "selected" : "" ?>>🟢 Faible</option>
                        <option value="moyenne" <?= ($old['gravite'] == "moyenne") ? "selected" : "" ?>>🟠 Moyenne</option>
                        <option value="grave" <?= ($old['gravite'] == "grave") ? "selected" : "" ?>>🔴 Grave</option>
                    </select>
                    <?php if (isset($errors['gravite'])): ?>
                        <p class="mt-1 text-xs text-red-500 font-semibold">⚠️ <?= htmlspecialchars($errors['gravite']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-6">
                    <label for="symptomes" class="block mb-2 text-sm font-bold text-slate-700">Symptômes Principaux <span class="text-red-500">*</span></label>
                    <textarea id="symptomes" name="symptomes" rows="3"
                              placeholder="Ex: Urticaire, gonflement, difficultés..."
                              class="focus:shadow-primary-outline text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none"><?= htmlspecialchars($old['symptomes'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    <p class="mt-1 text-xs text-slate-400">💡 Séparez les symptômes par des virgules.</p>
                    <?php if (isset($errors['symptomes'])): ?>
                        <p class="mt-1 text-xs text-red-500 font-semibold">⚠️ <?= htmlspecialchars($errors['symptomes']) ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="bg-blue-50 p-4 rounded-xl mb-6 border border-blue-100">
                    <h6 class="text-xs font-bold text-blue-700 uppercase mb-2 italic">Exemples :</h6>
                    <ul class="text-xs text-blue-600 list-disc pl-4 space-y-1">
                        <li>Alimentaire : Urticaire, gonflement, vomissements</li>
                        <li>Respiratoire : Éternuements, nez qui coule, asthme</li>
                    </ul>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="inline-block px-8 py-3 font-bold text-center text-white uppercase align-middle transition-all rounded-lg cursor-pointer bg-gradient-to-tl from-blue-600 to-cyan-400 leading-normal text-xs ease-in tracking-tight-rem shadow-xs hover:-translate-y-px active:opacity-85">
                        ✅ Enregistrer l'Allergie
                    </button>
                    <a href="allergies_list.php" class="inline-block px-8 py-3 font-bold text-center text-slate-500 uppercase align-middle transition-all bg-transparent border border-gray-300 rounded-lg cursor-pointer leading-normal text-xs ease-in tracking-tight-rem hover:bg-gray-100">
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