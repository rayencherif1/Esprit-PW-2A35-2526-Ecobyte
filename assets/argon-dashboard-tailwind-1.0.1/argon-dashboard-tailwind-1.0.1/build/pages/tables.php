<?php
require_once __DIR__ . '/../../../../../controller/RecetteController.php';
require_once __DIR__ . '/../../../../../controller/InstructionController.php';
$controller = new RecetteController();
$recettes = $controller->afficherRecettes();
$instructionController = new InstructionController();
$instructions = $instructionController->listAll();
$instructionsByRecetteId = [];
foreach ($instructions as $ins) {
    if (isset($ins['recette_id']) && $ins['recette_id'] !== null) {
        $instructionsByRecetteId[(int) $ins['recette_id']] = $ins;
    }
}

function instruction_is_completed(array $instruction): bool
{
    $ingredients = trim((string) ($instruction['ingredients'] ?? ''));
    $preparation = trim((string) ($instruction['preparation'] ?? ''));

    if ($ingredients === '' || $preparation === '') {
        return false;
    }

    if (str_starts_with($ingredients, 'À compléter') || str_starts_with($preparation, 'À compléter')) {
        return false;
    }

    // Si la preparation est juste amorcee ("1-"), ce n'est pas encore complete.
    if ($preparation === '1-' || $preparation === '1- ') {
        return false;
    }

    return true;
}

$message = $_GET['message'] ?? null;
$messageInstruction = $_GET['message_instruction'] ?? null;
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$hostNameOnly = preg_replace('/:\d+$/', '', $host) ?? $host;
$hostPort = '';
if (preg_match('/:(\d+)$/', $host, $m) === 1) {
    $hostPort = ':' . $m[1];
} elseif (!empty($_SERVER['SERVER_PORT']) && !in_array((string) $_SERVER['SERVER_PORT'], ['80', '443'], true)) {
    $hostPort = ':' . (string) $_SERVER['SERVER_PORT'];
}

// QR : meme hote que la page (ex. localhost si vous ouvrez le back-office en localhost).
$instructionPdfBase = $scheme . '://' . $hostNameOnly . $hostPort . '/recette/view/recette-instructions-pdf.php?id=';
$toastMessage = '';
if ($message === 'ajoute') {
    $toastMessage = 'Recette ajoutée avec succès.';
} elseif ($message === 'modifie') {
    $toastMessage = 'Recette modifiée avec succès.';
} elseif ($message === 'supprime') {
    $toastMessage = 'Recette supprimée avec succès.';
} elseif ($messageInstruction === 'ajoute') {
    $toastMessage = 'Instruction ajoutée avec succès.';
} elseif ($messageInstruction === 'modifie') {
    $toastMessage = 'Instruction modifiée avec succès.';
} elseif ($messageInstruction === 'supprime') {
    $toastMessage = 'Instruction supprimée avec succès.';
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png" />
    <link rel="icon" type="image/png" href="../assets/img/favicon.png" />
    <title>Recettes - Argon Dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link href="../assets/css/argon-dashboard-tailwind.css?v=1.0.1" rel="stylesheet" />
    <style>
      #search-recette:focus {
        outline: none;
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
      }
      #sort-recette:focus {
        outline: none;
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
      }
      .tiny-kcal { display: block; font-size: 10px; color: #94a3b8; line-height: 1.1; }
      .pill { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.2rem 0.55rem; border-radius: 999px; font-size: 11px; font-weight: 600; }
      .pill-type { background-color: #e8f8f1; color: #0f766e; }
      .pill-impact { background-color: #ecfdf5; color: #059669; }
      .sort-hint { color: #94a3b8; font-size: 10px; margin-left: 3px; }
      .table-footer { border-top: 1px solid #eef2f7; }
      .toolbar-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 14px;
        padding: 0.7rem 0.85rem;
      }
      .toolbar-control i {
        color: #94a3b8;
        font-size: 13px;
      }
      .toolbar-control input,
      .toolbar-control select {
        border: 0;
        background: transparent;
        outline: none;
        width: 100%;
        color: #334155;
        font-size: 13px;
        padding: 0;
      }
      .toolbar-control select { min-width: 170px; }
      .toolbar-label {
        font-size: 12px;
        color: #94a3b8;
        white-space: nowrap;
      }
      .form-toast {
        position: fixed;
        top: 18px;
        right: 18px;
        z-index: 9999;
        max-width: 360px;
        border-radius: 14px;
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: #166534;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
        display: none;
      }
    </style>
  </head>
  <body class="m-0 font-sans text-base antialiased font-normal bg-gray-50 text-slate-500">
    <div id="form-toast" class="form-toast" role="status" aria-live="polite"><?= htmlspecialchars($toastMessage) ?></div>
    <div class="absolute w-full min-h-75" style="background: linear-gradient(90deg, #059669 0%, #10b981 50%, #34d399 100%);"></div>
    <aside class="fixed inset-y-0 flex-wrap items-center justify-between block w-full p-0 my-4 overflow-y-auto transition-transform duration-200 -translate-x-full bg-white border-0 shadow-xl xl:ml-6 max-w-64 ease-nav-brand z-990 rounded-2xl xl:left-0 xl:translate-x-0" aria-expanded="false">
      <div class="h-19">
        <a class="block px-8 py-6 m-0 text-sm whitespace-nowrap text-slate-700" href="dashboard.html">
          <img src="../assets/img/logo-ct-dark.png" class="inline h-full max-w-full max-h-8" alt="main_logo" />
          <span class="ml-1 font-semibold">Argon Dashboard 2</span>
        </a>
      </div>
      <hr class="h-px mt-0 bg-transparent bg-gradient-to-r from-transparent via-black/40 to-transparent" />
      <div class="items-center block w-auto max-h-screen overflow-auto h-sidenav grow basis-full">
        <ul class="flex flex-col pl-0 mb-0">
          <li class="mt-0.5 w-full">
            <a class="py-2.7 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors text-slate-700" href="dashboard.html">
              <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg">
                <i class="relative top-0 text-sm leading-normal text-emerald-500 ni ni-tv-2"></i>
              </div>
              <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Dashboard</span>
            </a>
          </li>
          <li class="mt-0.5 w-full">
            <a class="py-2.7 bg-emerald-500/30 text-sm my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 font-semibold text-slate-700 transition-colors" href="tables.php">
              <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg">
                <i class="relative top-0 text-sm leading-normal text-orange-500 ni ni-calendar-grid-58"></i>
              </div>
              <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Recettes</span>
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out rounded-xl xl:ml-68">
      <div class="w-full px-6 py-6 mx-auto">
        <div class="flex flex-wrap -mx-3">
          <div class="flex-none w-full max-w-full px-3">
            <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 shadow-xl rounded-2xl bg-clip-border">
              <div class="p-6 pb-0 mb-0 border-b-0 rounded-t-2xl">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                  <div>
                    <h6>Gestion des recettes</h6>
                    <p class="mb-0 text-sm leading-normal text-slate-400">Tableau de bord back office pour la gestion des recettes.</p>
                  </div>
                  <a href="recette-form.php" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold shadow-md transition" style="background-color: #10b981; color: #ffffff;">
                    <span style="font-size: 18px; font-weight: 700; line-height: 1;">+</span>
                    <span>Ajouter</span>
                  </a>
                </div>
              </div>

              <div class="px-6 mt-4 mb-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div class="w-full">
                    <label class="sr-only" for="search-recette">Rechercher</label>
                    <div class="toolbar-control">
                      <i class="fas fa-search" aria-hidden="true"></i>
                      <input id="search-recette" type="text" placeholder="Rechercher une recette..." />
                    </div>
                  </div>
                  <div class="w-full sm:w-auto">
                    <label class="sr-only" for="sort-recette">Trier</label>
                    <div class="toolbar-control">
                      <span class="toolbar-label">Trier par</span>
                      <select id="sort-recette">
                        <option value="default">Par défaut</option>
                      <option value="nom_asc">Nom (A → Z)</option>
                      <option value="nom_desc">Nom (Z → A)</option>
                      <option value="calories_desc">Calories (élevées)</option>
                      <option value="calories_asc">Calories (faibles)</option>
                      <option value="temps_asc">Temps (rapide)</option>
                      <option value="temps_desc">Temps (long)</option>
                      <option value="instruction_done">Instruction faite d'abord</option>
                      <option value="instruction_pending">Instruction en attente d'abord</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex-auto px-0 pt-0 pb-2">
                <div class="p-0 overflow-x-auto">
                  <table id="recette-table" class="items-center w-full mb-0 align-top border-collapse text-slate-500">
                    <thead class="align-bottom">
                      <tr>
                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Image</th>
                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Nom <span class="sort-hint">↕</span></th>
                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Type <span class="sort-hint">↕</span></th>
                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Calories <span class="sort-hint">↕</span></th>
                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Temps <span class="sort-hint">↕</span></th>
                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Difficulte</th>
                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Impact</th>
                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Instruction</th>
                        <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b whitespace-nowrap text-slate-400 opacity-70">Actions</th>
                        <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b whitespace-nowrap text-slate-400 opacity-70">QR Code</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($recettes as $recette) : ?>
                        <tr
                          data-index="<?= (int) $recette['id'] ?>"
                          data-nom="<?= htmlspecialchars(function_exists('mb_strtolower') ? mb_strtolower((string) $recette['nom']) : strtolower((string) $recette['nom']), ENT_QUOTES) ?>"
                          data-search="<?= htmlspecialchars((string) ($recette['nom'] . ' ' . $recette['type']), ENT_QUOTES) ?>"
                          data-calories="<?= (int) $recette['calories'] ?>"
                          data-temps="<?= (int) $recette['tempsPreparation'] ?>"
                          data-instruction-done="<?= (isset($instructionsByRecetteId[(int) $recette['id']]) && instruction_is_completed($instructionsByRecetteId[(int) $recette['id']])) ? '1' : '0' ?>"
                        >
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap">
                            <img src="<?= htmlspecialchars($recette['image']) ?>" alt="<?= htmlspecialchars($recette['nom']) ?>" class="h-10 w-10 rounded-xl object-cover border border-slate-200" />
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap">
                            <span class="text-sm font-semibold"><?= htmlspecialchars($recette['nom']) ?></span>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap">
                            <span class="pill pill-type"><?= htmlspecialchars($recette['type']) ?></span>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap">
                            <span class="text-sm font-semibold"><?= htmlspecialchars($recette['calories']) ?></span>
                            <span class="tiny-kcal">kcal</span>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap">
                            <span class="text-sm">🕒 <?= htmlspecialchars($recette['tempsPreparation']) ?> min</span>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap">
                            <span class="text-sm"><?= htmlspecialchars($recette['difficulte']) ?></span>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap">
                            <span class="pill pill-impact">🍃 <?= htmlspecialchars($recette['impactCarbone']) ?></span>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap">
                            <?php
                              $linkedInstruction = $instructionsByRecetteId[(int) $recette['id']] ?? null;
                              $instructionDone = $linkedInstruction !== null && instruction_is_completed($linkedInstruction);
                              $instructionUrl = 'instruction-form.php';
                              if ($linkedInstruction !== null) {
                                  $instructionUrl .= '?id=' . urlencode((string) $linkedInstruction['id']) . '&return_to=tables';
                              } else {
                                  $instructionUrl .= '?recette_id=' . urlencode((string) $recette['id']) . '&return_to=tables';
                              }
                            ?>
                            <?php if ($instructionDone) : ?>
                              <a href="<?= htmlspecialchars($instructionUrl) ?>" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold text-white transition hover:opacity-90" style="background-color: #10b981;" title="Modifier l'instruction" aria-label="Modifier l'instruction">
                                <span aria-hidden="true">✓</span>
                              </a>
                            <?php else : ?>
                              <a href="<?= htmlspecialchars($instructionUrl) ?>" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold text-white transition hover:opacity-90" style="background-color: #0ea5e9;" title="Ajouter instruction" aria-label="Ajouter instruction">+</a>
                            <?php endif; ?>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap">
                            <div class="flex justify-center gap-2">
                              <a href="recette-form.php?id=<?= urlencode($recette['id']) ?>" class="inline-flex items-center justify-center rounded-full h-8 w-8 text-xs font-semibold transition hover:opacity-90" style="background-color: #10b981; color: #ffffff;"><span aria-hidden="true">&#x270F;&#xFE0F;</span></a>
                              <a href="/recette/controller/RecetteController.php?delete=<?= urlencode($recette['id']) ?>" class="inline-flex items-center justify-center rounded-full h-8 w-8 text-xs font-semibold transition hover:opacity-90" style="background-color: #ef4444; color: #ffffff;"><span aria-hidden="true">&#x274C;</span></a>
                            </div>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap">
                            <?php
                              $pdfUrl = $instructionPdfBase . urlencode((string) $recette['id']);
                              $qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=56x56&data=' . rawurlencode($pdfUrl);
                            ?>
                            <a href="<?= htmlspecialchars($pdfUrl) ?>" target="_blank" rel="noopener noreferrer" title="Ouvrir le PDF des instructions">
                              <img src="<?= htmlspecialchars($qrSrc) ?>" alt="QR recette <?= htmlspecialchars((string) $recette['id']) ?>" class="h-10 w-10 rounded-md border border-slate-200 bg-white p-0.5 inline-block" loading="lazy" />
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
                <div class="table-footer px-6 py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <span class="text-xs text-slate-500">Affichage de 1 à <?= count($recettes) ?> sur <?= count($recettes) ?> recettes</span>
                  <div class="flex items-center gap-2">
                    <button type="button" class="h-7 w-7 rounded-lg border border-slate-200 bg-white text-slate-400">&lt;</button>
                    <button type="button" class="h-7 w-7 rounded-lg bg-emerald-500 text-white text-xs font-semibold">1</button>
                    <button type="button" class="h-7 w-7 rounded-lg border border-slate-200 bg-white text-slate-400">&gt;</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-recette');
        const sortSelect = document.getElementById('sort-recette');
        const tbody = document.querySelector('#recette-table tbody');
        const tableRows = Array.from(tbody.querySelectorAll('tr'));

        function normalizeText(value) {
          return (value || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
        }

        function applyFilterAndSort() {
          const query = normalizeText(searchInput?.value || '');
          const sortValue = sortSelect?.value || 'default';

          const filtered = tableRows.filter((row) => {
            const haystack = normalizeText(row.dataset.search || row.textContent);
            return haystack.includes(query);
          });

          filtered.sort((a, b) => {
            const aNom = a.dataset.nom || '';
            const bNom = b.dataset.nom || '';
            const aCalories = Number(a.dataset.calories || '0');
            const bCalories = Number(b.dataset.calories || '0');
            const aTemps = Number(a.dataset.temps || '0');
            const bTemps = Number(b.dataset.temps || '0');
            const aDone = Number(a.dataset.instructionDone || '0');
            const bDone = Number(b.dataset.instructionDone || '0');
            const aIndex = Number(a.dataset.index || '0');
            const bIndex = Number(b.dataset.index || '0');

            switch (sortValue) {
              case 'nom_asc': return aNom.localeCompare(bNom, 'fr');
              case 'nom_desc': return bNom.localeCompare(aNom, 'fr');
              case 'calories_desc': return bCalories - aCalories;
              case 'calories_asc': return aCalories - bCalories;
              case 'temps_asc': return aTemps - bTemps;
              case 'temps_desc': return bTemps - aTemps;
              case 'instruction_done': return bDone - aDone;
              case 'instruction_pending': return aDone - bDone;
              default: return aIndex - bIndex;
            }
          });

          tableRows.forEach((row) => { row.style.display = 'none'; });
          filtered.forEach((row) => {
            row.style.display = '';
            tbody.appendChild(row);
          });
        }

        searchInput?.addEventListener('input', applyFilterAndSort);
        searchInput?.addEventListener('keyup', applyFilterAndSort);
        sortSelect?.addEventListener('change', applyFilterAndSort);
        applyFilterAndSort();

        const toast = document.getElementById('form-toast');
        if (toast && toast.textContent.trim() !== '') {
          toast.style.display = 'block';
          window.setTimeout(function () {
            toast.style.display = 'none';
          }, 3200);
        }
      });
    </script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js" async></script>
    <script src="../assets/js/argon-dashboard-tailwind.js?v=1.0.1" async></script>
  </body>
</html>
