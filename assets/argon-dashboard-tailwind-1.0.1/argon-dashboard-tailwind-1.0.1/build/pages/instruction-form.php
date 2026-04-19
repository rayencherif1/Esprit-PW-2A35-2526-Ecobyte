<?php
require_once __DIR__ . '/../../../../../controller/InstructionController.php';
$controller = new InstructionController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$fiche = $id ? $controller->getById($id) : null;
$message = $_GET['message'] ?? null;
$editing = $fiche !== null;
$formTitle = $editing ? 'Modifier une fiche instruction' : 'Ajouter une fiche instruction';
$imageValue = $fiche['image'] ?? '/recette/public/image/salade.jpg';
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($formTitle) ?></title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link href="../assets/css/argon-dashboard-tailwind.css?v=1.0.1" rel="stylesheet" />
  </head>
  <body class="m-0 min-h-screen font-sans text-base antialiased font-normal leading-default text-slate-600" style="font-family: 'Plus Jakarta Sans', 'Open Sans', system-ui, sans-serif; background: linear-gradient(165deg, #f0fdf4 0%, #f8fafc 35%, #ecfeff 100%);">
    <main class="relative min-h-screen transition-all duration-200 ease-in-out xl:ml-0">
      <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-emerald-400/15 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-24 h-80 w-80 rounded-full bg-teal-400/10 blur-3xl"></div>
      </div>

      <div class="relative mx-auto w-full max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="mb-8 flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
          <div class="space-y-3">
            <a href="instruction-tables.php" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-emerald-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
              Retour au tableau
            </a>
            <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-800 shadow-sm ring-1 ring-emerald-500/20 backdrop-blur-sm">
              <?= $editing ? 'Édition' : 'Création' ?>
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl"><?= htmlspecialchars($formTitle) ?></h1>
            <p class="max-w-xl text-base leading-relaxed text-slate-600"><?= $editing ? 'Mets à jour les détails de ta fiche.' : 'Décris le plat : ingrédients, étapes et durée — tout est regroupé dans une fiche claire et moderne.' ?></p>
          </div>
          <a href="instruction-form.php" class="inline-flex shrink-0 items-center gap-2.5 self-start rounded-2xl border border-slate-200/80 bg-white/90 px-5 py-3 text-sm font-semibold text-slate-800 shadow-md backdrop-blur-sm transition hover:border-emerald-200 hover:bg-white hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-inner">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            </span>
            Nouvelle fiche
          </a>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/90 shadow-2xl shadow-slate-200/50 ring-1 ring-white/60 backdrop-blur-md">
          <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-500/5 via-transparent to-teal-500/5 px-6 py-5 sm:px-8 sm:py-6">
            <h2 class="text-lg font-bold text-slate-800"><?= $editing ? 'Modifier les champs' : 'Remplir la fiche' ?></h2>
            <p class="mt-1 text-sm text-slate-500">Les champs marqués par la validation doivent être complets avant envoi.</p>
          </div>

          <div class="p-6 sm:p-8">
            <?php if ($id && !$fiche) : ?>
              <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                Fiche introuvable. Utilise le lien Retour pour revenir au tableau.
              </div>
            <?php elseif ($message === 'invalide') : ?>
              <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                Veuillez remplir le formulaire.
              </div>
            <?php endif; ?>

            <?php if (!$id || $fiche) : ?>
            <form id="instruction-form" action="/recette/controller/InstructionController.php" method="post" class="space-y-8" novalidate>
              <input type="hidden" name="save_instruction" value="1" />
              <?php if ($editing) : ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $fiche['id']) ?>" />
              <?php endif; ?>

              <div class="grid gap-8 md:grid-cols-2">
                <label class="block md:col-span-2">
                  <span class="mb-1.5 flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">1</span>
                    Nom de la recette
                  </span>
                  <input name="nom" type="text" value="<?= htmlspecialchars($fiche['nom'] ?? '') ?>" placeholder="Ex. Salade du marché" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10" />
                </label>

                <div class="md:col-span-2 grid gap-6 md:grid-cols-2 md:items-start">
                  <label class="block">
                    <span class="mb-1.5 flex items-center gap-2 text-sm font-semibold text-slate-800">
                      <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">2</span>
                      Visuel
                    </span>
                    <select id="instruction-image-select" name="image" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                      <option value="/recette/public/image/citron.jpg" <?= $imageValue === '/recette/public/image/citron.jpg' ? 'selected' : '' ?>>Citron</option>
                      <option value="/recette/public/image/curry.jpg" <?= $imageValue === '/recette/public/image/curry.jpg' ? 'selected' : '' ?>>Curry</option>
                      <option value="/recette/public/image/pain.jpg" <?= $imageValue === '/recette/public/image/pain.jpg' ? 'selected' : '' ?>>Pain</option>
                      <option value="/recette/public/image/salade.jpg" <?= $imageValue === '/recette/public/image/salade.jpg' ? 'selected' : '' ?>>Salade</option>
                      <option value="/recette/public/image/soupe.jpg" <?= $imageValue === '/recette/public/image/soupe.jpg' ? 'selected' : '' ?>>Soupe</option>
                    </select>
                  </label>
                  <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-4 text-center">
                    <p class="mb-3 text-xs font-medium uppercase tracking-wide text-slate-500">Aperçu</p>
                    <div class="mx-auto flex max-w-[200px] justify-center">
                      <img id="instruction-image-preview" src="<?= htmlspecialchars($imageValue) ?>" alt="" class="h-36 w-full max-w-[200px] rounded-2xl object-cover shadow-lg ring-2 ring-white" />
                    </div>
                  </div>
                </div>

                <label class="block md:col-span-2">
                  <span class="mb-1.5 flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">3</span>
                    Ingrédients
                  </span>
                  <textarea name="ingredients" rows="3" placeholder="Liste séparée par des virgules ou lignes…" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"><?= htmlspecialchars($fiche['ingredients'] ?? '') ?></textarea>
                </label>

                <label class="block md:col-span-2">
                  <span class="mb-1.5 flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">4</span>
                    Préparation
                  </span>
                  <textarea name="preparation" rows="4" placeholder="Étapes détaillées…" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"><?= htmlspecialchars($fiche['preparation'] ?? '') ?></textarea>
                </label>

                <label class="block">
                  <span class="mb-1.5 text-sm font-semibold text-slate-800">Nombre d’étapes</span>
                  <input name="nombreEtapes" type="number" min="1" value="<?= htmlspecialchars((string) ($fiche['nombreEtapes'] ?? '')) ?>" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10" />
                </label>
                <label class="block">
                  <span class="mb-1.5 text-sm font-semibold text-slate-800">Temps total (minutes)</span>
                  <input name="temps" type="number" min="0" value="<?= htmlspecialchars((string) ($fiche['temps'] ?? '')) ?>" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10" />
                </label>
              </div>

              <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-8 sm:flex-row sm:items-center sm:justify-between">
                <a href="instruction-tables.php" class="inline-flex items-center justify-center rounded-2xl border-2 border-slate-200 bg-white px-6 py-3.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2">
                  Annuler
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:from-emerald-600 hover:to-teal-700 hover:shadow-xl hover:shadow-emerald-500/35 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                  Confirmer
                </button>
              </div>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const sel = document.getElementById('instruction-image-select');
        const prev = document.getElementById('instruction-image-preview');
        if (sel && prev) {
          sel.addEventListener('change', function () {
            prev.src = sel.value;
          });
        }

        const form = document.getElementById('instruction-form');
        if (!form) return;

        form.addEventListener('submit', function (event) {
          const nom = (form.querySelector('[name="nom"]')?.value || '').trim();
          const ingredients = (form.querySelector('[name="ingredients"]')?.value || '').trim();
          const preparation = (form.querySelector('[name="preparation"]')?.value || '').trim();
          const image = (form.querySelector('[name="image"]')?.value || '').trim();
          const nombreEtapes = (form.querySelector('[name="nombreEtapes"]')?.value || '').trim();
          const temps = (form.querySelector('[name="temps"]')?.value || '').trim();

          let message = '';

          if (nom === '') {
            message = '\u274c Veuillez renseigner le nom.';
          } else if (nom.length < 3) {
            message = '\u274c Le nom doit contenir au moins 3 caractères.';
          } else if (!ingredients) {
            message = '\u274c Les ingrédients sont obligatoires.';
          } else if (!preparation) {
            message = '\u274c La préparation est obligatoire.';
          } else if (!image) {
            message = '\u274c L\'image est obligatoire.';
          } else if (nombreEtapes === '' || Number(nombreEtapes) < 1 || Number.isNaN(Number(nombreEtapes))) {
            message = '\u274c Le nombre d\'étapes doit être au moins 1.';
          } else if (temps === '' || Number(temps) < 0 || Number.isNaN(Number(temps))) {
            message = '\u274c Le temps doit être un nombre positif ou zéro.';
          }

          if (message) {
            event.preventDefault();
            alert(message);
          }
        });
      });
    </script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js" async></script>
    <script src="../assets/js/argon-dashboard-tailwind.js?v=1.0.1" async></script>
  </body>
</html>
