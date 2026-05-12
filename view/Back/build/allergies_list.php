<?php
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';

$controller = new AllergieC();

// Gestion des filtres
$search_query = $_GET['search'] ?? '';
$gravite_filter = $_GET['gravite'] ?? '';

// Suppression d'une allergie
if (isset($_GET['delete'])) {
    $controller->deleteAllergie($_GET['delete']);
    header('Location: allergies_list.php');
    exit;
}

// Chargement des données
$allergies = $controller->listAllergie($gravite_filter, $search_query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Allergies - EcoByte</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Open Sans', sans-serif; }
    </style>
</head>
<body class="m-0 font-sans text-base antialiased font-normal bg-gray-50 text-slate-500">
    
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="relative h-full min-h-screen transition-all duration-200 lg:ml-64">
        <!-- Blue Header Background -->
        <div class="absolute top-0 left-0 w-full bg-indigo-500 h-[300px] -z-10"></div>

        <div class="w-full px-10 py-10 mx-auto">
            
            <!-- Main Content Card -->
            <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl rounded-2xl bg-clip-border">
                
                <div class="p-8 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent">
                    <h5 class="mb-4 font-bold text-slate-700 text-xl">Programmes d'allergies</h5>
                    
                    <!-- Search & Filter Area -->
                    <form method="GET" class="flex flex-wrap items-center gap-6 mb-4">
                        <div class="flex flex-col">
                            <label class="text-xs font-semibold text-slate-400 mb-1">Type</label>
                            <select name="gravite" class="text-sm focus:shadow-soft-primary-outline leading-5.6 ease block w-48 appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all focus:border-blue-500 focus:outline-none">
                                <option value="">— tous —</option>
                                <option value="faible" <?= $gravite_filter === 'faible' ? 'selected' : '' ?>>Faible</option>
                                <option value="moyenne" <?= $gravite_filter === 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
                                <option value="grave" <?= $gravite_filter === 'grave' ? 'selected' : '' ?>>Grave</option>
                            </select>
                        </div>
                        
                        <div class="flex flex-col">
                            <label class="text-xs font-semibold text-slate-400 mb-1">Nom contient</label>
                            <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" class="text-sm focus:shadow-soft-primary-outline leading-5.6 ease block w-64 appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all focus:border-blue-500 focus:outline-none">
                        </div>

                        <button type="submit" class="mt-5 inline-block px-8 py-2.5 font-bold text-center text-white uppercase align-middle transition-all bg-[#5e72e4] rounded-lg cursor-pointer leading-normal text-xs ease-in tracking-tight-rem shadow-md hover:-translate-y-px active:opacity-85">
                            Filtrer
                        </button>
                    </form>

                    <div class="mb-8">
                        <a href="allergie_add.php" class="text-blue-600 font-semibold text-sm hover:underline">
                            + Nouveau programme
                        </a>
                    </div>
                </div>

                <div class="flex-auto px-0 pt-0 pb-2">
                    <div class="p-0 overflow-x-auto">
                        <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                            <thead class="align-bottom">
                                <tr>
                                    <th class="px-8 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Nom</th>
                                    <th class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Type</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Symptômes</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allergies)): ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center text-sm">Aucun programme trouvé.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($allergies as $a): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="p-4 px-8 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                <h6 class="mb-0 text-sm leading-normal text-slate-700 font-medium"><?= htmlspecialchars($a['nom_allergie'] ?? $a['nom'] ?? 'N/A') ?></h6>
                                            </td>
                                            <td class="p-4 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent text-sm">
                                                <span class="text-slate-500"><?= htmlspecialchars($a['gravite']) ?></span>
                                            </td>
                                            <td class="p-4 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent text-center text-sm">
                                                <span class="text-slate-500"><?= htmlspecialchars(substr($a['symptomes'], 0, 40)) ?>...</span>
                                            </td>
                                            <td class="p-4 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent text-center">
                                                <div class="flex justify-center gap-4 text-xs font-semibold leading-tight">
                                                    <a href="traitement_list.php?id_allergie=<?= $a['id_allergie'] ?>" class="text-slate-500 hover:text-blue-600 transition-colors">Gérer</a>
                                                    <a href="allergie_edit.php?id=<?= $a['id_allergie'] ?>" class="text-slate-500 hover:text-blue-600 transition-colors">Modifier</a>
                                                    <a href="allergies_list.php?delete=<?= $a['id_allergie'] ?>" 
                                                       onclick="return confirm('Supprimer ce programme ?')"
                                                       class="text-red-500 hover:text-red-700 transition-colors font-bold">Supprimer</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>