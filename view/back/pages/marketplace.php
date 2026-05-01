<!--
=========================================================
* Argon Dashboard 2 Tailwind - v1.0.1
=========================================================
* Adapted for EcoBite Marketplace
-->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoBite - Marketplace</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="/marketplace/view/back/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="/marketplace/view/back/assets/css/nucleo-svg.css" rel="stylesheet" />
    <link href="/marketplace/view/back/assets/css/argon-dashboard-tailwind.css?v=1.0.1" rel="stylesheet" />
    <style>
        .btn-action {
            background: #4CAF50;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            text-decoration: none;
            margin: 0 3px;
            display: inline-block;
            border: none;
            cursor: pointer;
        }
        .btn-action.danger { background: #f44336; }
        .btn-action.warning { background: #ff9800; }
        .btn-action.info { background: #17a2b8; }
        .btn-action.dark { background: #6c757d; }
        .btn-action.primary { background: #007bff; }
        .btn-action:hover { opacity: 0.8; color: white; }
        .table-crud { width: 100%; border-collapse: collapse; }
        .table-crud th, .table-crud td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table-crud th { background: #f5f5f5; color: #333; }
        .form-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .form-modal.active { display: flex; }
        .form-modal-content { background: white; padding: 30px; border-radius: 20px; width: 500px; max-width: 90%; }
        .form-modal-content input, .form-modal-content textarea, .form-modal-content select { width: 100%; padding: 8px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; }
        .close-modal { float: right; cursor: pointer; font-size: 24px; }
        .order-row td { background: #f8f9fa; padding: 15px; }
        .order-table { width: 100%; background: white; border-collapse: collapse; margin-top: 5px; }
        .order-table th, .order-table td { border: 1px solid #ddd; padding: 8px; }
        .error-message { color: red; font-size: 12px; margin-top: 5px; display: none; }
        .error-border { border-color: red !important; }
        .required-star { color: red; margin-left: 3px; }
        .form-group label { font-weight: 500; }
    </style>
</head>
<body class="m-0 font-sans text-base antialiased font-normal dark:bg-slate-900 leading-default bg-gray-50 text-slate-500">
    <div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>

    <!-- CHARGEMENT DES DONNÉES -->
    <?php
    session_start();
    require_once '../../../controller/ProduitController.php';
    require_once '../../../controller/CategorieController.php';
    require_once '../../../controller/CommandeController.php';
    
    $produitController = new ProduitController();
    $categorieController = new CategorieController();
    $commandeController = new CommandeController();
    
    $produits = $produitController->getAllProduits();
    $categories = $categorieController->getAllCategories();
    $commandes = $commandeController->getAllCommandes();

    // Regrouper les commandes par produit
    $commandesParProduit = [];
    foreach ($commandes as $cmd) {
        $produitsCmd = $commandeController->getProduitsByCommandeId($cmd['id']);
        foreach ($produitsCmd as $prodCmd) {
            $commandesParProduit[$prodCmd['id']][] = [
                'id' => $cmd['id'],
                'client' => $cmd['client_nom'],
                'email' => $cmd['client_email'],
                'date' => $cmd['date_commande'],
                'total' => $cmd['total'],
                'quantite' => $prodCmd['quantite']
            ];
        }
    }
    
    // Calcul des statistiques
    $totalProduits = count($produits);
    $totalCategories = count($categories);
    $totalCommandes = count($commandes);
    // Chiffre d'affaires réel (basé sur commande_produits)
    $dbStats = null;
    try {
        require_once '../../../model/Produit.php';
        $m = new Produit();
        $dbStats = $m->getDb();
    } catch (Exception $e) {
        $dbStats = null;
    }

    $revenuTotal = 0;
    $panierMoyen = 0;
    $totalItemsVendus = 0;
    $topProduits = [];
    $caParMois = [];
    if ($dbStats) {
        // CA total + nb commandes
        $row = $dbStats->query("SELECT 
                COALESCE(SUM(cp.quantite * cp.prix_unitaire), 0) AS ca_total,
                COUNT(DISTINCT cp.commande_id) AS nb_commandes,
                COALESCE(SUM(cp.quantite), 0) AS items_total
            FROM commande_produits cp")->fetch();
        $revenuTotal = floatval($row['ca_total'] ?? 0);
        $nbCmd = intval($row['nb_commandes'] ?? 0);
        $totalItemsVendus = intval($row['items_total'] ?? 0);
        $panierMoyen = $nbCmd > 0 ? ($revenuTotal / $nbCmd) : 0;

        // CA par mois (6 derniers mois)
        $stmt = $dbStats->query("
            SELECT DATE_FORMAT(c.date_commande, '%Y-%m') AS ym,
                   COALESCE(SUM(cp.quantite * cp.prix_unitaire), 0) AS ca
            FROM commandes c
            JOIN commande_produits cp ON cp.commande_id = c.id
            WHERE c.date_commande >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY ym
            ORDER BY ym ASC
        ");
        $caParMois = $stmt->fetchAll() ?: [];

        // Top produits (quantité vendue)
        $stmt = $dbStats->query("
            SELECT p.nom, COALESCE(SUM(cp.quantite),0) AS qte
            FROM commande_produits cp
            JOIN produits p ON p.id = cp.produit_id
            GROUP BY p.id, p.nom
            ORDER BY qte DESC
            LIMIT 5
        ");
        $topProduits = $stmt->fetchAll() ?: [];
    }
    $produitsRupture = 0;
    foreach($produits as $p) {
        if($p['stock'] <= 0) $produitsRupture++;
    }

    // ======== DATA CHARTS (dynamiques) ========
    // Ventes par catégorie (Top 6) — basé sur commandes réelles si possible
    $ventesByCat = [];
    if ($dbStats) {
        $stmt = $dbStats->query("
            SELECT COALESCE(cat.nom,'Sans catégorie') AS categorie,
                   COALESCE(SUM(cp.quantite),0) AS qte
            FROM commande_produits cp
            JOIN produits p ON p.id = cp.produit_id
            LEFT JOIN categories cat ON cat.id = p.categorie_id
            GROUP BY categorie
            ORDER BY qte DESC
            LIMIT 6
        ");
        $rows = $stmt->fetchAll() ?: [];
        foreach ($rows as $r) $ventesByCat[$r['categorie']] = intval($r['qte']);
    } else {
        foreach ($produits as $p) {
            $cat = $p['categorie_nom'] ?? 'Sans catégorie';
            $ventesByCat[$cat] = ($ventesByCat[$cat] ?? 0) + intval($p['ventes'] ?? 0);
        }
        arsort($ventesByCat);
        $ventesByCat = array_slice($ventesByCat, 0, 6, true);
    }

    // Stock: répartition (rupture/faible/ok)
    $stockStats = ['Rupture' => 0, 'Faible (<5)' => 0, 'OK' => 0];
    foreach ($produits as $p) {
        $s = intval($p['stock'] ?? 0);
        if ($s <= 0) $stockStats['Rupture']++;
        elseif ($s < 5) $stockStats['Faible (<5)']++;
        else $stockStats['OK']++;
    }

    // Promos
    $promoCount = 0;
    foreach ($produits as $p) if (!empty($p['is_promo'])) $promoCount++;
    $promoStats = ['En promo' => $promoCount, 'Hors promo' => max(0, count($produits) - $promoCount)];
    
    // Messages de succès/erreur
    $successMessage = $_SESSION['success_message'] ?? '';
    $errorMessage = $_SESSION['error_message'] ?? '';
    unset($_SESSION['success_message'], $_SESSION['error_message']);
    ?>

    <!-- SIDEBAR -->
    <aside class="fixed inset-y-0 flex-wrap items-center justify-between block w-full p-0 my-4 overflow-y-auto antialiased transition-transform duration-200 -translate-x-full bg-white border-0 shadow-xl dark:shadow-none dark:bg-slate-850 max-w-64 ease-nav-brand z-990 xl:ml-6 rounded-2xl xl:left-0 xl:translate-x-0">
        <div class="h-19">
            <a class="block px-8 py-6 m-0 text-sm whitespace-nowrap dark:text-white text-slate-700" href="/marketplace/view/back/pages/marketplace.php">
                <img src="/marketplace/view/front/images/logo-ecobite.jpg" alt="EcoBite" style="height: 35px;">
            </a>
        </div>
        <hr class="h-px mt-0 bg-transparent bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent" />
        <div class="items-center block w-auto max-h-screen overflow-auto h-sidenav grow basis-full">
            <ul class="flex flex-col pl-0 mb-0">
                <li class="mt-0.5 w-full"><a class="py-2.7 bg-blue-500/13 dark:text-white dark:opacity-80 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 font-semibold text-slate-700 transition-colors" href="/marketplace/view/back/pages/marketplace.php"><div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-blue-500 ni ni-shop"></i></div><span>Marketplace</span></a></li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="#"><div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-orange-500 ni ni-hat-3"></i></div><span>Coaching</span></a></li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="#"><div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-red-500 ni ni-alert"></i></div><span>Allergie</span></a></li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="#"><div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-cyan-500 ni ni-blog"></i></div><span>Blog</span></a></li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="#"><div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-emerald-500 ni ni-book-bookmark"></i></div><span>Recette</span></a></li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="#"><div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-purple-500 ni ni-single-02"></i></div><span>User</span></a></li>
                <li class="mt-0.5 w-full">
                    <div class="relative">
                        <a class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80 cursor-pointer" onclick="toggleExportMenu()">
                            <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-red-500 ni ni-cloud-download"></i></div>
                            <span>📥 Export PDF</span>
                        </a>
                        <div id="exportMenu" class="hidden absolute left-0 mt-0 w-48 bg-white dark:bg-slate-850 rounded-lg shadow-lg z-50">
                            <a href="/marketplace/index.php?controller=export&action=exportProduitsPDF" class="block px-4 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                                📦 Export Produits
                            </a>
                            <a href="/marketplace/index.php?controller=export&action=exportCommandesPDF" class="block px-4 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                                🛒 Export Commandes
                            </a>
                            <a href="/marketplace/index.php?controller=export&action=exportCategoriesPDF" class="block px-4 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                                📁 Export Catégories
                            </a>
                            <hr class="my-1 border-gray-200 dark:border-gray-700">
                            <a href="/marketplace/index.php?controller=export&action=exportRapportCompletPDF" class="block px-4 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 font-semibold">
                                📊 Rapport Complet
                            </a>
                        </div>
                    </div>
                </li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80" href="/marketplace/index.php?controller=auth&action=logout"><div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5"><i class="relative top-0 text-sm leading-normal text-slate-400 ni ni-button-power"></i></div><span>Déconnexion</span></a></li>
            </ul>
        </div>
    </aside>

    <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
        <!-- Navbar -->
        <nav class="relative flex flex-wrap items-center justify-between px-0 py-2 mx-6 transition-all ease-in shadow-none duration-250 rounded-2xl lg:flex-nowrap lg:justify-start">
            <div class="flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit">
                <nav>
                    <ol class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16">
                        <li class="text-sm leading-normal"><a class="text-white opacity-50" href="javascript:;">Pages</a></li>
                        <li class="text-sm pl-2 capitalize leading-normal text-white before:float-left before:pr-2 before:text-white before:content-['/']">Marketplace</li>
                    </ol>
                    <h6 class="mb-0 font-bold text-white capitalize">Gestion Marketplace</h6>
                </nav>
                <div class="flex items-center mt-2 grow sm:mt-0 sm:mr-6 md:mr-0 lg:flex lg:basis-auto">
                    <ul class="flex flex-row justify-end pl-0 mb-0 list-none md-max:w-full">
                        <li class="flex items-center"><a href="/marketplace/index.php?controller=auth&action=logout" class="block px-0 py-2 text-sm font-semibold text-white"><i class="fa fa-sign-out-alt sm:mr-1"></i><span class="hidden sm:inline"> Déconnexion</span></a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- STATISTIQUES -->
        <div class="w-full px-6 py-6 mx-auto">
            <div class="flex flex-wrap -mx-3">
                <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="flex-auto p-4">
                            <div class="flex flex-row -mx-3">
                                <div class="flex-none w-2/3 max-w-full px-3">
                                    <div>
                                        <p class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60">Total Produits</p>
                                        <h5 class="mb-2 font-bold dark:text-white"><?= $totalProduits ?></h5>
                                        <p class="mb-0 dark:text-white dark:opacity-60">
                                            <span class="text-sm font-bold leading-normal text-red-500"><?= $produitsRupture ?></span> en rupture
                                        </p>
                                    </div>
                                </div>
                                <div class="px-3 text-right basis-1/3">
                                    <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-blue-500 to-violet-500">
                                        <i class="ni leading-none ni-box-2 text-lg relative top-3.5 text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="flex-auto p-4">
                            <div class="flex flex-row -mx-3">
                                <div class="flex-none w-2/3 max-w-full px-3">
                                    <div>
                                        <p class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60">Catégories</p>
                                        <h5 class="mb-2 font-bold dark:text-white"><?= $totalCategories ?></h5>
                                        <p class="mb-0 dark:text-white dark:opacity-60">Actives sur le site</p>
                                    </div>
                                </div>
                                <div class="px-3 text-right basis-1/3">
                                    <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-emerald-500 to-teal-400">
                                        <i class="ni leading-none ni-paper-diploma text-lg relative top-3.5 text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="flex-auto p-4">
                            <div class="flex flex-row -mx-3">
                                <div class="flex-none w-2/3 max-w-full px-3">
                                    <div>
                                        <p class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60">Commandes</p>
                                        <h5 class="mb-2 font-bold dark:text-white"><?= $totalCommandes ?></h5>
                                        <p class="mb-0 dark:text-white dark:opacity-60">Total reçues</p>
                                    </div>
                                </div>
                                <div class="px-3 text-right basis-1/3">
                                    <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-orange-500 to-yellow-500">
                                        <i class="ni leading-none ni-cart text-lg relative top-3.5 text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full max-w-full px-3 sm:w-1/2 sm:flex-none xl:w-1/4">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="flex-auto p-4">
                            <div class="flex flex-row -mx-3">
                                <div class="flex-none w-2/3 max-w-full px-3">
                                    <div>
                                        <p class="mb-0 font-sans text-sm font-semibold leading-normal uppercase dark:text-white dark:opacity-60">Revenus</p>
                                        <h5 class="mb-2 font-bold dark:text-white"><?= number_format($revenuTotal, 2) ?> DT</h5>
                                        <p class="mb-0 dark:text-white dark:opacity-60">Chiffre d'affaires</p>
                                    </div>
                                </div>
                                <div class="px-3 text-right basis-1/3">
                                    <div class="inline-block w-12 h-12 text-center rounded-circle bg-gradient-to-tl from-red-600 to-orange-600">
                                        <i class="ni leading-none ni-money-coins text-lg relative top-3.5 text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DASHBOARD ANALYTICS (Charts) -->
        <div class="w-full px-6 pb-6 mx-auto">
            <div class="flex flex-wrap -mx-3">
                <div class="w-full max-w-full px-3 mb-6 lg:mb-0 lg:w-7/12">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex justify-between items-center">
                            <h6 class="dark:text-white">📈 Ventes par catégorie (Top)</h6>
                            <a href="/marketplace/index.php?controller=export&action=exportStatsPDF" class="btn-action info">Exporter Stats PDF</a>
                        </div>
                        <div class="p-6">
                            <canvas id="chartVentesByCat" height="140"></canvas>
                        </div>
                    </div>
                </div>
                <div class="w-full max-w-full px-3 lg:w-5/12">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border mb-6">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent">
                            <h6 class="dark:text-white">📦 Stock (répartition)</h6>
                        </div>
                        <div class="p-6">
                            <canvas id="chartStock" height="140"></canvas>
                        </div>
                    </div>
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent">
                            <h6 class="dark:text-white">🏷️ Promotions</h6>
                        </div>
                        <div class="p-6">
                            <canvas id="chartPromo" height="140"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ANALYTICS (Revenue + Top produits) -->
        <div class="w-full px-6 pb-6 mx-auto">
            <div class="flex flex-wrap -mx-3">
                <div class="w-full max-w-full px-3 mb-6 lg:mb-0 lg:w-7/12">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent">
                            <h6 class="dark:text-white">💰 Chiffre d'affaires (6 derniers mois)</h6>
                            <p class="text-sm" style="margin-top:6px; color:#6b7280;">
                                Panier moyen: <strong><?= number_format($panierMoyen, 2) ?> DT</strong> • Articles vendus: <strong><?= (int)$totalItemsVendus ?></strong>
                            </p>
                        </div>
                        <div class="p-6">
                            <canvas id="chartCaMois" height="140"></canvas>
                        </div>
                    </div>
                </div>
                <div class="w-full max-w-full px-3 lg:w-5/12">
                    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent">
                            <h6 class="dark:text-white">🏆 Top produits (quantités vendues)</h6>
                        </div>
                        <div class="p-6">
                            <canvas id="chartTopProduits" height="140"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION CATÉGORIES (en haut) -->
        <div class="w-full px-6 py-6 mx-auto">
            <?php if(!empty($successMessage)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($successMessage) ?>
            </div>
            <?php endif; ?>
            <?php if(!empty($errorMessage)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
            <?php endif; ?>
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex justify-between items-center">
                            <h6 class="dark:text-white">📁 Catégories</h6>
                            <button class="btn-action" style="background:#28a745;" onclick="openModal('addCategoryModal')">+ Ajouter une catégorie</button>
                        </div>
                        <div class="flex-auto px-0 pt-0 pb-2">
                            <div class="p-0 overflow-x-auto">
                                <table class="table-crud">
                                    <thead>
                                        <tr>
                                            <th>Nom</th><th>Description</th><th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($categories as $c): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($c['nom']) ?></td>
                                            <td><?= htmlspecialchars($c['description']) ?></td>
                                            <td>
                                                <button class="btn-action warning" onclick='openEditCategoryModal(<?= (int)$c["id"] ?>, <?= json_encode((string)($c["nom"] ?? ""), JSON_UNESCAPED_UNICODE) ?>, <?= json_encode((string)($c["description"] ?? ""), JSON_UNESCAPED_UNICODE) ?>)'>Modifier</button>
                                                <a href="/marketplace/index.php?controller=categorie&action=delete&id=<?= $c['id'] ?>" class="btn-action danger" onclick="return confirm('Supprimer cette catégorie ?')">Supprimer</a>
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

        <!-- SECTION PRODUITS (en dessous) -->
        <div class="w-full px-6 py-6 mx-auto">
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex justify-between items-center">
                            <h6 class="dark:text-white">📦 Produits</h6>
                            <button class="btn-action" style="background:#28a745;" onclick="openModal('addProductModal')">+ Ajouter un produit</button>
                        </div>
                        <div class="flex-auto px-0 pt-0 pb-2">
                            <div class="p-0 overflow-x-auto">
                                <table class="table-crud">
                                    <thead>
                                        <tr>
                                            <th>Nom</th><th>Prix (DT)</th><th>Stock</th><th>Catégorie</th><th>Promo</th><th>Actions</th>
                                            <th>Commandes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($produits as $p): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($p['nom']) ?></td>
                                                <td><?= number_format($p['prix'], 2) ?> DT</td>
                                                <td><?= $p['stock'] ?></td>
                                                <td><?= htmlspecialchars($p['categorie_nom'] ?? '—') ?></td>
                                                <td>
                                                    <?php if (!empty($p['is_promo'])): ?>
                                                        <span class="btn-action" style="background:#ff9800;">Promo</span>
                                                        <?php if (isset($p['prix_promo']) && $p['prix_promo'] !== null && $p['prix_promo'] !== ''): ?>
                                                            <div style="font-size:12px; color:#333; margin-top:4px;">
                                                                <?= number_format($p['prix_promo'], 2) ?> DT
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn-action warning" onclick='openEditProductModal(
                                                        <?= (int)$p["id"] ?>,
                                                        <?= json_encode((string)($p["nom"] ?? ""), JSON_UNESCAPED_UNICODE) ?>,
                                                        <?= json_encode((float)($p["prix"] ?? 0), JSON_UNESCAPED_UNICODE) ?>,
                                                        <?= json_encode((int)($p["stock"] ?? 0), JSON_UNESCAPED_UNICODE) ?>,
                                                        <?= json_encode((string)($p["description"] ?? ""), JSON_UNESCAPED_UNICODE) ?>,
                                                        <?= json_encode(($p["categorie_id"] ?? null) !== null ? (int)$p["categorie_id"] : null, JSON_UNESCAPED_UNICODE) ?>,
                                                        <?= json_encode(!empty($p["is_promo"]) ? 1 : 0, JSON_UNESCAPED_UNICODE) ?>,
                                                        <?= json_encode((isset($p["prix_promo"]) && $p["prix_promo"] !== '' && $p["prix_promo"] !== null) ? (float)$p["prix_promo"] : null, JSON_UNESCAPED_UNICODE) ?>
                                                    )'>Modifier</button>
                                                    <a href="/marketplace/index.php?controller=produit&action=delete&id=<?= $p['id'] ?>" class="btn-action danger" onclick="return confirm('Supprimer ce produit ?')">Supprimer</a>
                                                </td>
                                                <td>
                                                    <button class="btn-action info" onclick="toggleOrders(<?= $p['id'] ?>)">📋 Voir commandes</button>
                                                </td>
                                            </tr>
                                            <tr id="orders-<?= $p['id'] ?>" style="display: none;" class="order-row">
                                                <td colspan="7">
                                                    <?php if (!empty($commandesParProduit[$p['id']])): ?>
                                                        <table class="order-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Client</th>
                                                                    <th>Email</th>
                                                                    <th>Date</th>
                                                                    <th>Total (DT)</th>
                                                                    <th>Quantité</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($commandesParProduit[$p['id']] as $ord): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($ord['client']) ?></td>
                                                                        <td><?= htmlspecialchars($ord['email']) ?></td>
                                                                        <td><?= $ord['date'] ?></td>
                                                                        <td><?= number_format($ord['total'], 2) ?> DT</td>
                                                                        <td><?= $ord['quantite'] ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    <?php else: ?>
                                                        <p class="text-muted p-2">Aucune commande pour ce produit.</p>
                                                    <?php endif; ?>
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

        <footer class="pt-4">
            <div class="w-full px-6 mx-auto">
                <div class="text-sm leading-normal text-center text-slate-500">
                    © 2026 - EcoBite Marketplace - Projet étudiant
                </div>
            </div>
        </footer>
    </main>

    <!-- MODAL AJOUT PRODUIT -->
    <div id="addProductModal" class="form-modal">
        <div class="form-modal-content">
            <span class="close-modal" onclick="closeModal('addProductModal')">&times;</span>
            <h3>Ajouter un produit</h3>
            <div id="addProductError" class="error-message"></div>
            <form id="addProductForm" action="/marketplace/index.php?controller=produit&action=store" method="POST" onsubmit="return validateAddProduct()">
                <div class="form-group">
                    <label>Nom du produit <span class="required-star">*</span></label>
                    <input type="text" name="nom" id="add_nom" placeholder="Nom du produit">
                </div>
                <div class="form-group">
                    <label>Prix (DT) <span class="required-star">*</span></label>
                    <input type="number" name="prix" id="add_prix" step="0.01" placeholder="Prix">
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" id="add_stock" placeholder="Stock" value="0">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="add_description" placeholder="Description"></textarea>
                </div>
                <div class="form-group">
                    <label>Catégorie <span class="required-star">*</span></label>
                    <select name="categorie_id" id="add_categorie_id">
                        <option value="">-- Sélectionnez une catégorie --</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" name="is_promo" id="add_is_promo" style="width:auto;">
                        Produit en promo
                    </label>
                </div>
                <div class="form-group">
                    <label>Prix promo (DT)</label>
                    <input type="number" name="prix_promo" id="add_prix_promo" step="0.01" placeholder="Ex: 9.90">
                </div>
                <button type="submit" class="btn-action">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- MODAL MODIFICATION PRODUIT -->
    <div id="editProductModal" class="form-modal">
        <div class="form-modal-content">
            <span class="close-modal" onclick="closeModal('editProductModal')">&times;</span>
            <h3>Modifier le produit</h3>
            <div id="editProductError" class="error-message"></div>
            <form id="editProductForm" action="/marketplace/index.php?controller=produit&action=update" method="POST" onsubmit="return validateEditProduct()">
                <input type="hidden" name="id" id="edit_product_id">
                <div class="form-group">
                    <label>Nom du produit <span class="required-star">*</span></label>
                    <input type="text" name="nom" id="edit_product_nom" placeholder="Nom du produit">
                </div>
                <div class="form-group">
                    <label>Prix (DT) <span class="required-star">*</span></label>
                    <input type="number" name="prix" step="0.01" id="edit_product_prix" placeholder="Prix">
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" id="edit_product_stock" placeholder="Stock">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_product_description" placeholder="Description"></textarea>
                </div>
                <div class="form-group">
                    <label>Catégorie <span class="required-star">*</span></label>
                    <select name="categorie_id" id="edit_product_categorie_id">
                        <option value="">-- Sélectionnez une catégorie --</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" name="is_promo" id="edit_is_promo" style="width:auto;">
                        Produit en promo
                    </label>
                </div>
                <div class="form-group">
                    <label>Prix promo (DT)</label>
                    <input type="number" name="prix_promo" id="edit_prix_promo" step="0.01" placeholder="Ex: 9.90">
                </div>
                <button type="submit" class="btn-action">Mettre à jour</button>
            </form>
        </div>
    </div>

    <!-- MODAL AJOUT CATÉGORIE -->
    <div id="addCategoryModal" class="form-modal">
        <div class="form-modal-content">
            <span class="close-modal" onclick="closeModal('addCategoryModal')">&times;</span>
            <h3>Ajouter une catégorie</h3>
            <div id="addCategoryError" class="error-message"></div>
            <form id="addCategoryForm" action="/marketplace/index.php?controller=categorie&action=store" method="POST" onsubmit="return validateAddCategory()">
                <div class="form-group">
                    <label>Nom de la catégorie <span class="required-star">*</span></label>
                    <input type="text" name="nom" id="add_cat_nom" placeholder="Nom de la catégorie">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="add_cat_description" placeholder="Description"></textarea>
                </div>
                <button type="submit" class="btn-action">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- MODAL MODIFICATION CATÉGORIE -->
    <div id="editCategoryModal" class="form-modal">
        <div class="form-modal-content">
            <span class="close-modal" onclick="closeModal('editCategoryModal')">&times;</span>
            <h3>Modifier la catégorie</h3>
            <div id="editCategoryError" class="error-message"></div>
            <form id="editCategoryForm" action="/marketplace/index.php?controller=categorie&action=update" method="POST" onsubmit="return validateEditCategory()">
                <input type="hidden" name="id" id="edit_category_id">
                <div class="form-group">
                    <label>Nom <span class="required-star">*</span></label>
                    <input type="text" name="nom" id="edit_category_nom" placeholder="Nom">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_category_description" placeholder="Description"></textarea>
                </div>
                <button type="submit" class="btn-action">Mettre à jour</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }
        
        function toggleExportMenu() {
            var menu = document.getElementById('exportMenu');
            menu.classList.toggle('hidden');
        }
        
        // Fermer le menu quand on clique ailleurs
        document.addEventListener('click', function(event) {
            var menu = document.getElementById('exportMenu');
            var toggle = event.target.closest('[onclick="toggleExportMenu()"]');
            if (!toggle && !menu.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
        
        function openEditProductModal(id, nom, prix, stock, description, categorie_id, is_promo, prix_promo) {
            document.getElementById('edit_product_id').value = id;
            document.getElementById('edit_product_nom').value = nom;
            document.getElementById('edit_product_prix').value = prix;
            document.getElementById('edit_product_stock').value = stock;
            document.getElementById('edit_product_description').value = description;
            if(categorie_id && categorie_id != 'null') document.getElementById('edit_product_categorie_id').value = categorie_id;
            document.getElementById('edit_is_promo').checked = !!is_promo;
            document.getElementById('edit_prix_promo').value = (prix_promo === null || prix_promo === 'null') ? '' : prix_promo;
            openModal('editProductModal');
        }
        
        function openEditCategoryModal(id, nom, description) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_nom').value = nom;
            document.getElementById('edit_category_description').value = description;
            openModal('editCategoryModal');
        }
        
        function toggleOrders(productId) {
            var row = document.getElementById('orders-' + productId);
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }
        
        // Validation AJOUT PRODUIT avec catégorie obligatoire
        function validateAddProduct() {
            let nom = document.getElementById('add_nom').value.trim();
            let prix = document.getElementById('add_prix').value;
            let stock = document.getElementById('add_stock').value;
            let categorieId = document.getElementById('add_categorie_id').value;
            let errorDiv = document.getElementById('addProductError');
            
            errorDiv.innerHTML = '';
            
            if (nom === '') {
                errorDiv.innerHTML = '❌ Le nom du produit est obligatoire';
                errorDiv.style.display = 'block';
                return false;
            }
            if (prix === '' || parseFloat(prix) <= 0) {
                errorDiv.innerHTML = '❌ Le prix doit être supérieur à 0';
                errorDiv.style.display = 'block';
                return false;
            }
            if (stock !== '' && parseInt(stock) < 0) {
                errorDiv.innerHTML = '❌ Le stock ne peut pas être négatif';
                errorDiv.style.display = 'block';
                return false;
            }
            if (categorieId === '') {
                errorDiv.innerHTML = '❌ Veuillez sélectionner une catégorie';
                errorDiv.style.display = 'block';
                return false;
            }
            errorDiv.style.display = 'none';
            return true;
        }
        
        // Validation MODIFICATION PRODUIT avec catégorie obligatoire
        function validateEditProduct() {
            let nom = document.getElementById('edit_product_nom').value.trim();
            let prix = document.getElementById('edit_product_prix').value;
            let stock = document.getElementById('edit_product_stock').value;
            let categorieId = document.getElementById('edit_product_categorie_id').value;
            let errorDiv = document.getElementById('editProductError');
            
            errorDiv.innerHTML = '';
            
            if (nom === '') {
                errorDiv.innerHTML = '❌ Le nom du produit est obligatoire';
                errorDiv.style.display = 'block';
                return false;
            }
            if (prix === '' || parseFloat(prix) <= 0) {
                errorDiv.innerHTML = '❌ Le prix doit être supérieur à 0';
                errorDiv.style.display = 'block';
                return false;
            }
            if (stock !== '' && parseInt(stock) < 0) {
                errorDiv.innerHTML = '❌ Le stock ne peut pas être négatif';
                errorDiv.style.display = 'block';
                return false;
            }
            if (categorieId === '') {
                errorDiv.innerHTML = '❌ Veuillez sélectionner une catégorie';
                errorDiv.style.display = 'block';
                return false;
            }
            errorDiv.style.display = 'none';
            return true;
        }
        
        function validateAddCategory() {
            let nom = document.getElementById('add_cat_nom').value.trim();
            let errorDiv = document.getElementById('addCategoryError');
            
            if (nom === '') {
                errorDiv.innerHTML = '❌ Le nom de la catégorie est obligatoire';
                errorDiv.style.display = 'block';
                return false;
            }
            errorDiv.style.display = 'none';
            return true;
        }
        
        function validateEditCategory() {
            let nom = document.getElementById('edit_category_nom').value.trim();
            let errorDiv = document.getElementById('editCategoryError');
            
            if (nom === '') {
                errorDiv.innerHTML = '❌ Le nom de la catégorie est obligatoire';
                errorDiv.style.display = 'block';
                return false;
            }
            errorDiv.style.display = 'none';
            return true;
        }
        
        window.onclick = function(event) {
            if (event.target.classList.contains('form-modal')) {
                event.target.classList.remove('active');
                let errors = document.querySelectorAll('.error-message');
                errors.forEach(e => e.style.display = 'none');
            }
        }
    </script>

    <!-- Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
      const ventesLabels = <?= json_encode(array_keys($ventesByCat), JSON_UNESCAPED_UNICODE) ?>;
      const ventesData = <?= json_encode(array_values($ventesByCat), JSON_UNESCAPED_UNICODE) ?>;
      const stockLabels = <?= json_encode(array_keys($stockStats), JSON_UNESCAPED_UNICODE) ?>;
      const stockData = <?= json_encode(array_values($stockStats), JSON_UNESCAPED_UNICODE) ?>;
      const promoLabels = <?= json_encode(array_keys($promoStats), JSON_UNESCAPED_UNICODE) ?>;
      const promoData = <?= json_encode(array_values($promoStats), JSON_UNESCAPED_UNICODE) ?>;
      const caMoisLabels = <?= json_encode(array_map(fn($r) => $r['ym'], $caParMois), JSON_UNESCAPED_UNICODE) ?>;
      const caMoisData = <?= json_encode(array_map(fn($r) => (float)$r['ca'], $caParMois), JSON_UNESCAPED_UNICODE) ?>;
      const topProdLabels = <?= json_encode(array_map(fn($r) => $r['nom'], $topProduits), JSON_UNESCAPED_UNICODE) ?>;
      const topProdData = <?= json_encode(array_map(fn($r) => (int)$r['qte'], $topProduits), JSON_UNESCAPED_UNICODE) ?>;

      function makeBar(ctx, labels, data, title) {
        return new Chart(ctx, {
          type: 'bar',
          data: {
            labels,
            datasets: [{
              label: title,
              data,
              backgroundColor: 'rgba(46,125,50,0.25)',
              borderColor: 'rgba(46,125,50,1)',
              borderWidth: 1.5,
              borderRadius: 10,
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
              x: { grid: { display: false } },
              y: { beginAtZero: true }
            }
          }
        });
      }

      function makeDoughnut(ctx, labels, data, colors) {
        return new Chart(ctx, {
          type: 'doughnut',
          data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0 }] },
          options: { responsive: true, plugins: { legend: { position: 'bottom' } }, cutout: '62%' }
        });
      }

      function makeLine(ctx, labels, data, title) {
        return new Chart(ctx, {
          type: 'line',
          data: {
            labels,
            datasets: [{
              label: title,
              data,
              borderColor: 'rgba(33,150,243,1)',
              backgroundColor: 'rgba(33,150,243,0.15)',
              fill: true,
              tension: 0.35
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
          }
        });
      }

      const el1 = document.getElementById('chartVentesByCat');
      if (el1) makeBar(el1, ventesLabels, ventesData, 'Ventes');
      const el2 = document.getElementById('chartStock');
      if (el2) makeDoughnut(el2, stockLabels, stockData, ['#ef5350', '#ffb300', '#66bb6a']);
      const el3 = document.getElementById('chartPromo');
      if (el3) makeDoughnut(el3, promoLabels, promoData, ['#fb8c00', '#90a4ae']);

      // Charts supplémentaires (innovants)
      const el4 = document.getElementById('chartCaMois');
      if (el4) makeLine(el4, caMoisLabels, caMoisData, 'Chiffre d\'affaires');
      const el5 = document.getElementById('chartTopProduits');
      if (el5) makeBar(el5, topProdLabels, topProdData, 'Quantités vendues');
    </script>
</body>
</html>