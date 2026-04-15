<?php
require_once(__DIR__ . "/../controller/RecetteController.php");

$controller = new RecetteController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$recette = $id ? $controller->getRecetteById($id) : null;
$editing = $recette !== null;
$formTitle = $editing ? 'Modifier la recette' : 'Ajouter une recette';
$submitLabel = $editing ? 'Modifier' : 'Ajouter';
$imageValue = $recette['image'] ?? '/recette/public/image/salade.jpg';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($formTitle) ?></title>
    <link rel="stylesheet" href="../assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/assets/css/argon-dashboard-tailwind.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<div class="min-h-screen w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight"><?= htmlspecialchars($formTitle) ?></h1>
                <p class="mt-1 text-sm text-slate-500">Remplis les champs pour ajouter ou modifier une recette.</p>
            </div>
            <a href="back.php" class="inline-flex items-center justify-center rounded-2xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-slate-900">
                Retour au tableau
            </a>
        </div>

        <?php if ($id && !$recette) : ?>
            <div class="rounded-3xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">
                Recette introuvable. Utilise le bouton Retour pour revenir au tableau.
            </div>
        <?php else : ?>
            <form action="/recette/controller/RecetteController.php" method="post" class="space-y-6 rounded-[32px] border border-slate-200 bg-white p-8 shadow-lg">
                <input type="hidden" name="save" value="1">
                <?php if ($editing) : ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($recette['id']) ?>">
                <?php endif; ?>

                <div class="grid gap-6 md:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Nom</span>
                        <input name="nom" type="text" value="<?= htmlspecialchars($recette['nom'] ?? '') ?>" required class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" />
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Type</span>
                        <select name="type" required class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100">
                            <option value="Petit déjeuner" <?= isset($recette['type']) && $recette['type'] === 'Petit déjeuner' ? 'selected' : '' ?>>Petit déjeuner</option>
                            <option value="Déjeuner" <?= isset($recette['type']) && $recette['type'] === 'Déjeuner' ? 'selected' : '' ?>>Déjeuner</option>
                            <option value="Dîner" <?= isset($recette['type']) && $recette['type'] === 'Dîner' ? 'selected' : '' ?>>Dîner</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Calories</span>
                        <input name="calories" type="number" min="0" value="<?= htmlspecialchars($recette['calories'] ?? '') ?>" required class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" />
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Temps de préparation (min)</span>
                        <input name="tempsPreparation" type="number" min="0" value="<?= htmlspecialchars($recette['tempsPreparation'] ?? '') ?>" required class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" />
                    </label>

                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Difficulté</span>
                        <input name="difficulte" type="text" placeholder="Ex : ★★ ou ★★★★" value="<?= htmlspecialchars($recette['difficulte'] ?? '') ?>" required class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" />
                    </label>

                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Impact carbone</span>
                        <input name="impactCarbone" type="text" placeholder="Ex : 0.3 kg" value="<?= htmlspecialchars($recette['impactCarbone'] ?? '') ?>" required class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" />
                    </label>

                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Image</span>
                        <select name="image" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100">
                            <option value="/recette/public/image/citron.jpg" <?= $imageValue === '/recette/public/image/citron.jpg' ? 'selected' : '' ?>>Citron</option>
                            <option value="/recette/public/image/curry.jpg" <?= $imageValue === '/recette/public/image/curry.jpg' ? 'selected' : '' ?>>Curry</option>
                            <option value="/recette/public/image/pain.jpg" <?= $imageValue === '/recette/public/image/pain.jpg' ? 'selected' : '' ?>>Pain</option>
                            <option value="/recette/public/image/salade.jpg" <?= $imageValue === '/recette/public/image/salade.jpg' ? 'selected' : '' ?>>Salade</option>
                            <option value="/recette/public/image/soupe.jpg" <?= $imageValue === '/recette/public/image/soupe.jpg' ? 'selected' : '' ?>>Soupe</option>
                        </select>
                    </label>
                </div>

                <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:justify-between">
                    <a href="back.php" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Annuler
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600">
                        <?= htmlspecialchars($submitLabel) ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/assets/js/argon-dashboard-tailwind.js"></script>
</body>
</html>
