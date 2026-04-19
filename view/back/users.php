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
    <div class="absolute w-full dark:hidden min-h-75" style="background: linear-gradient(90deg, #059669, #10b981);"></div>

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
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent flex justify-between items-center">
                            <h6 class="dark:text-white">Liste des Utilisateurs</h6>
                            <a href="?section=back&action=addUser" class="inline-block px-4 py-2 text-xs font-bold text-center text-white uppercase align-middle transition-all rounded-lg shadow-md hover:-translate-y-px" style="background-color: #10b981;">
                                Ajouter un utilisateur
                            </a>
                        </div>
                        <div class="flex-auto px-0 pt-0 pb-2">
                            <div class="p-0 overflow-x-auto">
                                <table class="items-center w-full mb-0 align-top border-collapse dark:border-white/40 text-slate-500">
                                    <thead class="align-bottom">
                                        <tr>
                                            <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Utilisateur</th>
                                            <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Téléphone</th>
                                            <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Physique</th>
                                            <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-collapse shadow-none dark:border-white/40 dark:text-white text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Date</th>
                                            <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-collapse border-solid shadow-none dark:border-white/40 dark:text-white tracking-none whitespace-nowrap text-slate-400 opacity-70">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($users)): ?>
                                            <tr>
                                                <td colspan="5" class="p-4 text-center">Aucun utilisateur trouvé.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($users as $u): ?>
                                            <tr>
                                                <td class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent">
                                                    <div class="flex px-2 py-1">
                                                        <div>
                                                            <img src="<?php echo !empty($u['photo']) ? $u['photo'] : 'view/front/images/user-icon.png'; ?>" class="inline-flex items-center justify-center mr-4 text-sm text-white transition-all duration-200 ease-in-out h-9 w-9 rounded-xl" alt="user" />
                                                        </div>
                                                        <div class="flex flex-col justify-center">
                                                            <h6 class="mb-0 text-sm leading-normal dark:text-white"><?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?></h6>
                                                            <p class="mb-0 text-xs leading-tight dark:text-white dark:opacity-80 text-slate-400"><?php echo htmlspecialchars($u['email']); ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent">
                                                    <p class="mb-0 text-xs font-semibold leading-tight dark:text-white dark:opacity-80"><?php echo htmlspecialchars($u['telephone'] ?? '-'); ?></p>
                                                </td>
                                                <td class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent">
                                                    <span class="text-xs font-semibold dark:text-white"><?php echo $u['poids'] ?? '-'; ?> kg / <?php echo $u['taille'] ?? '-'; ?> cm</span>
                                                </td>
                                                <td class="p-2 text-center align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent">
                                                    <span class="text-xs font-semibold leading-tight dark:text-white dark:opacity-80 text-slate-400"><?php echo $u['date_creation'] ?? '-'; ?></span>
                                                </td>
                                                <td class="p-2 align-middle bg-transparent border-b dark:border-white/40 whitespace-nowrap shadow-transparent text-center">
                                                    <a href="?section=back&action=editUser&id=<?php echo $u['id']; ?>" class="text-xs font-bold text-emerald-500 mr-2">Modifier</a>
                                                    <a href="?section=back&action=deleteUser&id=<?php echo $u['id']; ?>" class="text-xs font-bold text-red-500" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
