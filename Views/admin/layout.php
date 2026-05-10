<?php
/**
 * Layout back-office — s’inspire d’Argon (liens CSS/JS vers vos assets Argon).
 * Variables attendues : $pageTitle, $content (html capturé) — ici on utilise ob_start dans chaque vue ou inclusion directe.
 * Pour simplifier : chaque vue admin inclut ce fichier en premier avec $slot.
 */
$pageTitle = $pageTitle ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="<?= e(URL_ARGON_ASSETS) ?>/css/nucleo-icons.css" rel="stylesheet" />
    <link href="<?= e(URL_ARGON_ASSETS) ?>/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <link href="<?= e(URL_ARGON_ASSETS) ?>/css/argon-dashboard-tailwind.css?v=1.0.1" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="m-0 font-sans text-base antialiased font-normal dark:bg-slate-900 leading-default bg-gray-50 text-slate-500">
<div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>

<aside class="fixed inset-y-0 flex-wrap items-center justify-between block w-full p-0 my-4 overflow-y-auto antialiased transition-transform duration-200 -translate-x-full bg-white border-0 shadow-xl dark:shadow-none dark:bg-slate-850 max-w-64 ease-nav-brand z-990 xl:ml-6 rounded-2xl xl:left-0 xl:translate-x-0" aria-expanded="false">
    <div class="h-19 px-8 py-6">
        <span class="font-bold text-slate-700 dark:text-white">Nutrition — Admin</span>
        <p class="text-xs text-slate-500 mt-1">Gestion du contenu</p>
    </div>
    <hr class="h-px mt-0 bg-transparent bg-gradient-to-r from-transparent via-black/40 to-transparent" />
    <ul class="flex flex-col pl-0 mb-0">
        <li class="mt-0.5 w-full">
            <a class="py-2.7 text-sm my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 font-semibold text-slate-700" href="<?= e(ADMIN_URL) ?>/index.php?action=dashboard">
                <span class="ml-1">Tableau de bord</span>
            </a>
        </li>
        <li class="mt-0.5 w-full">
            <a class="py-2.7 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 text-slate-700" href="<?= e(ADMIN_URL) ?>/index.php?action=exercise_list">
                <span class="ml-1">Exercices (CRUD)</span>
            </a>
        </li>
        <li class="mt-0.5 w-full">
            <a class="py-2.7 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 text-slate-700" href="<?= e(ADMIN_URL) ?>/index.php?action=program_list">
                <span class="ml-1">Programmes (CRUD)</span>
            </a>
        </li>
        <li class="mt-0.5 w-full">
            <a class="py-2.7 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 text-blue-600" href="<?= e(BASE_URL) ?>/index.php?action=home">
                <span class="ml-1">Voir le site</span>
            </a>
        </li>
    </ul>
</aside>

<main class="relative h-full max-h-screen transition-all duration-200 xl:ml-68 rounded-xl">
    <div class="w-full px-6 mx-auto">
        <div class="flex flex-wrap mt-6">
            <div class="w-full max-w-full px-3">
                <?php
                // Le contenu spécifique de la page est injecté ici
                if (isset($slot)) {
                    echo $slot; // @var string $slot
                }
                ?>
            </div>
        </div>
    </div>
</main>

<script src="<?= e(URL_ARGON_ASSETS) ?>/js/argon-dashboard-tailwind.min.js?v=1.0.1" async></script>
</body>
</html>
