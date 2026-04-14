<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Connexion Admin - Rayench</title>
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
    <main class="mt-0 transition-all duration-200 ease-in-out">
        <section>
            <div class="relative flex items-center min-h-screen p-0 overflow-hidden bg-center bg-cover">
                <div class="container z-1">
                    <div class="flex flex-wrap -mx-3">
                        <div class="flex flex-col w-full max-w-full px-3 mx-auto lg:mx-0 shrink-0 md:flex-0 md:w-7/12 lg:w-5/12 xl:w-4/12">
                            <div class="relative flex flex-col min-w-0 break-words bg-transparent border-0 shadow-none lg:py4 dark:bg-gray-950 rounded-2xl bg-clip-border">
                                <div class="p-6 pb-0 mb-0">
                                    <h4 class="font-bold">Administration</h4>
                                    <p class="mb-0">Entrez vos identifiants admin</p>
                                </div>
                                <div class="flex-auto p-6">
                                    <?php if (!empty($errors)): ?>
                                        <div class="p-4 mb-4 text-white bg-red-500 rounded-lg">
                                            <?php foreach ($errors as $error) echo htmlspecialchars($error) . '<br>'; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form role="form" method="POST">
                                        <div class="mb-4">
                                            <input type="text" name="email" placeholder="Email / Login" class="focus:shadow-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding p-3 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-400 focus:border-fuchsia-300 focus:outline-none" required />
                                        </div>
                                        <div class="mb-4">
                                            <input type="password" name="password" placeholder="Mot de passe" class="focus:shadow-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding p-3 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-400 focus:border-fuchsia-300 focus:outline-none" required />
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="inline-block w-full px-16 py-3.5 mt-6 mb-0 font-bold leading-normal text-center text-white align-middle transition-all bg-blue-500 border-0 rounded-lg shadow-md cursor-pointer hover:-translate-y-px active:opacity-85">Connexion</button>
                                        </div>
                                    </form>
                                    <div class="text-center mt-4">
                                        <a href="?section=front" class="text-xs text-blue-500">Retour au site front</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="absolute top-0 right-0 flex-col justify-center hidden w-6/12 h-full max-w-full px-3 pr-0 my-auto text-center flex-0 lg:flex">
                            <div class="relative flex flex-col justify-center h-full bg-cover px-24 m-4 overflow-hidden bg-[url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/signin-ill.jpg')] rounded-xl">
                                <span class="absolute top-0 left-0 w-full h-full bg-center bg-cover bg-gradient-to-tl from-blue-500 to-violet-500 opacity-60"></span>
                                <h4 class="z-20 mt-12 font-bold text-white">"Attention builds excellence"</h4>
                                <p class="z-20 text-white opacity-80">Gérez vos utilisateurs en toute simplicité.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
