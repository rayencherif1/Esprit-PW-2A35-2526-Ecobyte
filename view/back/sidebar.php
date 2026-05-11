<!-- SIDEBAR UNIFIÉE - ADMINISTRATION ECOBYTE -->
<aside class="fixed inset-y-0 left-0 bg-white w-64 shadow-lg border-r border-gray-100 z-50 h-screen overflow-y-auto transition-all duration-300">
    <div class="px-8 py-6 flex items-center border-b border-gray-50 mb-4">
        <i class="fas fa-leaf text-green-500 mr-3 text-2xl"></i>
        <span class="font-bold text-xl tracking-tight">
            <span class="text-slate-800 uppercase">Eco</span><span class="text-green-500 uppercase">byte</span>
        </span>
    </div>

    <div class="px-4">
        <ul class="flex flex-col pl-0 mb-0 list-none">
            
            <!-- SECTION MODULES -->
            <li class="w-full mt-2">
                <h6 class="pl-4 text-xs font-bold leading-tight uppercase opacity-40 text-slate-500 mb-3">MODULES D'ADMINISTRATION</h6>
            </li>

            <!-- Boutique -->
            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="/2int/boutique_admin.php">
                    <div class="mr-3 flex items-center justify-center text-lg w-8">🛒</div>
                    <span class="font-medium">Boutique Bio</span>
                </a>
            </li>

            <!-- Fitness -->
            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="/2int/public/index.php?action=admin">
                    <div class="mr-3 flex items-center justify-center text-lg w-8">🏋️</div>
                    <span class="font-medium">Fitness & Sport</span>
                </a>
            </li>

            <!-- Santé -->
            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="/2int/View/Back/back.php">
                    <div class="mr-3 flex items-center justify-center text-lg w-8">🏥</div>
                    <span class="font-medium">Santé & Allergies</span>
                </a>
            </li>

            <!-- Cuisine -->
            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="/2int/view/back/back.php">
                    <div class="mr-3 flex items-center justify-center text-lg w-8">🍲</div>
                    <span class="font-medium">Cuisine & Recettes</span>
                </a>
            </li>

            <!-- Blog (ACTIF ICI) -->
            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 rounded-xl bg-blue-50 text-slate-800 transition-all shadow-sm" href="posts.php">
                    <div class="mr-3 flex items-center justify-center text-lg w-8">📝</div>
                    <span class="font-bold text-blue-700">Communauté & Blog</span>
                </a>
            </li>

            <!-- IA Assistant -->
            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="summaries.php">
                    <div class="mr-3 flex items-center justify-center text-lg w-8">🤖</div>
                    <span class="font-medium">IA Assistant</span>
                </a>
            </li>

            <!-- SECTION ACTIONS BLOG -->
            <li class="w-full mt-8">
                <h6 class="pl-4 text-xs font-bold leading-tight uppercase opacity-40 text-slate-500 mb-3">GESTION BLOG</h6>
            </li>

            <li class="mt-0.5 w-full">
                <a class="py-2.5 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 text-slate-600 hover:text-blue-600 hover:bg-gray-50 rounded-xl transition-all" href="posts.php">
                    <div class="mr-4 flex items-center justify-center w-5"><i class="fas fa-file-alt"></i></div>
                    <span>Articles</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a class="py-2.5 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 text-slate-600 hover:text-blue-600 hover:bg-gray-50 rounded-xl transition-all" href="replies.php">
                    <div class="mr-4 flex items-center justify-center w-5"><i class="fas fa-comments"></i></div>
                    <span>Commentaires</span>
                </a>
            </li>

            <!-- DÉCONNEXION (Placeholder) -->
            <li class="mt-12 w-full mb-8">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-red-50 text-red-500 rounded-xl" href="#">
                    <div class="mr-4 flex items-center justify-center w-5"><i class="fas fa-power-off"></i></div>
                    <span class="font-bold uppercase tracking-tight">Déconnexion</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
