<!--
=========================================================
* Argon Dashboard 2 Tailwind - v1.0.1
=========================================================
* Adapted for EcoBite Marketplace - Version tout-en-un avec formulaires
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
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link href="../assets/css/argon-dashboard-tailwind.css?v=1.0.1" rel="stylesheet" />
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
    </style>
</head>
<body class="m-0 font-sans text-base antialiased font-normal dark:bg-slate-900 leading-default bg-gray-50 text-slate-500">
    <div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>

    <!-- SIDEBAR -->
    <aside class="fixed inset-y-0 flex-wrap items-center justify-between block w-full p-0 my-4 overflow-y-auto antialiased transition-transform duration-200 -translate-x-full bg-white border-0 shadow-xl dark:shadow-none dark:bg-slate-850 xl:ml-6 max-w-64 ease-nav-brand rounded-2xl xl:left-0 xl:translate-x-0">
        <div class="h-19">
            <a class="block px-8 py-6 m-0 text-sm whitespace-nowrap dark:text-white text-slate-700" href="#">
                <span class="ml-1 font-semibold">EcoBite Admin</span>
            </a>
        </div>
        <hr class="h-px mt-0 bg-transparent bg-gradient-to-r from-transparent via-black/40 to-transparent" />
        <div class="items-center block w-auto max-h-screen overflow-auto h-sidenav grow basis-full">
            <ul class="flex flex-col pl-0 mb-0">
                <li class="mt-0.5 w-full"><a class="py-2.7 bg-blue-500/13 text-sm my-0 mx-2 flex items-center rounded-lg px-4 font-semibold text-slate-700" href="/marketplace/view/back/pages/marketplace.php"><div class="mr-2 flex h-8 w-8 items-center justify-center"><i class="text-sm leading-normal text-blue-500 ni ni-shop"></i></div><span>Marketplace</span></a></li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm my-0 mx-2 flex items-center px-4 text-slate-500" href="#"><div class="mr-2 flex h-8 w-8 items-center justify-center"><i class="text-sm leading-normal text-orange-500 ni ni-hat-3"></i></div><span>Coaching</span></a></li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm my-0 mx-2 flex items-center px-4 text-slate-500" href="#"><div class="mr-2 flex h-8 w-8 items-center justify-center"><i class="text-sm leading-normal text-red-500 ni ni-alert"></i></div><span>Allergie</span></a></li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm my-0 mx-2 flex items-center px-4 text-slate-500" href="#"><div class="mr-2 flex h-8 w-8 items-center justify-center"><i class="text-sm leading-normal text-cyan-500 ni ni-blog"></i></div><span>Blog</span></a></li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm my-0 mx-2 flex items-center px-4 text-slate-500" href="#"><div class="mr-2 flex h-8 w-8 items-center justify-center"><i class="text-sm leading-normal text-emerald-500 ni ni-book-bookmark"></i></div><span>Recette</span></a></li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm my-0 mx-2 flex items-center px-4 text-slate-500" href="#"><div class="mr-2 flex h-8 w-8 items-center justify-center"><i class="text-sm leading-normal text-purple-500 ni ni-single-02"></i></div><span>User</span></a></li>
                <li class="mt-0.5 w-full"><a class="py-2.7 text-sm my-0 mx-2 flex items-center px-4 text-slate-500" href="/marketplace/index.php?controller=auth&action=logout"><div class="mr-2 flex h-8 w-8 items-center justify-center"><i class="text-sm leading-normal text-slate-400 ni ni-button-power"></i></div><span>Déconnexion</span></a></li>
            </ul>
        </div>
    </aside>

    <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
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

        <div class="w-full px-6 py-6 mx-auto">

            <!-- SECTION PRODUITS -->
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex justify-between items-center">
                            <h6 class="dark:text-white">📦 Produits</h6>
                            <button class="btn-action" style="background:#28a745;" onclick="openModal('addProductModal')">+ Ajouter un produit</button>
                        </div>
                        <div class="flex-auto px-0 pt-0 pb-2">
                            <div class="p-0 overflow-x-auto">
                                <?php
                                require_once '../../../controller/ProduitController.php';
                                $controller = new ProduitController();
                                $produits = $controller->getAllProduits();
                                ?>
                                <table class="table-crud">
                                    <thead><tr><th>ID</th><th>Nom</th><th>Prix (€)</th><th>Stock</th><th>Catégorie</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <?php foreach($produits as $p): ?>
                                        <tr>
                                            <td><?= $p['id'] ?></td>
                                            <td><?= htmlspecialchars($p['nom']) ?></td>
                                            <td><?= number_format($p['prix'], 2) ?> €</td>
                                            <td><?= $p['stock'] ?></td>
                                            <td><?= htmlspecialchars($p['categorie_nom'] ?? '—') ?></td>
                                            <td>
                                                <button class="btn-action warning" onclick="openEditProductModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nom']) ?>', <?= $p['prix'] ?>, <?= $p['stock'] ?>, '<?= htmlspecialchars($p['description']) ?>', <?= $p['categorie_id'] ?? 'null' ?>)">Modifier</button>
                                                <a href="/marketplace/index.php?controller=produit&action=delete&id=<?= $p['id'] ?>" class="btn-action danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
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

            <!-- SECTION CATÉGORIES -->
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex justify-between items-center">
                            <h6 class="dark:text-white">📁 Catégories</h6>
                            <button class="btn-action" style="background:#28a745;" onclick="openModal('addCategoryModal')">+ Ajouter une catégorie</button>
                        </div>
                        <div class="flex-auto px-0 pt-0 pb-2">
                            <div class="p-0 overflow-x-auto">
                                <?php
                                require_once '../../../controller/CategorieController.php';
                                $catController = new CategorieController();
                                $categories = $catController->getAllCategories();
                                ?>
                                <table class="table-crud">
                                    <thead><tr><th>ID</th><th>Nom</th><th>Description</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <?php foreach($categories as $c): ?>
                                        <tr>
                                            <td><?= $c['id'] ?></td>
                                            <td><?= htmlspecialchars($c['nom']) ?></td>
                                            <td><?= htmlspecialchars($c['description']) ?></td>
                                            <td>
                                                <button class="btn-action warning" onclick="openEditCategoryModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['nom']) ?>', '<?= htmlspecialchars($c['description']) ?>')">Modifier</button>
                                                <a href="/marketplace/index.php?controller=categorie&action=delete&id=<?= $c['id'] ?>" class="btn-action danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
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

            <!-- SECTION COMMANDES -->
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent">
                            <h6 class="dark:text-white">🛒 Commandes</h6>
                        </div>
                        <div class="flex-auto px-0 pt-0 pb-2">
                            <div class="p-0 overflow-x-auto">
                                <?php
                                require_once '../../../controller/CommandeController.php';
                                $cmdController = new CommandeController();
                                $commandes = $cmdController->getAllCommandes();
                                ?>
                                <table class="table-crud">
                                    <thead><tr><th>ID</th><th>Client</th><th>Email</th><th>Total (€)</th><th>Date</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <?php foreach($commandes as $cmd): ?>
                                        <tr>
                                            <td><?= $cmd['id'] ?></td>
                                            <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
                                            <td><?= htmlspecialchars($cmd['client_email']) ?></td>
                                            <td><?= number_format($cmd['total'], 2) ?> €</td>
                                            <td><?= $cmd['date_commande'] ?></td>
                                            <td>
                                                <a href="/marketplace/index.php?controller=commande&action=delete&id=<?= $cmd['id'] ?>" class="btn-action danger" onclick="return confirm('Supprimer cette commande ?')">Supprimer</a>
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
            <form action="/marketplace/index.php?controller=produit&action=store" method="POST">
                <input type="text" name="nom" placeholder="Nom du produit" required>
                <input type="number" name="prix" step="0.01" placeholder="Prix" required>
                <input type="number" name="stock" placeholder="Stock" value="0">
                <textarea name="description" placeholder="Description"></textarea>
                <select name="categorie_id">
                    <option value="">-- Aucune catégorie --</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-action">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- MODAL MODIFICATION PRODUIT -->
    <div id="editProductModal" class="form-modal">
        <div class="form-modal-content">
            <span class="close-modal" onclick="closeModal('editProductModal')">&times;</span>
            <h3>Modifier le produit</h3>
            <form id="editProductForm" action="/marketplace/index.php?controller=produit&action=update" method="POST">
                <input type="hidden" name="id" id="edit_product_id">
                <input type="text" name="nom" id="edit_product_nom" placeholder="Nom du produit" required>
                <input type="number" name="prix" step="0.01" id="edit_product_prix" placeholder="Prix" required>
                <input type="number" name="stock" id="edit_product_stock" placeholder="Stock">
                <textarea name="description" id="edit_product_description" placeholder="Description"></textarea>
                <select name="categorie_id" id="edit_product_categorie_id">
                    <option value="">-- Aucune catégorie --</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-action">Mettre à jour</button>
            </form>
        </div>
    </div>

    <!-- MODAL AJOUT CATÉGORIE -->
    <div id="addCategoryModal" class="form-modal">
        <div class="form-modal-content">
            <span class="close-modal" onclick="closeModal('addCategoryModal')">&times;</span>
            <h3>Ajouter une catégorie</h3>
            <form action="/marketplace/index.php?controller=categorie&action=store" method="POST">
                <input type="text" name="nom" placeholder="Nom de la catégorie" required>
                <textarea name="description" placeholder="Description"></textarea>
                <button type="submit" class="btn-action">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- MODAL MODIFICATION CATÉGORIE -->
    <div id="editCategoryModal" class="form-modal">
        <div class="form-modal-content">
            <span class="close-modal" onclick="closeModal('editCategoryModal')">&times;</span>
            <h3>Modifier la catégorie</h3>
            <form id="editCategoryForm" action="/marketplace/index.php?controller=categorie&action=update" method="POST">
                <input type="hidden" name="id" id="edit_category_id">
                <input type="text" name="nom" id="edit_category_nom" placeholder="Nom" required>
                <textarea name="description" id="edit_category_description" placeholder="Description"></textarea>
                <button type="submit" class="btn-action">Mettre à jour</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }
        
        function openEditProductModal(id, nom, prix, stock, description, categorie_id) {
            document.getElementById('edit_product_id').value = id;
            document.getElementById('edit_product_nom').value = nom;
            document.getElementById('edit_product_prix').value = prix;
            document.getElementById('edit_product_stock').value = stock;
            document.getElementById('edit_product_description').value = description;
            if(categorie_id) document.getElementById('edit_product_categorie_id').value = categorie_id;
            openModal('editProductModal');
        }
        
        function openEditCategoryModal(id, nom, description) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_nom').value = nom;
            document.getElementById('edit_category_description').value = description;
            openModal('editCategoryModal');
        }
        
        window.onclick = function(event) {
            if (event.target.classList.contains('form-modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>