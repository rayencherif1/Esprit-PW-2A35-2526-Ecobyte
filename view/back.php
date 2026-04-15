<?php
require_once __DIR__ . "/../controller/RecetteController.php";

$controller = new RecetteController();
$recettes = $controller->afficherRecettes();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office Recettes</title>
    <link rel="stylesheet" href="../assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/assets/css/argon-dashboard-tailwind.css">
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-100 via-slate-100 to-cyan-50 text-slate-900">

<div class="min-h-screen w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        <div class="mb-6 rounded-[32px] border border-slate-200 bg-white p-6 shadow-lg flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">Recettes</h1>
                <p class="mt-1 text-sm text-slate-500">Tableau principal pour ajouter, modifier et suivre les recettes.</p>
            </div>
            <a href="form.php" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:from-emerald-600 hover:to-cyan-600">
                <span class="text-lg leading-none font-bold">+</span>
                <span>Ajouter une recette</span>
            </a>
        </div>

        <?php if (isset($_GET['message'])) : ?>
            <?php $message = $_GET['message']; ?>
            <div class="mb-6 rounded-[32px] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-800 shadow-sm">
                <?php if ($message === 'ajoute') : ?>
                    Recette ajoutée avec succès.
                <?php elseif ($message === 'modifie') : ?>
                    Recette modifiée avec succès.
                <?php elseif ($message === 'supprime') : ?>
                    Recette supprimée avec succès.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="mb-6 rounded-[32px] border border-slate-200 bg-white/95 p-4 shadow-md">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex w-full items-center gap-3">
                    <label class="sr-only" for="search-recette">Recherche</label>
                    <input id="search-recette" type="text" placeholder="Rechercher une recette..." class="flex-1 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition duration-200 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" />
                    <a href="form.php" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:from-emerald-600 hover:to-cyan-600">
                        <span class="text-lg leading-none font-bold">+</span>
                        <span>Ajouter</span>
                    </a>
                </div>
                <span class="text-xs text-slate-500">Tape le nom d'une recette pour filtrer</span>
            </div>
        </div>

        <div class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-xl">
            <div class="overflow-x-auto">
                <table id="recette-table" class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Image</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Nom</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Type</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Calories</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Temps</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Difficulté</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Impact</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($recettes as $r) : ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 align-top">
                                    <img src="<?php echo htmlspecialchars($r['image']); ?>" alt="<?php echo htmlspecialchars($r['nom']); ?>" class="h-12 w-12 rounded-2xl object-cover border border-slate-200" />
                                </td>
                                <td class="px-4 py-4 align-top text-slate-700"><?php echo htmlspecialchars($r['nom']); ?></td>
                                <td class="px-4 py-4 align-top text-slate-700"><?php echo htmlspecialchars($r['type']); ?></td>
                                <td class="px-4 py-4 align-top text-slate-700"><?php echo htmlspecialchars($r['calories']); ?></td>
                                <td class="px-4 py-4 align-top text-slate-700"><?php echo htmlspecialchars($r['tempsPreparation']); ?> min</td>
                                <td class="px-4 py-4 align-top text-slate-700"><?php echo htmlspecialchars($r['difficulte']); ?></td>
                                <td class="px-4 py-4 align-top text-slate-700"><?php echo htmlspecialchars($r['impactCarbone']); ?></td>
                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="form.php?id=<?php echo urlencode($r['id']); ?>" class="inline-flex items-center justify-center rounded-2xl bg-slate-800 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-900">
                                            ✏️
                                        </a>
                                        <a href="../controller/RecetteController.php?delete=<?php echo urlencode($r['id']); ?>" class="inline-flex items-center justify-center rounded-2xl bg-red-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-600" onclick="return confirm('Voulez-vous supprimer cette recette ?');">
                                            ❌
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<a href="form.php" class="fixed bottom-6 right-6 z-50 inline-flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500 text-3xl font-bold leading-none text-white shadow-2xl transition hover:scale-105 hover:from-emerald-600 hover:to-cyan-600" title="Ajouter une recette" aria-label="Ajouter une recette">
    +
</a>

<script src="../assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/assets/js/argon-dashboard-tailwind.js"></script>
<script>
    const searchInput = document.getElementById('search-recette');
    const recetteRows = document.querySelectorAll('#recette-table tbody tr');

    searchInput.addEventListener('input', () => {
        const filter = searchInput.value.toLowerCase();
        recetteRows.forEach((row) => {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    });
</script>

</body>
</html>