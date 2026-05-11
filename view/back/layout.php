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
    <div class="h-19">
        <a class="block px-8 py-6 m-0 text-sm whitespace-nowrap dark:text-white text-slate-700" href="#">
            <span style="font-family:'Poppins',sans-serif;font-weight:800;font-size:1.1rem;">
                🌿 <span style="color:#4caf50;">ECO</span><span style="color:#ff6b35;">BYTE</span>
            </span>
        </a>
    </div>
    <hr class="h-px mt-0 bg-transparent bg-gradient-to-r from-transparent via-black/40 to-transparent" />
    <div class="items-center block w-auto max-h-screen overflow-auto h-sidenav grow basis-full">
        <ul class="flex flex-col pl-0 mb-0">
            <li class="px-4 py-1 text-xs text-slate-400 uppercase tracking-wider">Modules</li>
            
            <li class="mt-0.5 w-full">
                <a id="sidebar-back-boutique" class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="#">
                    <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-blue-500 ni ni-shop"></i></div>
                    <span>🛒 Boutique Bio</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a id="sidebar-back-fitness" class="py-2.7 bg-blue-500/13 dark:text-white dark:opacity-80 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 font-semibold text-slate-700 transition-colors" href="<?= e(ADMIN_URL) ?>/index.php?action=dashboard">
                    <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-orange-500 ni ni-hat-3"></i></div>
                    <span>🏋️ Fitness & Sport</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a id="sidebar-back-sante" class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="#">
                    <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-red-500 ni ni-alert"></i></div>
                    <span>⚠️ Santé & Allergies</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a id="sidebar-back-blog" class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="#">
                    <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-cyan-500 ni ni-blog"></i></div>
                    <span>📝 Blog & Actu</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a id="sidebar-back-cuisine" class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="#">
                    <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-emerald-500 ni ni-book-bookmark"></i></div>
                    <span>🥗 Cuisine & Recettes</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a id="sidebar-back-users" class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="#">
                    <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-purple-500 ni ni-single-02"></i></div>
                    <span>👤 Utilisateurs</span>
                </a>
            </li>

            <hr class="h-px my-1 bg-transparent bg-gradient-to-r from-transparent via-black/20 to-transparent" />
            <li class="px-4 py-1 text-xs text-slate-400 uppercase tracking-wider">Gestion Fitness</li>

            <li class="mt-0.5 w-full">
                <a class="py-2.7 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 text-slate-700 hover:bg-gray-100 rounded-lg transition-colors" href="<?= e(ADMIN_URL) ?>/index.php?action=exercise_list">
                    <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-gray-500 ni ni-bullet-list-67"></i></div>
                    <span class="ml-1">Exercices (CRUD)</span>
                </a>
            </li>
            <li class="mt-0.5 w-full">
                <a class="py-2.7 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 text-slate-700 hover:bg-gray-100 rounded-lg transition-colors" href="<?= e(ADMIN_URL) ?>/index.php?action=program_list">
                    <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-gray-500 ni ni-collection"></i></div>
                    <span class="ml-1">Programmes (CRUD)</span>
                </a>
            </li>

            <hr class="h-px my-1 bg-transparent bg-gradient-to-r from-transparent via-black/20 to-transparent" />

            <li class="mt-0.5 w-full">
                <a id="sidebar-logout" class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="#">
                    <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">
                        <i class="relative top-0 text-sm leading-normal text-slate-400 ni ni-button-power"></i>
                    </div>
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </div>
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
