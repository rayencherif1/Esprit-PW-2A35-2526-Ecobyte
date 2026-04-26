<?php
require_once __DIR__ . '/../../../../../controller/RecetteController.php';
$controller = new RecetteController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$recette = $id ? $controller->getRecetteById($id) : null;
$message = $_GET['message'] ?? null;
$editing = $recette !== null;
$formTitle = $editing ? 'Modifier une recette' : 'Ajouter une recette';
$submitLabel = 'Confirmer';
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
    <style>
      #recette-form input:focus,
      #recette-form select:focus {
        outline: none;
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
      }
      .form-toast {
        position: fixed;
        top: 18px;
        right: 18px;
        z-index: 9999;
        max-width: 360px;
        border-radius: 14px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
        display: none;
      }
    </style>
  </head>
  <body class="m-0 font-sans text-base antialiased font-normal dark:bg-slate-900 leading-default bg-gray-50 text-slate-500">
    <div id="form-toast" class="form-toast" role="alert" aria-live="assertive"></div>
    <main class="relative h-full min-h-screen transition-all duration-200 ease-in-out xl:ml-0 rounded-xl">
      <div class="w-full px-6 py-6 mx-auto">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h6 class="mb-1 text-xl font-semibold text-slate-700 dark:text-white"><?= htmlspecialchars($formTitle) ?></h6>
            <p class="text-sm leading-normal text-slate-500 dark:text-white/70">Remplis les champs pour <?= $editing ? 'modifier' : 'ajouter' ?> ta recette.</p>
          </div>
          <div class="flex items-center gap-3">
            <a href="tables.php" class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:opacity-90" style="background-color: #047857;">
              Retour au tableau
            </a>
            <a href="recette-form.php" class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold shadow-md transition" style="background-color: #10b981; color: #ffffff;">
              + Ajouter
            </a>
          </div>
        </div>

        <div class="mt-6 rounded-[32px] border border-slate-200 bg-white p-8 shadow-xl">
          <?php if ($message === 'invalide') : ?>
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
              Veuillez remplir le formulaire.
            </div>
          <?php endif; ?>
          <form id="recette-form" action="/recette/controller/RecetteController.php" method="post" class="space-y-6" novalidate>
            <input type="hidden" name="save" value="1" />
            <?php if ($editing) : ?>
              <input type="hidden" name="id" value="<?= htmlspecialchars($recette['id']) ?>" />
            <?php endif; ?>
            <div class="grid gap-6 md:grid-cols-2">
              <label class="block">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Nom</span>
                <input name="nom" type="text" value="<?= htmlspecialchars($recette['nom'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition" />
              </label>
              <label class="block">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Type</span>
                <select name="type" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition">
                  <option value="Petit déjeuner" <?= isset($recette['type']) && $recette['type'] === 'Petit déjeuner' ? 'selected' : '' ?>>Petit déjeuner</option>
                  <option value="Déjeuner" <?= isset($recette['type']) && $recette['type'] === 'Déjeuner' ? 'selected' : '' ?>>Déjeuner</option>
                  <option value="Dîner" <?= isset($recette['type']) && $recette['type'] === 'Dîner' ? 'selected' : '' ?>>Dîner</option>
                </select>
              </label>
              <label class="block">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Calories</span>
                <input name="calories" type="number" value="<?= htmlspecialchars($recette['calories'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition" />
              </label>
              <label class="block">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Temps (min)</span>
                <input name="tempsPreparation" type="number" value="<?= htmlspecialchars($recette['tempsPreparation'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition" />
              </label>
              <label class="block md:col-span-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Difficulté</span>
                <input name="difficulte" type="text" placeholder="Ex : &#9733;&#9733; ou &#9733;&#9733;&#9733;&#9733;" value="<?= htmlspecialchars($recette['difficulte'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition" />
              </label>
              <label class="block md:col-span-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Impact carbone</span>
                <input name="impactCarbone" type="text" placeholder="Ex : 0.3 kg" value="<?= htmlspecialchars($recette['impactCarbone'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition" />
              </label>
              <label class="block md:col-span-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Image</span>
                <select name="image" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition">
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
              <button type="submit" class="inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-semibold transition" style="background-color: #10b981; color: #ffffff;">
                Confirmer
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('recette-form');
        if (!form) return;

        form.addEventListener('submit', function (event) {
          const nom = (form.querySelector('[name="nom"]')?.value || '').trim();
          const type = (form.querySelector('[name="type"]')?.value || '').trim();
          const calories = (form.querySelector('[name="calories"]')?.value || '').trim();
          const temps = (form.querySelector('[name="tempsPreparation"]')?.value || '').trim();
          const difficulte = (form.querySelector('[name="difficulte"]')?.value || '').trim();
          const impact = (form.querySelector('[name="impactCarbone"]')?.value || '').trim();
          const image = (form.querySelector('[name="image"]')?.value || '').trim();

          let message = '';

          if (nom === '') {
            message = '\u274c Veuillez renseigner le nom.';
          } else if (nom.length < 3) {
            message = '\u274c Le nom doit contenir au moins 3 caractères.';
          } else if (!type) {
            message = '\u274c Le type est obligatoire.';
          } else if (calories === '' || Number(calories) < 0 || Number.isNaN(Number(calories))) {
            message = '\u274c Les calories doivent être un nombre positif.';
          } else if (temps === '' || Number(temps) < 0 || Number.isNaN(Number(temps))) {
            message = '\u274c Le temps de préparation doit être un nombre positif.';
          } else if (!difficulte) {
            message = '\u274c La difficulté est obligatoire.';
          } else if (!impact) {
            message = '\u274c L\'impact carbone est obligatoire.';
          } else if (!image) {
            message = '\u274c L\'image est obligatoire.';
          }

          if (message) {
            event.preventDefault();
            showFormToast(message);
          }
        });

        function showFormToast(message) {
          const toast = document.getElementById('form-toast');
          if (!toast) return;
          toast.textContent = message;
          toast.style.display = 'block';
          window.clearTimeout(showFormToast._timer);
          showFormToast._timer = window.setTimeout(function () {
            toast.style.display = 'none';
          }, 3200);
        }
      });
    </script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js" async></script>
    <script src="../assets/js/argon-dashboard-tailwind.js?v=1.0.1" async></script>
  </body>
</html>
