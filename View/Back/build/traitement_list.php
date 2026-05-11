<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/traitement.php';
require_once __DIR__ . '/../../../Controller/traitement.Controller.php';
require_once __DIR__ . '/../../../Controller/allergie.Controller.php';

$controller = new TraitementC();

// Récupérer l'ID de l'allergie pour filtrer (optionnel)
$id_allergie = $_GET['id_allergie'] ?? null;

// Récupérer le nom de l'allergie pour l'affichage
$allergie_nom = null;
if ($id_allergie) {
    $allergieController = new AllergieC();
    $allergieInfo = $allergieController->getAllergieById($id_allergie);
    if ($allergieInfo) {
        $allergie_nom = $allergieInfo['nom_allergie'] ?? $allergieInfo['nom'];
    }
    $traitements = $controller->listTraitementByAllergie($id_allergie);
} else {
    $traitements = $controller->listTraitement();
}

// S'assurer que c'est un tableau
if (!is_array($traitements)) {
    $traitements = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Traitements - EcoByte</title>
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
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h5 class="font-bold text-slate-700 text-xl">Programmes de traitements</h5>
                            <?php if ($allergie_nom): ?>
                                <p class="text-sm text-blue-500 font-semibold uppercase">Allergie : <?= htmlspecialchars($allergie_nom) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-8">
                        <a href="traitement_add.php<?= $id_allergie ? '?id_allergie=' . urlencode($id_allergie) : '' ?>" class="text-blue-600 font-semibold text-sm hover:underline">
                            + Nouveau programme
                        </a>
                    </div>
                </div>

                <div class="flex-auto px-0 pt-0 pb-2">
                    <div class="p-0 overflow-x-auto">
                        <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                            <thead class="align-bottom">
                                <tr>
                                    <th class="px-8 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Nom du traitement</th>
                                    <th class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Posologie</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Durée</th>
                                    <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($traitements)): ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center text-sm">Aucun traitement trouvé.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($traitements as $t): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="p-4 px-8 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                <h6 class="mb-0 text-sm leading-normal text-slate-700 font-medium"><?= htmlspecialchars($t['nom_traitement']) ?></h6>
                                            </td>
                                            <td class="p-4 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent text-sm">
                                                <span class="text-slate-500"><?= htmlspecialchars($t['posologie'] ?? 'N/A') ?></span>
                                            </td>
                                            <td class="p-4 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent text-center text-sm">
                                                <span class="text-slate-500"><?= htmlspecialchars($t['duree'] ?? 'N/A') ?></span>
                                            </td>
                                            <td class="p-4 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent text-center">
                                                <div class="flex justify-center gap-4 text-xs font-semibold leading-tight">
                                                    <a href="traitement_edit.php?id=<?= $t['id_traitement'] ?>" class="text-slate-500 hover:text-blue-600 transition-colors">Modifier</a>
                                                    <a href="traitement_delete.php?id=<?= $t['id_traitement'] ?><?= $id_allergie ? '&id_allergie='.$id_allergie : '' ?>" 
                                                       onclick="return confirm('Supprimer ce traitement ?')"
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