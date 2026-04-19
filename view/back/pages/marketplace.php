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
    </style>
</head>
<body class="m-0 font-sans text-base antialiased font-normal dark:bg-slate-900 leading-default bg-gray-50 text-slate-500">
    <div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>

    <!-- CHARGEMENT DES DONNÉES -->
    <?php
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

        <!-- SECTION PRODUITS (sans colonne ID) -->
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
                                            <th>Nom</th><th>Prix (€)</th><th>Stock</th><th>Catégorie</th><th>Actions</th>
                                            <th>Commandes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($produits as $p): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($p['nom']) ?></td>
                                                <td><?= number_format($p['prix'], 2) ?> €</td>
                                                <td><?= $p['stock'] ?></td>
                                                <td><?= htmlspecialchars($p['categorie_nom'] ?? '—') ?></td>
                                                <td>
                                                    <button class="btn-action warning" onclick="openEditProductModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nom']) ?>', <?= $p['prix'] ?>, <?= $p['stock'] ?>, '<?= htmlspecialchars($p['description']) ?>', <?= $p['categorie_id'] ?? 'null' ?>)">Modifier</button>
                                                    <a href="/marketplace/index.php?controller=produit&action=delete&id=<?= $p['id'] ?>" class="btn-action danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                                                </td>
                                                <td>
                                                    <button class="btn-action info" onclick="toggleOrders(<?= $p['id'] ?>)">📋 Voir commandes</button>
                                                </td>
                                            </tr>
                                            <tr id="orders-<?= $p['id'] ?>" style="display: none;" class="order-row">
                                                <td colspan="6">
                                                    <?php if (!empty($commandesParProduit[$p['id']])): ?>
                                                        <table class="order-table">
                                                            <thead>
                                                                <tr><th>Commande ID</th><th>Client</th><th>Email</th><th>Date</th><th>Total (€)</th><th>Quantité</th></tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($commandesParProduit[$p['id']] as $ord): ?>
                                                                    <tr>
                                                                        <td><?= $ord['id'] ?></td>
                                                                        <td><?= htmlspecialchars($ord['client']) ?></td>
                                                                        <td><?= htmlspecialchars($ord['email']) ?></td>
                                                                        <td><?= $ord['date'] ?></td>
                                                                        <td><?= number_format($ord['total'], 2) ?> €</td>
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

        <!-- SECTION CATÉGORIES (sans colonne ID) -->
        <div class="w-full px-6 py-6 mx-auto">
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
        </div>

        <footer class="pt-4">
            <div class="w-full px-6 mx-auto">
                <div class="text-sm leading-normal text-center text-slate-500">
                    © 2026 - EcoBite Marketplace - Projet étudiant
                </div>
            </div>
        </footer>
    </main>

    <!-- MODAL AJOUT PRODUIT (sans required HTML) -->
    <div id="addProductModal" class="form-modal">
        <div class="form-modal-content">
            <span class="close-modal" onclick="closeModal('addProductModal')">&times;</span>
            <h3>Ajouter un produit</h3>
            <div id="addProductError" class="error-message"></div>
            <form id="addProductForm" action="/marketplace/index.php?controller=produit&action=store" method="POST" onsubmit="return validateAddProduct()">
                <input type="text" name="nom" id="add_nom" placeholder="Nom du produit">
                <input type="number" name="prix" id="add_prix" step="0.01" placeholder="Prix">
                <input type="number" name="stock" id="add_stock" placeholder="Stock" value="0">
                <textarea name="description" id="add_description" placeholder="Description"></textarea>
                <select name="categorie_id" id="add_categorie_id">
                    <option value="">-- Aucune catégorie --</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-action">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- MODAL MODIFICATION PRODUIT (sans required HTML) -->
    <div id="editProductModal" class="form-modal">
        <div class="form-modal-content">
            <span class="close-modal" onclick="closeModal('editProductModal')">&times;</span>
            <h3>Modifier le produit</h3>
            <div id="editProductError" class="error-message"></div>
            <form id="editProductForm" action="/marketplace/index.php?controller=produit&action=update" method="POST" onsubmit="return validateEditProduct()">
                <input type="hidden" name="id" id="edit_product_id">
                <input type="text" name="nom" id="edit_product_nom" placeholder="Nom du produit">
                <input type="number" name="prix" step="0.01" id="edit_product_prix" placeholder="Prix">
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

    <!-- MODAL AJOUT CATÉGORIE (sans required HTML) -->
    <div id="addCategoryModal" class="form-modal">
        <div class="form-modal-content">
            <span class="close-modal" onclick="closeModal('addCategoryModal')">&times;</span>
            <h3>Ajouter une catégorie</h3>
            <div id="addCategoryError" class="error-message"></div>
            <form id="addCategoryForm" action="/marketplace/index.php?controller=categorie&action=store" method="POST" onsubmit="return validateAddCategory()">
                <input type="text" name="nom" id="add_cat_nom" placeholder="Nom de la catégorie">
                <textarea name="description" id="add_cat_description" placeholder="Description"></textarea>
                <button type="submit" class="btn-action">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- MODAL MODIFICATION CATÉGORIE (sans required HTML) -->
    <div id="editCategoryModal" class="form-modal">
        <div class="form-modal-content">
            <span class="close-modal" onclick="closeModal('editCategoryModal')">&times;</span>
            <h3>Modifier la catégorie</h3>
            <div id="editCategoryError" class="error-message"></div>
            <form id="editCategoryForm" action="/marketplace/index.php?controller=categorie&action=update" method="POST" onsubmit="return validateEditCategory()">
                <input type="hidden" name="id" id="edit_category_id">
                <input type="text" name="nom" id="edit_category_nom" placeholder="Nom">
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
            if(categorie_id && categorie_id != 'null') document.getElementById('edit_product_categorie_id').value = categorie_id;
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
        
        // Validation JavaScript (sans HTML5)
        function validateAddProduct() {
            let nom = document.getElementById('add_nom').value.trim();
            let prix = document.getElementById('add_prix').value;
            let stock = document.getElementById('add_stock').value;
            let errorDiv = document.getElementById('addProductError');
            
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
            errorDiv.style.display = 'none';
            return true;
        }
        
        function validateEditProduct() {
            let nom = document.getElementById('edit_product_nom').value.trim();
            let prix = document.getElementById('edit_product_prix').value;
            let stock = document.getElementById('edit_product_stock').value;
            let errorDiv = document.getElementById('editProductError');
            
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
                // Réinitialiser les erreurs
                let errors = document.querySelectorAll('.error-message');
                errors.forEach(e => e.style.display = 'none');
            }
        }
    </script>
</body>
</html>