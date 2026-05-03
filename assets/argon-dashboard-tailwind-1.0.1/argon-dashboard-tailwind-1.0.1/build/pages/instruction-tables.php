<?php
require_once __DIR__ . '/../../../../../controller/InstructionController.php';

function instruction_resume(string $text, int $max = 56): string
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

$controller = new InstructionController();
$rows = $controller->listAll();
$message = $_GET['message'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png" />
    <link rel="icon" type="image/png" href="../assets/img/favicon.png" />
    <title>Instructions - Argon Dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link href="../assets/css/argon-dashboard-tailwind.css?v=1.0.1" rel="stylesheet" />
    <style>
      #search-instruction:focus {
        outline: none;
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
      }
    </style>
  </head>
  <body class="m-0 font-sans text-base antialiased font-normal bg-gray-50 text-slate-500">
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
            <a class="py-2.7 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors text-slate-700" href="tables.php">
              <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg">
                <i class="relative top-0 text-sm leading-normal text-orange-500 ni ni-calendar-grid-58"></i>
              </div>
              <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Recettes</span>
            </a>
          </li>
          <li class="mt-0.5 w-full">
            <a class="py-2.7 bg-emerald-500/30 text-sm my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 font-semibold text-slate-700 transition-colors" href="instruction-tables.php">
              <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg">
                <i class="fas fa-book-open relative top-0 text-sm leading-normal text-emerald-500"></i>
              </div>
              <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Instructions</span>
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
                    <h6>Gestion des instructions</h6>
                    <p class="mb-0 text-sm leading-normal text-slate-400">Image, nom, ingredients, preparation, nombre d'etapes et temps total.</p>
                  </div>
                  <a href="instruction-form.php" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold shadow-md transition" style="background-color: #10b981; color: #ffffff;">
                    <span style="font-size: 18px; font-weight: 700; line-height: 1;">+</span>
                    <span>Ajouter</span>
                  </a>
                </div>
                <?php if ($message) : ?>
                  <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-800">
                    <?php if ($message === 'ajoute') : ?>Fiche instruction ajoutee avec succes.<?php endif; ?>
                    <?php if ($message === 'modifie') : ?>Fiche instruction modifiee avec succes.<?php endif; ?>
                    <?php if ($message === 'supprime') : ?>Fiche instruction supprimee avec succes.<?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>

              <div class="px-6 mt-4 mb-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div class="flex w-full items-center gap-3">
                    <label class="text-sm font-semibold text-slate-600" for="search-instruction">Recherche</label>
                    <input id="search-instruction" type="text" placeholder="Rechercher..." class="flex-1 rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition duration-200" />
                  </div>
                </div>
              </div>

              <div class="flex-auto px-0 pt-0 pb-2">
                <div class="p-0 overflow-x-auto">
                  <table id="instruction-table" class="items-center w-full mb-0 align-top border-collapse text-slate-500">
                    <thead class="align-bottom">
                      <tr>
                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Image</th>
                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Nom</th>
                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Ingredients</th>
                        <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Preparation</th>
                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Etapes</th>
                        <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b text-xxs whitespace-nowrap text-slate-400 opacity-70">Temps</th>
                        <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b whitespace-nowrap text-slate-400 opacity-70">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($rows as $r) : ?>
                        <tr>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap">
                            <img src="<?= htmlspecialchars($r['image']) ?>" alt="<?= htmlspecialchars($r['nom']) ?>" class="h-10 w-10 rounded-xl object-cover border border-slate-200" />
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap">
                            <span class="text-sm font-semibold"><?= htmlspecialchars($r['nom']) ?></span>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b max-w-xs">
                            <span class="text-sm"><?= htmlspecialchars(instruction_resume($r['ingredients'] ?? '', 64)) ?></span>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b max-w-xs">
                            <span class="text-sm"><?= htmlspecialchars(instruction_resume($r['preparation'] ?? '', 64)) ?></span>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap">
                            <span class="text-sm font-semibold"><?= htmlspecialchars((string) $r['nombreEtapes']) ?></span>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap">
                            <span class="text-sm"><?= htmlspecialchars((string) $r['temps']) ?> min</span>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap">
                            <div class="flex justify-center gap-2">
                              <a href="instruction-form.php?id=<?= urlencode($r['id']) ?>" class="inline-flex items-center justify-center rounded-full px-3 py-2 text-xs font-semibold transition hover:opacity-90" style="background-color: #047857; color: #ffffff;"><span aria-hidden="true">&#x270F;&#xFE0F;</span></a>
                              <a href="/recette/controller/InstructionController.php?delete_instruction=<?= urlencode($r['id']) ?>" class="inline-flex items-center justify-center rounded-full px-3 py-2 text-xs font-semibold transition hover:opacity-90" style="background-color: #ef4444; color: #ffffff;"><span aria-hidden="true">&#x274C;</span></a>
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
        </div>
      </div>
    </main>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-instruction');
        const tableRows = document.querySelectorAll('#instruction-table tbody tr');
        searchInput.addEventListener('input', function () {
          const query = this.value.toLowerCase();
          tableRows.forEach((row) => {
            row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
          });
        });
      });
    </script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js" async></script>
    <script src="../assets/js/argon-dashboard-tailwind.js?v=1.0.1" async></script>
  </body>
</html>
