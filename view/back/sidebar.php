<<<<<<< HEAD
<aside class="fixed inset-y-0 flex-wrap items-center justify-between block w-full p-0 my-4 overflow-y-auto antialiased transition-transform duration-200 -translate-x-full bg-white border-0 shadow-xl dark:shadow-none dark:bg-slate-850 xl:ml-6 max-w-64 ease-nav-brand z-990 rounded-2xl xl:left-0 xl:translate-x-0" aria-expanded="false">
  <div class="h-19 text-center py-6">
    <i class="absolute top-0 right-0 p-4 opacity-50 cursor-pointer fas fa-times dark:text-white text-slate-400 xl:hidden" sidenav-close></i>
    <a class="m-0 text-sm whitespace-nowrap dark:text-white text-slate-700" href="/2int/index.php">
      <span class="font-bold text-2xl tracking-tight">
        <span class="text-green-500 uppercase">Eco</span><span class="text-orange-500 uppercase">byte</span>
      </span>
    </a>
  </div>

  <hr class="h-px mt-0 bg-transparent bg-gradient-to-r from-transparent via-black/40 to-transparent dark:bg-gradient-to-r dark:from-transparent dark:via-white dark:to-transparent" />

  <div class="items-center block w-auto max-h-screen overflow-auto h-sidenav grow basis-full">
    <ul class="flex flex-col pl-0 mb-0">
      
      <li class="w-full mt-4">
        <h6 class="pl-6 ml-2 text-xs font-bold leading-tight uppercase dark:text-white opacity-60">ADMINISTRATION</h6>
      </li>

      <!-- UTILISATEURS -->
      <li class="mt-0.5 w-full">
        <a class="py-2.7 <?php echo ($action === 'users' ? 'bg-blue-500/13 font-semibold text-blue-600' : 'text-slate-700 dark:text-white dark:opacity-80'); ?> text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors rounded-lg" href="/2int/index.php?section=back&action=users">
          <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">
            <i class="relative top-0 text-sm leading-normal text-blue-500 ni ni-single-02"></i>
          </div>
          <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Gestion Utilisateurs</span>
        </a>
      </li>

      <!-- SANTÉ -->
      <li class="mt-0.5 w-full">
        <a class="py-2.7 text-slate-700 dark:text-white dark:opacity-80 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors rounded-lg hover:bg-gray-50" href="/2int/view/Back/build/allergies_list.php">
          <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">
            <i class="relative top-0 text-sm leading-normal text-red-500 ni ni-notification-70"></i>
          </div>
          <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Gestion Santé</span>
        </a>
      </li>

      <!-- CUISINE -->
      <li class="mt-0.5 w-full">
        <a class="py-2.7 text-slate-700 dark:text-white dark:opacity-80 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors rounded-lg hover:bg-gray-50" href="/2int/view/back/back.php">
          <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">
            <i class="relative top-0 text-sm leading-normal text-orange-500 ni ni-shop"></i>
          </div>
          <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Gestion Cuisine</span>
        </a>
      </li>

      <!-- FITNESS -->
      <li class="mt-0.5 w-full">
        <a class="py-2.7 text-slate-700 dark:text-white dark:opacity-80 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors rounded-lg hover:bg-gray-50" href="/2int/public/admin/index.php">
          <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">
            <i class="relative top-0 text-sm leading-normal text-indigo-500 ni ni-user-run"></i>
          </div>
          <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Gestion Fitness</span>
        </a>
      </li>

      <!-- BOUTIQUE -->
      <li class="mt-0.5 w-full">
        <a class="py-2.7 text-slate-700 dark:text-white dark:opacity-80 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors rounded-lg hover:bg-gray-50" href="/2int/view/back_boutique/index.php">
          <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">
            <i class="relative top-0 text-sm leading-normal text-pink-500 ni ni-cart"></i>
          </div>
          <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Gestion Boutique</span>
        </a>
      </li>

      <!-- BLOG -->
      <li class="mt-0.5 w-full">
        <a class="py-2.7 text-slate-700 dark:text-white dark:opacity-80 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors rounded-lg hover:bg-gray-50" href="/2int/view/back/blog_list.php">
          <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">
            <i class="relative top-0 text-sm leading-normal text-emerald-500 ni ni-paper-diploma"></i>
          </div>
          <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Gestion Blog</span>
        </a>
      </li>

      <li class="w-full mt-4">
        <h6 class="pl-6 ml-2 text-xs font-bold leading-tight uppercase dark:text-white opacity-60">COMPTE</h6>
      </li>

      <?php if (isset($_SESSION['admin_logged_in'])): ?>
      <li class="mt-0.5 w-full">
        <form action="/2int/index.php?section=back&action=logout" method="POST" class="m-0 p-0">
          <button type="submit" class="w-full text-left py-2.7 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors text-red-600 bg-transparent border-0 cursor-pointer rounded-lg hover:bg-red-50">
            <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">
              <i class="relative top-0 text-sm leading-normal ni ni-user-run"></i>
            </div>
            <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Déconnexion</span>
          </button>
        </form>
      </li>
      <?php else: ?>
      <li class="mt-0.5 w-full">
        <a class="py-2.7 <?php echo ($action === 'sign-in' ? 'bg-blue-500/13 font-semibold' : ''); ?> text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-colors dark:text-white dark:opacity-80 rounded-lg" href="/2int/index.php?section=back&action=sign-in">
          <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">
            <i class="relative top-0 text-sm leading-normal text-orange-500 ni ni-single-copy-04"></i>
          </div>
          <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Connexion Admin</span>
        </a>
      </li>
      <?php endif; ?>

    </ul>
  </div>
=======
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
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="/2int/view/back/back.php">
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
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 rounded-xl bg-blue-50 text-slate-800 transition-all shadow-sm" href="/2int/view/back/posts.php">
                    <div class="mr-3 flex items-center justify-center text-lg w-8">📝</div>
                    <span class="font-bold text-blue-700">Communauté & Blog</span>
                </a>
            </li>

            <!-- IA Assistant -->
            <li class="mt-0.5 w-full">
                <a class="py-3 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 transition-all hover:bg-gray-50 rounded-xl text-slate-600" href="/2int/view/back/summaries.php">
                    <div class="mr-3 flex items-center justify-center text-lg w-8">🤖</div>
                    <span class="font-medium">IA Assistant</span>
                </a>
            </li>

            <!-- SECTION ACTIONS BLOG -->
            <li class="w-full mt-8">
                <h6 class="pl-4 text-xs font-bold leading-tight uppercase opacity-40 text-slate-500 mb-3">GESTION BLOG</h6>
            </li>

            <li class="mt-0.5 w-full">
                <a class="py-2.5 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 text-slate-600 hover:text-blue-600 hover:bg-gray-50 rounded-xl transition-all" href="/2int/view/back/posts.php">
                    <div class="mr-4 flex items-center justify-center w-5"><i class="fas fa-file-alt"></i></div>
                    <span>Articles</span>
                </a>
            </li>

            <li class="mt-0.5 w-full">
                <a class="py-2.5 text-sm my-0 mx-2 flex items-center whitespace-nowrap px-4 text-slate-600 hover:text-blue-600 hover:bg-gray-50 rounded-xl transition-all" href="/2int/view/back/replies.php">
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
>>>>>>> blog
</aside>
