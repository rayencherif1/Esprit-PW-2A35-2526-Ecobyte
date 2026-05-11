<?php
require_once __DIR__ . '/../../controller/InstructionController.php';

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
    <link rel="apple-touch-icon" sizes="76x76" href="/2int/assets/img/apple-icon.png" />
    <link rel="icon" type="image/png" href="/2int/assets/img/favicon.png" />
    <title>Instructions - Argon Dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"><style>body{font-family:'Open Sans',sans-serif;}</style>
    <style>
      #search-instruction:focus {
        outline: none;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
      }
    </style>
  </head>
  <body class="m-0 font-sans text-base antialiased font-normal bg-gray-50 text-slate-500">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Indigo background -->
    <div style="position:fixed; top:0; left:256px; right:0; height:300px; background:#5e72e4; z-index:0;"></div>

    <main style="margin-left:256px; position:relative; z-index:1; min-height:100vh;">

        <div class="w-full px-10 py-10 mx-auto">
            <div class="mx-auto max-w-7xl">
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
                  <div class="flex items-center gap-2">
                    <a href="../../controller/InstructionController.php?sync_all=1" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold shadow-md transition" style="background-color: #10b981; color: #ffffff;">
                      <i class="fas fa-sync-alt"></i>
                      <span>Restaurer</span>
                    </a>
                    <a href="instruction-form.php" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold shadow-md transition" style="background-color: #3b82f6; color: #ffffff;">
                      <span style="font-size: 18px; font-weight: 700; line-height: 1;">+</span>
                      <span>Ajouter</span>
                    </a>
                  </div>
                </div>
                <?php if ($message) : ?>
                  <div class="mt-4 rounded-2xl border border-blue-200 bg-blue-50 px-6 py-4 text-sm text-blue-900">
                    <?php if ($message === 'ajoute') : ?>Fiche instruction ajoutee avec succes.<?php endif; ?>
                    <?php if ($message === 'modifie') : ?>Fiche instruction modifiee avec succes.<?php endif; ?>
                    <?php if ($message === 'supprime') : ?>Fiche instruction supprimee avec succes.<?php endif; ?>
                    <?php if ($message === 'restaure') : ?>Toutes les instructions manquantes ont été restaurées avec succès !<?php endif; ?>
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
                              <a href="instruction-form.php?id=<?= urlencode($r['id']) ?>" class="inline-flex items-center justify-center rounded-full px-3 py-2 text-xs font-semibold transition hover:opacity-90" style="background-color: #1d4ed8; color: #ffffff;"><span aria-hidden="true">&#x270F;&#xFE0F;</span></a>
                              <a href="../../controller/InstructionController.php?delete_instruction=<?= urlencode($r['id']) ?>" class="inline-flex items-center justify-center rounded-full px-3 py-2 text-xs font-semibold transition hover:opacity-90" style="background-color: #ef4444; color: #ffffff;"><span aria-hidden="true">&#x274C;</span></a>
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
    <script src="/2int/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/assets/js/argon-dashboard-tailwind.js"></script>
  </body>
</html>

