<!-- SIDEBAR MASTER -->
<aside class="fixed inset-y-0 left-0 bg-white w-64 shadow-lg border-r border-gray-100 z-50 h-screen overflow-y-auto">
    <!-- Header with Leaf Logo -->
    <div class="px-8 py-6 flex items-center">
        <i class="fas fa-leaf text-green-500 mr-2 text-xl"></i>
        <span class="font-bold text-xl tracking-tight">
            <span class="text-green-500 uppercase">Eco</span><span class="text-orange-500 uppercase">byte</span>
        </span>
    </div>

    <div class="px-4">
        <ul class="flex flex-col pl-0 mb-0 list-none">
            
            <li class="w-full mt-4">
                <h6 class="pl-4 text-xs font-bold leading-tight uppercase opacity-40 text-slate-500 mb-4">MODULES</h6>
            </li>

            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="#">
                    <div class="mr-3 flex items-center justify-center text-lg">
                        🏠
                    </div>
                    <span class="font-medium">Boutique Bio</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="#">
                    <div class="mr-3 flex items-center justify-center text-lg">
                        🏋️
                    </div>
                    <span class="font-medium">Fitness & Sport</span>
                </a>
            </li>

            <!-- ITEM ACTIF (Santé & Allergies) -->
            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 rounded-xl bg-blue-50 text-slate-800 transition-all" href="allergies_list.php">
                    <div class="mr-3 flex items-center justify-center text-lg">
                        ⚠️
                    </div>
                    <span class="font-bold text-blue-700">Santé & Allergies</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="#">
                    <div class="mr-3 flex items-center justify-center text-lg">
                        📝
                    </div>
                    <span class="font-medium">Blog & Actu</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="#">
                    <div class="mr-3 flex items-center justify-center text-lg">
                        🍲
                    </div>
                    <span class="font-medium">Cuisine & Recettes</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="#">
                    <div class="mr-3 flex items-center justify-center text-lg">
                        👤
                    </div>
                    <span class="font-medium">Utilisateurs</span>
                </a>
            </li>

            <!-- SECTION GESTION SANTÉ -->
            <li class="w-full mt-8">
                <h6 class="pl-4 text-xs font-bold leading-tight uppercase opacity-40 text-slate-500 mb-4">GESTION SANTÉ</h6>
            </li>

            <?php $current_page = basename($_SERVER['PHP_SELF']); ?>

            <li class="mt-0.5 w-full">
                <a class="py-2.5 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 <?= $current_page == 'allergies_list.php' ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600 hover:bg-gray-50' ?> rounded-xl transition-all" href="allergies_list.php">
                    <div class="mr-4 flex items-center justify-center">
                        <i class="fas fa-list-ul <?= $current_page == 'allergies_list.php' ? 'text-blue-600' : 'text-slate-400' ?>"></i>
                    </div>
                    <span>Liste Allergies</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a class="py-2.5 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 <?= $current_page == 'traitement_list.php' ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600 hover:bg-gray-50' ?> rounded-xl transition-all" href="traitement_list.php">
                    <div class="mr-4 flex items-center justify-center">
                        <i class="fas fa-pills <?= $current_page == 'traitement_list.php' ? 'text-blue-600' : 'text-slate-400' ?>"></i>
                    </div>
                    <span>Liste Traitements</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a class="py-2.5 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 <?= $current_page == 'stat.php' ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600 hover:bg-gray-50' ?> rounded-xl transition-all" href="stat.php">
                    <div class="mr-4 flex items-center justify-center">
                        <i class="fas fa-chart-pie <?= $current_page == 'stat.php' ? 'text-blue-600' : 'text-slate-400' ?>"></i>
                    </div>
                    <span>Statistiques</span>
                </a>
            </li>

            <!-- LOGOUT -->
            <li class="mt-12 w-full mb-8">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-red-50 text-red-500 rounded-xl" href="#">
                    <div class="mr-4 flex items-center justify-center">
                        <i class="fas fa-power-off"></i>
                    </div>
                    <span class="font-bold uppercase tracking-tight">Déconnexion</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
