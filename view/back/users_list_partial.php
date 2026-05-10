<?php if (empty($users)): ?>
    <tr>
        <td colspan="6" class="p-4 text-center">Aucun utilisateur trouvé.</td>
    </tr>
<?php else: ?>
    <?php foreach ($users as $u): ?>
    <?php
    $isOnline = false;
    if (!empty($u['last_activity'])) {
        $lastActivityTime = strtotime($u['last_activity']);
        if ($lastActivityTime && (time() - $lastActivityTime <= 120)) { // 2 minutes de marge
            $isOnline = true;
        }
    }
    ?>
    <tr>
        <td class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent">
            <div class="flex px-2 py-1">
                <div class="relative inline-block mr-4">
                    <img src="<?php echo !empty($u['photo']) ? $u['photo'] : 'view/front/images/user-icon.png'; ?>" class="inline-flex items-center justify-center text-sm text-white transition-all duration-200 ease-in-out h-9 w-9 rounded-xl" alt="user" />
                    <?php if ($isOnline): ?>
                        <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full" title="En ligne" style="z-index: 10;"></span>
                    <?php else: ?>
                        <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-gray-400 border-2 border-white rounded-full" title="Hors ligne" style="z-index: 10;"></span>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col justify-center">
                    <h6 class="mb-0 text-sm leading-normal dark:text-white"><?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?></h6>
                    <p class="mb-0 text-xs leading-tight dark:text-white dark:opacity-80 text-slate-400"><?php echo htmlspecialchars($u['email']); ?></p>
                </div>
            </div>
        </td>
        <td class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent">
            <?php if ($isOnline): ?>
                <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">
                    <i class="fas fa-circle text-xxs mr-1" style="font-size: 8px;"></i> En ligne
                </span>
            <?php else: ?>
                <span class="bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full dark:bg-gray-700 dark:text-gray-300">
                    <i class="fas fa-circle text-xxs mr-1" style="font-size: 8px;"></i> Hors ligne
                </span>
            <?php endif; ?>
        </td>
        <td class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent">
            <p class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-80"><?php echo htmlspecialchars($u['telephone'] ?? '-'); ?></p>
        </td>
        <td class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent">
            <span class="text-xs font-semibold dark:text-white"><?php echo $u['poids'] ?? '-'; ?> kg / <?php echo $u['taille'] ?? '-'; ?> cm</span>
        </td>
        <td class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent">
            <p class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-80"><?php echo htmlspecialchars($u['adresse'] ?? '-'); ?></p>
        </td>
        <td class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent">
            <span class="text-xs font-semibold leading-tight dark:text-white dark:opacity-80 text-slate-400"><?php echo $u['date_creation'] ?? '-'; ?></span>
        </td>
        <td class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent text-center">
            <a href="?section=back&action=editUser&id=<?php echo $u['id']; ?>" class="text-xs font-bold text-blue-500 mr-2">Modifier</a>
            <a href="?section=back&action=deleteUser&id=<?php echo $u['id']; ?>" class="text-xs font-bold text-red-500 mr-2" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
            
            <div class="relative inline-block text-left ban-dropdown">
                <button class="text-xs font-bold text-orange-500 focus:outline-none">Ban</button>
                <div class="ban-menu absolute right-0 w-32 mt-0 origin-top-right bg-white border border-gray-100 divide-y divide-gray-100 rounded-md shadow-lg opacity-0 pointer-events-none z-50 transition-opacity duration-300">
                    <div class="py-1">
                        <a href="?section=back&action=banUser&id=<?php echo $u['id']; ?>&duration=1d" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">1 Jour</a>
                        <a href="?section=back&action=banUser&id=<?php echo $u['id']; ?>&duration=2d" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">2 Jours</a>
                        <a href="?section=back&action=banUser&id=<?php echo $u['id']; ?>&duration=3d" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">3 Jours</a>
                        <a href="?section=back&action=banUser&id=<?php echo $u['id']; ?>&duration=5d" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">5 Jours</a>
                        <a href="?section=back&action=banUser&id=<?php echo $u['id']; ?>&duration=perm" class="block px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-bold">Définitif</a>
                        <a href="?section=back&action=banUser&id=<?php echo $u['id']; ?>&duration=unban" class="block px-4 py-2 text-xs text-green-600 hover:bg-green-50">Débannir</a>
                    </div>
                </div>
            </div>
            
            <?php if ($u['ban_until'] && strtotime($u['ban_until']) > time()): ?>
                <div class="mt-1">
                    <span class="text-xxs font-bold text-red-500 uppercase">Banni jusqu'au <?php echo date('d/m/y', strtotime($u['ban_until'])); ?></span>
                </div>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
