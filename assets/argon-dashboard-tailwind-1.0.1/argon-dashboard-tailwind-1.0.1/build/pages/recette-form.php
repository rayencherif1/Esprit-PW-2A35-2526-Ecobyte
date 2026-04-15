<?php
require_once __DIR__ . '/../../../../../controller/RecetteController.php';
$controller = new RecetteController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$recette = $id ? $controller->getRecetteById($id) : null;
$editing = $recette !== null;
$formTitle = $editing ? 'Modifier une recette' : 'Ajouter une recette';
$submitLabel = $editing ? 'Modifier' : 'Ajouter';
$imageValue = $recette['image'] ?? '/recette/public/image/salade.jpg';
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($formTitle) ?></title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link href="../assets/css/argon-dashboard-tailwind.css?v=1.0.1" rel="stylesheet" />
  </head>
  <body class="m-0 font-sans text-base antialiased font-normal dark:bg-slate-900 leading-default bg-gray-50 text-slate-500">
    <main class="relative h-full min-h-screen transition-all duration-200 ease-in-out xl:ml-0 rounded-xl">
      <div class="w-full px-6 py-6 mx-auto">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h6 class="mb-1 text-xl font-semibold text-slate-700 dark:text-white"><?= htmlspecialchars($formTitle) ?></h6>
            <p class="text-sm leading-normal text-slate-500 dark:text-white/70">Remplis les champs pour <?= $editing ? 'modifier' : 'ajouter' ?> ta recette.</p>
          </div>
          <div class="flex items-center gap-3">
            <a href="tables.php" class="inline-flex items-center rounded-full bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-slate-900">
              Retour au tableau
            </a>
            <a href="recette-form.php" class="inline-flex items-center rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-emerald-600">
              + Ajouter
            </a>
          </div>
        </div>

        <div class="mt-6 rounded-[32px] border border-slate-200 bg-white p-8 shadow-xl">
          <form action="/recette/controller/RecetteController.php" method="post" class="space-y-6">
            <input type="hidden" name="save" value="1" />
            <?php if ($editing) : ?>
              <input type="hidden" name="id" value="<?= htmlspecialchars($recette['id']) ?>" />
            <?php endif; ?>
            <div class="grid gap-6 md:grid-cols-2">
              <label class="block">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Nom</span>
                <input name="nom" type="text" required value="<?= htmlspecialchars($recette['nom'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" />
              </label>
              <label class="block">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Type</span>
                <select name="type" required class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100">
                  <option value="Petit déjeuner" <?= isset($recette['type']) && $recette['type'] === 'Petit déjeuner' ? 'selected' : '' ?>>Petit déjeuner</option>
                  <option value="Déjeuner" <?= isset($recette['type']) && $recette['type'] === 'Déjeuner' ? 'selected' : '' ?>>Déjeuner</option>
                  <option value="Dîner" <?= isset($recette['type']) && $recette['type'] === 'Dîner' ? 'selected' : '' ?>>Dîner</option>
                </select>
              </label>
              <label class="block">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Calories</span>
                <input name="calories" type="number" min="0" required value="<?= htmlspecialchars($recette['calories'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" />
              </label>
              <label class="block">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Temps (min)</span>
                <input name="tempsPreparation" type="number" min="0" required value="<?= htmlspecialchars($recette['tempsPreparation'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" />
              </label>
              <label class="block md:col-span-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Difficulté</span>
                <input name="difficulte" type="text" placeholder="Ex : ★★ ou ★★★★" required value="<?= htmlspecialchars($recette['difficulte'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" />
              </label>
              <label class="block md:col-span-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Impact carbone</span>
                <input name="impactCarbone" type="text" placeholder="Ex : 0.3 kg" required value="<?= htmlspecialchars($recette['impactCarbone'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" />
              </label>
              <label class="block md:col-span-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Image</span>
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
              <a href="tables.php" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Annuler
              </a>
              <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600">
                <?= htmlspecialchars($submitLabel) ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js" async></script>
    <script src="../assets/js/argon-dashboard-tailwind.js?v=1.0.1" async></script>
  </body>
</html>
