<?php
require_once __DIR__ . "/../controller/RecetteController.php";
require_once __DIR__ . "/../controller/InstructionController.php";

$controller = new RecetteController();
$recettes = $controller->afficherRecettes();

$instructionCtrl = new InstructionController();
$instructions = $instructionCtrl->listAll();

function back_instruction_resume(string $text, int $max = 64): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }
    if (function_exists('mb_strlen') && mb_strlen($text) > $max) {
        return mb_substr($text, 0, $max) . '…';
    }
    if (strlen($text) > $max) {
        return substr($text, 0, $max) . '…';
    }
    return $text;
}

$instructionFormBase = '../assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/instruction-form.php';
$instructionTablesUrl = '../assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/instruction-tables.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office Recettes</title>
    <link rel="stylesheet" href="../assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/assets/css/argon-dashboard-tailwind.css">
    <style>
      #search-recette:focus,
      #search-instruction:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        outline: none;
      }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-100 via-emerald-50/40 to-emerald-50 text-slate-900">

<div class="min-h-screen w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        <div class="mb-6 rounded-[32px] border border-slate-200 bg-white p-6 shadow-lg flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">Recettes</h1>
                <p class="mt-1 text-sm text-slate-500">Tableau principal pour ajouter, modifier et suivre les recettes.</p>
            </div>
            <a href="form.php" class="inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-semibold shadow-lg transition" style="background-color: #10b981; color: #ffffff;">
                <span style="font-size: 18px; font-weight: 700; line-height: 1;">+</span>
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
                    <input id="search-recette" type="text" placeholder="Rechercher une recette..." class="flex-1 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition duration-200" />
                    <a href="form.php" class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold shadow-md transition" style="background-color: #10b981; color: #ffffff;">
                        <span style="font-size: 18px; font-weight: 700; line-height: 1;">+</span>
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
                                        <a href="form.php?id=<?php echo urlencode($r['id']); ?>" class="inline-flex items-center justify-center rounded-2xl px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90" style="background-color: #047857;">
                                            <span aria-hidden="true">&#x270F;&#xFE0F;</span>
                                        </a>
                                        <a href="../controller/RecetteController.php?delete=<?php echo urlencode($r['id']); ?>" class="inline-flex items-center justify-center rounded-2xl px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90" style="background-color: #ef4444;" onclick="return confirm('Voulez-vous supprimer cette recette ?');">
                                            <span aria-hidden="true">&#x274C;</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-10 mb-6 rounded-[32px] border border-slate-200 bg-white p-6 shadow-lg flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight">Instructions</h2>
                <p class="mt-1 text-sm text-slate-500">Tableau des fiches : image, nom, ingrédients, préparation, nombre d'étapes et temps — comme pour les recettes.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="<?php echo htmlspecialchars($instructionTablesUrl); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Page Argon complète
                </a>
                <a href="<?php echo htmlspecialchars($instructionFormBase); ?>" class="inline-flex items-center gap-2.5 rounded-2xl px-6 py-3.5 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:opacity-95 hover:shadow-xl" style="background: linear-gradient(90deg, #059669 0%, #10b981 50%, #34d399 100%); box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.35);">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    </span>
                    Nouvelle fiche
                </a>
            </div>
        </div>

        <div class="mb-6 rounded-[32px] border border-slate-200 bg-white/95 p-4 shadow-md">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex w-full items-center gap-3">
                    <label class="sr-only" for="search-instruction">Recherche instructions</label>
                    <input id="search-instruction" type="text" placeholder="Rechercher une fiche..." class="flex-1 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition duration-200" />
                </div>
                <span class="text-xs text-slate-500">Filtre sur le tableau ci-dessous</span>
            </div>
        </div>

        <div class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-xl">
            <div class="overflow-x-auto">
                <table id="instruction-table" class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Image</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Nom</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Ingrédients</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Préparation</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Étapes</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Temps</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($instructions as $ins) : ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 align-top">
                                    <img src="<?php echo htmlspecialchars($ins['image']); ?>" alt="<?php echo htmlspecialchars($ins['nom']); ?>" class="h-12 w-12 rounded-2xl object-cover border border-slate-200" />
                                </td>
                                <td class="px-4 py-4 align-top text-slate-700"><?php echo htmlspecialchars($ins['nom']); ?></td>
                                <td class="px-4 py-4 align-top text-slate-700 max-w-xs"><?php echo htmlspecialchars(back_instruction_resume($ins['ingredients'] ?? '', 80)); ?></td>
                                <td class="px-4 py-4 align-top text-slate-700 max-w-xs"><?php echo htmlspecialchars(back_instruction_resume($ins['preparation'] ?? '', 80)); ?></td>
                                <td class="px-4 py-4 align-top text-slate-700"><?php echo htmlspecialchars((string) $ins['nombreEtapes']); ?></td>
                                <td class="px-4 py-4 align-top text-slate-700"><?php echo htmlspecialchars((string) $ins['temps']); ?> min</td>
                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="<?php echo htmlspecialchars($instructionFormBase); ?>?id=<?php echo urlencode((string) $ins['id']); ?>" class="inline-flex items-center justify-center rounded-2xl px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90" style="background-color: #047857;">
                                            <span aria-hidden="true">&#x270F;&#xFE0F;</span>
                                        </a>
                                        <a href="/recette/controller/InstructionController.php?delete_instruction=<?php echo urlencode((string) $ins['id']); ?>" class="inline-flex items-center justify-center rounded-2xl px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90" style="background-color: #ef4444;" onclick="return confirm('Voulez-vous supprimer cette fiche ?');">
                                            <span aria-hidden="true">&#x274C;</span>
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

<a href="form.php" class="fixed bottom-6 right-6 z-50 inline-flex h-14 w-14 items-center justify-center rounded-full text-3xl font-bold leading-none text-white shadow-2xl transition hover:scale-105 hover:opacity-95" style="background: linear-gradient(135deg, #059669 0%, #10b981 45%, #34d399 100%);" title="Ajouter une recette" aria-label="Ajouter une recette">
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

    const searchInstruction = document.getElementById('search-instruction');
    const instructionRows = document.querySelectorAll('#instruction-table tbody tr');
    if (searchInstruction) {
        searchInstruction.addEventListener('input', () => {
            const filter = searchInstruction.value.toLowerCase();
            instructionRows.forEach((row) => {
                row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });
        });
    }
</script>

</body>
</html>