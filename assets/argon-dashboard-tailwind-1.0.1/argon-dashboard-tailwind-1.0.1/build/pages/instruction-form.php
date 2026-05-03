<?php
require_once __DIR__ . '/../../../../../controller/InstructionController.php';
require_once __DIR__ . '/../../../../../controller/RecetteController.php';
$controller = new InstructionController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$recetteId = isset($_GET['recette_id']) ? (int) $_GET['recette_id'] : null;
$returnTo = $_GET['return_to'] ?? '';

$fiche = null;
if ($id) {
    $fiche = $controller->getById($id);
} elseif ($recetteId) {
    $fiche = $controller->getByRecetteId($recetteId);
}

$recette = null;
if ($recetteId) {
    $recette = (new RecetteController())->getRecetteById($recetteId);
}

$message = $_GET['message'] ?? null;
$editing = $fiche !== null;
$formTitle = $editing ? 'Modifier une fiche instruction' : 'Ajouter une fiche instruction';
$imageValue = $fiche['image'] ?? ($recette['image'] ?? '/recette/public/image/salade.jpg');
$nomValue = $fiche['nom'] ?? ($recette['nom'] ?? '');
$tempsValue = $fiche['temps'] ?? ($recette['tempsPreparation'] ?? '');
$preparationValue = $fiche['preparation'] ?? '';
if (trim($preparationValue) === '') {
    $preparationValue = '1- ';
}
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
    <style>
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
  <body class="m-0 min-h-screen font-sans text-base antialiased font-normal leading-default text-slate-600" style="font-family: 'Plus Jakarta Sans', 'Open Sans', system-ui, sans-serif; background: linear-gradient(165deg, #f0fdf4 0%, #f8fafc 35%, #ecfeff 100%);">
    <div id="form-toast" class="form-toast" role="alert" aria-live="assertive"></div>
    <main class="relative min-h-screen transition-all duration-200 ease-in-out xl:ml-0">
      <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-emerald-400/15 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-24 h-80 w-80 rounded-full bg-teal-400/10 blur-3xl"></div>
      </div>

      <div class="relative mx-auto w-full max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="mb-8 flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
          <div class="space-y-3">
            <?php
              $returnUrl = 'instruction-tables.php';
              if ($returnTo === 'back') {
                  $returnUrl = '/recette/index.php';
              } elseif ($returnTo === 'tables') {
                  $returnUrl = '/recette/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/tables.php';
              }
            ?>
            <a href="<?= $returnUrl ?>" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-emerald-700">
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
              <?php if ($recetteId) : ?>
                <input type="hidden" name="recette_id" value="<?= htmlspecialchars((string) $recetteId) ?>" />
              <?php endif; ?>
              <?php if ($returnTo !== '') : ?>
                <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>" />
              <?php endif; ?>
              <?php if ($editing) : ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $fiche['id']) ?>" />
              <?php endif; ?>

              <div class="grid gap-8 md:grid-cols-2">
                <label class="block md:col-span-2">
                  <span class="mb-1.5 flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">1</span>
                    Nom de la recette
                  </span>
                  <input name="nom" type="text" value="<?= htmlspecialchars($nomValue) ?>" placeholder="Ex. Salade du marché" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10" />
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
                      <img id="instruction-image-preview" src="<?= htmlspecialchars($imageValue) ?>" alt="" class="h-28 w-full max-w-[160px] rounded-2xl object-cover shadow-lg ring-2 ring-white" />
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
                  <textarea name="preparation" rows="6" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"><?= htmlspecialchars($preparationValue) ?></textarea>
                  <p class="mt-2 text-xs text-slate-500">Chaque ligne = une étape. La numérotation (1., 2., 3.) et le nombre d'étapes seront générés automatiquement.</p>
                </label>

                <label class="block">
                  <span class="mb-1.5 text-sm font-semibold text-slate-800">Temps total (minutes)</span>
                  <input name="temps" type="number" min="0" value="<?= htmlspecialchars((string) $tempsValue) ?>" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10" />
                </label>
              </div>

              <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-8 sm:flex-row sm:items-center sm:justify-between">
                <a href="<?= $returnUrl ?>" class="inline-flex items-center justify-center rounded-2xl border-2 border-slate-200 bg-white px-6 py-3.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2">
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
        const prepField = form.querySelector('[name="preparation"]');

        function ensureFirstStepPrefix() {
          if (!prepField) return;
          const value = (prepField.value || '').trim();
          if (value === '') {
            prepField.value = '1- ';
          }
        }

        function getNextStepNumber(textBeforeCursor) {
          const lines = textBeforeCursor.split(/\r?\n/);
          let lastStep = 0;
          lines.forEach((line) => {
            const match = line.trim().match(/^(\d+)\s*[-\.\)]\s*/);
            if (match) {
              const n = Number(match[1]);
              if (!Number.isNaN(n)) {
                lastStep = n;
              }
            }
          });
          return lastStep > 0 ? lastStep + 1 : 1;
        }

        ensureFirstStepPrefix();
        prepField?.addEventListener('focus', ensureFirstStepPrefix);
        prepField?.addEventListener('keydown', function (event) {
          if (event.key !== 'Enter') return;
          event.preventDefault();
          const start = prepField.selectionStart ?? prepField.value.length;
          const end = prepField.selectionEnd ?? prepField.value.length;
          const before = prepField.value.slice(0, start);
          const after = prepField.value.slice(end);
          const next = getNextStepNumber(before);
          const insertion = `\n${next}- `;
          prepField.value = before + insertion + after;
          const cursor = before.length + insertion.length;
          prepField.setSelectionRange(cursor, cursor);
        });

        form.addEventListener('submit', function (event) {
          const nom = (form.querySelector('[name="nom"]')?.value || '').trim();
          const ingredients = (form.querySelector('[name="ingredients"]')?.value || '').trim();
          const preparation = (form.querySelector('[name="preparation"]')?.value || '').trim();
          const image = (form.querySelector('[name="image"]')?.value || '').trim();
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
          } else if (temps === '' || Number(temps) < 0 || Number.isNaN(Number(temps))) {
            message = '\u274c Le temps doit être un nombre positif ou zéro.';
          }

          if (message) {
            event.preventDefault();
            showFormToast(message);
            return;
          }

          if (prepField) {
            const lines = prepField.value
              .split(/\r?\n/)
              .map((line) => line.trim())
              .filter((line) => line !== '')
              .map((line) => line.replace(/^\d+\s*[-\.\)]\s*/u, ''));

            if (lines.length > 0) {
              prepField.value = lines.map((line, idx) => `${idx + 1}- ${line}`).join('\n');
            }
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
