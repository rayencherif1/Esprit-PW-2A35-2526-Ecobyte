<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des Utilisateurs - Admin</title>
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- Nucleo Icons -->
    <link href="view/back/build/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="view/back/build/assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Main Styling -->
    <link href="view/back/build/assets/css/argon-dashboard-tailwind.css?v=1.0.1" rel="stylesheet" />
</head>
<body class="m-0 font-sans text-base antialiased font-normal dark:bg-slate-900 leading-default bg-gray-50 text-slate-500">
    <div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>

    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
        <nav class="relative flex flex-wrap items-center justify-between px-0 py-2 mx-6 transition-all ease-in shadow-none duration-250 rounded-2xl lg:flex-nowrap lg:justify-start" navbar-main navbar-scroll="false">
            <div class="flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit">
                <nav>
                    <h6 class="mb-0 font-bold text-white capitalize">Utilisateurs</h6>
                </nav>
            </div>
        </nav>

        <div class="w-full px-6 py-6 mx-auto">
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex flex-wrap justify-between items-center gap-4">
                            <h6 class="dark:text-white">Liste des Utilisateurs</h6>
                            
                            <div class="flex items-center gap-4 flex-wrap">
                                 <form action="" method="GET" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                                     <input type="hidden" name="section" value="back">
                                     <input type="hidden" name="action" value="users">
                                     
                                     <!-- Search -->
                                     <div class="relative flex flex-wrap items-stretch transition-all rounded-lg ease w-full lg:w-64">
                                         <span class="text-sm ease leading-5.6 absolute z-50 -ml-px flex h-full items-center whitespace-nowrap rounded-lg rounded-tr-none rounded-br-none border border-r-0 border-transparent bg-transparent py-2 px-2.5 text-center font-normal text-slate-500 transition-all">
                                             <i class="fas fa-search"></i>
                                         </span>
                                         <input type="text" id="searchInput" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" class="pl-9 text-sm focus:shadow-primary-outline dark:bg-slate-850 dark:text-white pb-2.5 pt-2.5 w-full leading-5.6 ease block appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none" placeholder="Rechercher un utilisateur..." autocomplete="off" style="background-color: #fffde7 !important;" />
                                     </div>

                                     <!-- Sort Toggle Button -->
                                     <div class="relative flex items-center">
                                         <button type="button" id="toggleSortBtn" class="px-4 py-2 text-sm font-bold text-white transition-all bg-blue-500 rounded-lg shadow-md hover:-translate-y-px" style="background-color: #5e72e4 !important;">
                                             <i class="fas fa-sort mr-1"></i> Tri
                                         </button>
                                         
                                         <!-- Sort Options (Hidden by default) -->
                                         <div id="sortOptions" class="hidden absolute top-full right-0 mt-2 p-3 bg-white dark:bg-slate-850 border border-gray-100 dark:border-white/10 rounded-xl shadow-xl z-50 flex flex-col gap-2 min-w-[200px]">
                                             <div class="flex flex-col gap-1">
                                                 <label class="text-xxs font-bold uppercase text-slate-400 px-1">Critère</label>
                                                 <select id="sortField" class="text-xs focus:shadow-primary-outline dark:bg-slate-850 dark:text-white pb-2 pt-2 rounded-lg border border-solid border-gray-200 bg-white px-3 outline-none transition-all focus:border-blue-500">
                                                     <option value="date_creation" <?php echo ($sort === 'date_creation') ? 'selected' : ''; ?>>Date de création</option>
                                                     <option value="poids" <?php echo ($sort === 'poids') ? 'selected' : ''; ?>>Poids (Physique)</option>
                                                     <option value="taille" <?php echo ($sort === 'taille') ? 'selected' : ''; ?>>Hauteur (Physique)</option>
                                                 </select>
                                             </div>
                                             
                                             <div class="flex flex-col gap-1">
                                                 <label class="text-xxs font-bold uppercase text-slate-400 px-1">Ordre</label>
                                                 <select id="sortOrder" class="text-xs focus:shadow-primary-outline dark:bg-slate-850 dark:text-white pb-2 pt-2 rounded-lg border border-solid border-gray-200 bg-white px-3 outline-none transition-all focus:border-blue-500">
                                                     <option value="DESC" <?php echo ($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
                                                     <option value="ASC" <?php echo ($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                                                 </select>
                                             </div>
                                         </div>
                                     </div>
                                 </form>

                                <div class="flex items-center gap-2">
                                    <a id="exportPdfBtn" href="?section=back&action=exportPDF<?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?>" class="inline-block px-4 py-2 text-xs font-bold text-center text-white uppercase align-middle transition-all bg-red-600 rounded-lg shadow-md hover:-translate-y-px" style="background-color: #f5365c !important; color: white !important; min-width: 80px;">
                                        <i class="fas fa-file-pdf mr-1"></i> PDF
                                    </a>
                                    <a href="?section=back&action=addUser" class="inline-block px-4 py-2 text-xs font-bold text-center text-white uppercase align-middle transition-all bg-blue-500 rounded-lg shadow-md hover:-translate-y-px" style="background-color: #5e72e4 !important; color: white !important;">
                                        Ajouter un utilisateur
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="flex-auto px-0 pt-0 pb-2">
                            <div class="p-0 overflow-x-auto">
                                <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
                                    <thead class="align-bottom">
                                        <tr>
                                            <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Utilisateur</th>
                                            <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Téléphone</th>
                                             <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Physique</th>
                                             <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Adresse</th>
                                             <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Date</th>
                                            <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-collapse border-solid shadow-none dark:border-white/40 dark:text-white tracking-none whitespace-nowrap text-slate-400 opacity-70">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usersTableBody">
                                        <?php require __DIR__ . '/users_list_partial.php'; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Statistiques -->
            <?php
                // Calcul des statistiques
                $totalUsers = count($users);
                $totalPoids = 0;
                $countPoids = 0;
                $totalTaille = 0;
                $countTaille = 0;
                $bannedUsers = 0;

                $weightDistribution = [
                    '< 50 kg' => 0,
                    '50 - 70 kg' => 0,
                    '70 - 90 kg' => 0,
                    '> 90 kg' => 0
                ];

                foreach ($users as $u) {
                    if (!empty($u['poids']) && is_numeric($u['poids'])) {
                        $p = $u['poids'];
                        $totalPoids += $p;
                        $countPoids++;
                        
                        if ($p < 50) $weightDistribution['< 50 kg']++;
                        elseif ($p <= 70) $weightDistribution['50 - 70 kg']++;
                        elseif ($p <= 90) $weightDistribution['70 - 90 kg']++;
                        else $weightDistribution['> 90 kg']++;
                    }
                    if (!empty($u['taille']) && is_numeric($u['taille'])) {
                        $totalTaille += $u['taille'];
                        $countTaille++;
                    }
                    if (!empty($u['ban_until']) && strtotime($u['ban_until']) > time()) {
                        $bannedUsers++;
                    }
                }

                $avgPoids = $countPoids > 0 ? round($totalPoids / $countPoids, 1) : 0;
                $avgTaille = $countTaille > 0 ? round($totalTaille / $countTaille, 1) : 0;
                $activeUsers = $totalUsers - $bannedUsers;
            ?>
            <div class="flex flex-wrap -mx-3 mt-6">
                <!-- Total Utilisateurs -->
                <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="flex-auto p-4">
                            <div class="flex flex-row -mx-3">
                                <div class="flex-none w-2/3 max-w-full px-3">
                                    <div>
                                        <p class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60">Total Utilisateurs</p>
                                        <h5 class="mb-2 font-bold dark:text-white"><?php echo $totalUsers; ?></h5>
                                    </div>
                                </div>
                                <div class="px-3 text-right basis-1/3">
                                    <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-blue-500 to-violet-500 flex items-center justify-center">
                                        <i class="fas fa-users text-lg text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Utilisateurs Actifs -->
                <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="flex-auto p-4">
                            <div class="flex flex-row -mx-3">
                                <div class="flex-none w-2/3 max-w-full px-3">
                                    <div>
                                        <p class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60">Utilisateurs Actifs</p>
                                        <h5 class="mb-2 font-bold dark:text-white"><?php echo $activeUsers; ?></h5>
                                        <p class="mb-0 dark:text-white dark:opacity-60 text-xs">
                                            <span class="font-bold text-red-600"><?php echo $bannedUsers; ?></span> bannis
                                        </p>
                                    </div>
                                </div>
                                <div class="px-3 text-right basis-1/3">
                                    <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-emerald-500 to-teal-400 flex items-center justify-center">
                                        <i class="fas fa-user-check text-lg text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Poids Moyen -->
                <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="flex-auto p-4">
                            <div class="flex flex-row -mx-3">
                                <div class="flex-none w-2/3 max-w-full px-3">
                                    <div>
                                        <p class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60">Poids Moyen</p>
                                        <h5 class="mb-2 font-bold dark:text-white"><?php echo $avgPoids; ?> kg</h5>
                                    </div>
                                </div>
                                <div class="px-3 text-right basis-1/3">
                                    <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-orange-500 to-yellow-500 flex items-center justify-center">
                                        <i class="fas fa-weight text-lg text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Taille Moyenne -->
                <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="flex-auto p-4">
                            <div class="flex flex-row -mx-3">
                                <div class="flex-none w-2/3 max-w-full px-3">
                                    <div>
                                        <p class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60">Taille Moyenne</p>
                                        <h5 class="mb-2 font-bold dark:text-white"><?php echo $avgTaille; ?> cm</h5>
                                    </div>
                                </div>
                                <div class="px-3 text-right basis-1/3">
                                    <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-red-600 to-orange-600 flex items-center justify-center">
                                        <i class="fas fa-ruler-vertical text-lg text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Graphiques -->
            <div class="flex flex-wrap -mx-3 mt-6">
                <!-- Pie Chart -->
                <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 lg:w-1/3">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-4 pb-0 mb-0 rounded-t-4">
                            <h6 class="mb-0 dark:text-white font-bold">Statut des Utilisateurs</h6>
                        </div>
                        <div class="flex-auto p-4">
                            <div style="height: 250px; position: relative;">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bar Chart -->
                <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 lg:w-2/3">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-4 pb-0 mb-0 rounded-t-4">
                            <h6 class="mb-0 dark:text-white font-bold">Répartition des Poids</h6>
                        </div>
                        <div class="flex-auto p-4">
                            <div style="height: 250px; position: relative;">
                                <canvas id="weightChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
    <script>
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const sortField = document.getElementById('sortField');
        const sortOrder = document.getElementById('sortOrder');
        const toggleSortBtn = document.getElementById('toggleSortBtn');
        const sortOptions = document.getElementById('sortOptions');
        const tableBody = document.getElementById('usersTableBody');
        const exportPdfBtn = document.getElementById('exportPdfBtn');

        let currentSort = '<?php echo $sort; ?>';
        let currentOrder = '<?php echo $order; ?>';

        function updateResults() {
            const query = searchInput ? searchInput.value : '';
            
            // Mettre à jour le lien PDF
            if (exportPdfBtn) {
                const pdfUrl = `index.php?section=back&action=exportPDF&search=${encodeURIComponent(query)}&sort=${currentSort}&order=${currentOrder}`;
                exportPdfBtn.href = pdfUrl;
            }

            // AJAX update
            const ajaxUrl = `index.php?section=back&action=users&ajax=1&search=${encodeURIComponent(query)}&sort=${currentSort}&order=${currentOrder}`;
            
            fetch(ajaxUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Erreur réseau');
                    return response.text();
                })
                .then(html => {
                    tableBody.innerHTML = html;
                })
                .catch(err => {
                    console.error('Erreur AJAX:', err);
                });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(updateResults, 300);
            });
        }

        if (sortField) {
            sortField.addEventListener('change', function() {
                currentSort = this.value;
                updateResults();
            });
        }

        if (sortOrder) {
            sortOrder.addEventListener('change', function() {
                currentOrder = this.value;
                updateResults();
            });
        }

        if (toggleSortBtn && sortOptions) {
            toggleSortBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                sortOptions.classList.toggle('hidden');
            });

            // Fermer le menu si on clique ailleurs
            document.addEventListener('click', function(e) {
                if (!sortOptions.contains(e.target) && e.target !== toggleSortBtn) {
                    sortOptions.classList.add('hidden');
                }
            });
        }

            // Animation du dropdown ban (via délégation d'événement pour supporter l'AJAX)
            tableBody.addEventListener('mouseover', function(e) {
                const dropdown = e.target.closest('.ban-dropdown');
                if (dropdown) {
                    const menu = dropdown.querySelector('.ban-menu');
                    if (menu) {
                        menu.style.display = 'block';
                        setTimeout(() => menu.style.opacity = '1', 10);
                    }
                }
            });

            tableBody.addEventListener('mouseout', function(e) {
                const dropdown = e.target.closest('.ban-dropdown');
                if (dropdown) {
                    const menu = dropdown.querySelector('.ban-menu');
                    if (menu) {
                        menu.style.opacity = '0';
                        setTimeout(() => menu.style.display = 'none', 300);
                    }
                }
            });
    </script>
    <style>
        .ban-menu {
            transition: opacity 0.3s ease;
        }
    </style>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart for User Status
            const ctxStatus = document.getElementById('statusChart');
            if (ctxStatus) {
                new Chart(ctxStatus, {
                    type: 'doughnut',
                    data: {
                        labels: ['Actifs', 'Bannis'],
                        datasets: [{
                            data: [<?php echo $activeUsers; ?>, <?php echo $bannedUsers; ?>],
                            backgroundColor: ['#2dce89', '#f5365c'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }

            // Chart for Weight Distribution
            const ctxWeight = document.getElementById('weightChart');
            if (ctxWeight) {
                new Chart(ctxWeight, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_keys($weightDistribution)); ?>,
                        datasets: [{
                            label: 'Utilisateurs',
                            data: <?php echo json_encode(array_values($weightDistribution)); ?>,
                            backgroundColor: '#5e72e4',
                            borderRadius: 4,
                            barPercentage: 0.5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                },
                                grid: {
                                    borderDash: [5, 5],
                                    drawBorder: false,
                                    color: 'rgba(200, 200, 200, 0.2)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
