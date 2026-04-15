<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($user['id']) ? 'Modifier' : 'Ajouter'; ?> Utilisateur - Admin</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="view/back/build/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="view/back/build/assets/css/nucleo-svg.css" rel="stylesheet" />
    <link href="view/back/build/assets/css/argon-dashboard-tailwind.css?v=1.0.1" rel="stylesheet" />
</head>
<body class="m-0 font-sans text-base antialiased font-normal dark:bg-slate-900 leading-default bg-gray-50 text-slate-500">
    <div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>

    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
        <div class="w-full px-6 py-6 mx-auto">
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
                        <div class="p-6 pb-0 mb-0 border-b-0 border-b-solid rounded-t-2xl border-b-transparent">
                            <h6 class="dark:text-white"><?php echo isset($user['id']) ? 'Modifier' : 'Ajouter'; ?> un Profil Utilisateur</h6>
                        </div>
                        <div class="flex-auto p-6">
                            <?php if (!empty($errors)): ?>
                                <div class="p-4 mb-4 text-white bg-red-500 rounded-lg">
                                    <?php foreach ($errors as $error) echo htmlspecialchars($error) . '<br>'; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="flex flex-wrap -mx-3">
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label class="inline-block mb-2 ml-1 font-bold text-xs text-slate-700 dark:text-white/80">Nom</label>
                                            <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-400 focus:border-blue-500 focus:outline-none" required />
                                        </div>
                                    </div>
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label class="inline-block mb-2 ml-1 font-bold text-xs text-slate-700 dark:text-white/80">Prénom</label>
                                            <input type="text" name="prenom" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-400 focus:border-blue-500 focus:outline-none" required />
                                        </div>
                                    </div>
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label class="inline-block mb-2 ml-1 font-bold text-xs text-slate-700 dark:text-white/80">Email</label>
                                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-400 focus:border-blue-500 focus:outline-none" required />
                                        </div>
                                    </div>
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label class="inline-block mb-2 ml-1 font-bold text-xs text-slate-700 dark:text-white/80">Téléphone</label>
                                            <input type="text" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>" class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-400 focus:border-blue-500 focus:outline-none" />
                                        </div>
                                    </div>
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label class="inline-block mb-2 ml-1 font-bold text-xs text-slate-700 dark:text-white/80">Poids (kg)</label>
                                            <input type="number" step="0.1" name="poids" value="<?php echo htmlspecialchars($user['poids'] ?? ''); ?>" class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-400 focus:border-blue-500 focus:outline-none" />
                                        </div>
                                    </div>
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-6/12 md:flex-0">
                                        <div class="mb-4">
                                            <label class="inline-block mb-2 ml-1 font-bold text-xs text-slate-700 dark:text-white/80">Taille (cm)</label>
                                            <input type="number" step="0.1" name="taille" value="<?php echo htmlspecialchars($user['taille'] ?? ''); ?>" class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-400 focus:border-blue-500 focus:outline-none" />
                                        </div>
                                    </div>
                                    
                                    <div class="w-full max-w-full px-3 shrink-0 md:w-12/12 md:flex-0">
                                        <div class="mb-4">
                                            <label class="inline-block mb-2 ml-1 font-bold text-xs text-slate-700 dark:text-white/80">Nouveau Mot de passe (Laisser vide pour ne pas changer)</label>
                                            <input type="password" name="password" class="focus:shadow-primary-outline dark:bg-slate-850 dark:text-white text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-400 focus:border-blue-500 focus:outline-none" />
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end mt-4">
                                    <a href="?section=back&action=users" class="inline-block px-6 py-3 mr-3 font-bold text-center text-white uppercase align-middle transition-all bg-slate-700 rounded-lg shadow-md hover:-translate-y-px">Annuler</a>
                                    <button type="submit" class="inline-block px-6 py-3 font-bold text-center text-white uppercase align-middle transition-all bg-blue-500 rounded-lg shadow-md hover:-translate-y-px">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
