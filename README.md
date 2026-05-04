# Site d’entraînement (PHP + MySQL + XAMPP)

Application web simple : catalogue de **programmes** et **exercices**, espace **admin** pour les gérer, et quelques outils côté navigateur (IMC, conseil du jour, alternatives d’exercices via wger).

---

## Démarrage rapide (Windows / XAMPP)

1. Copiez le dossier du projet dans `C:\xampp\htdocs\` (ou ailleurs, en adaptant les URLs).
2. Ouvrez le **XAMPP Control Panel** et démarrez **Apache** + **MySQL**.
3. Créez la base **`nutrition_sante`** (utf8mb4) dans phpMyAdmin.
4. Importez **`database/schema.sql`** dans cette base.
5. Vérifiez **`config/config.php`** :
   - `DB_USER` / `DB_PASS` (souvent `root` + mot de passe vide).
   - `BASE_URL` et `ADMIN_URL` (doivent correspondre à votre URL locale).
   - `URL_FOODMART` et `URL_ARGON_ASSETS` (chemins vers les dossiers de thème).
6. Logo du bandeau : **`public/images/mylogo.png`** (c’est l’emplacement utilisé par le layout).
7. Ouvrez dans le navigateur :
   - **Site public :** `{BASE_URL}/index.php?action=home`  
     Exemple : `http://localhost/projetselem/public/index.php?action=home`
   - **Admin :** `{ADMIN_URL}/index.php?action=dashboard`  
     Exemple : `http://localhost/projetselem/public/admin/index.php?action=dashboard`

---

## Structure des dossiers (l’essentiel)

| Dossier / fichier | Rôle |
|-------------------|------|
| **`public/`** | Point d’entrée web : `index.php` (site) et `admin/index.php` (back-office). Les URLs pointent ici. |
| **`public/js/training-api.js`** | JavaScript : IMC, conseil du jour, alternatives wger (muscle + vidéos / YouTube). |
| **`public/images/mylogo.png`** | Logo affiché en haut du site (à fournir). |
| **`app/`** | Code PHP : contrôleurs, modèles, vues. |
| **`app/Controllers/`** | Logique des pages (`FrontController`, contrôleurs admin). |
| **`app/Models/`** | Accès base de données (PDO). |
| **`app/Views/`** | Gabarits HTML PHP (front + admin). |
| **`config/config.php`** | Réglages globaux (BDD, URLs, constantes métier). |
| **`database/schema.sql`** | Structure MySQL à importer. |
| **`FoodMart-1.0.0/`** | Thème **front** (CSS/JS/images) — requis par `URL_FOODMART`. |
| **`argon-dashboard-tailwind-1.0.1/`** | Assets **admin** — requis par `URL_ARGON_ASSETS`. |

Les gros dossiers de thème ne sont pas du “code métier”, mais le site les charge pour le style.

---

## Fonctionnalités principales

- **Accueil** : liste des programmes, filtres, widgets IMC + conseil.
- **Séance** : détail d’un programme avec exercices ; boutons YouTube et alternatives (wger) si un muscle wger est renseigné sur l’exercice.
- **Admin** : CRUD exercices et programmes, liaison programme ↔ exercices.

---

## Dépendances externes (navigateur)

- API IMC, API conseils, API **wger** (exercices / traductions / vidéos) : appelées depuis le navigateur ; connexion Internet nécessaire pour ces blocs.

---

## Fichiers supprimés comme inutiles dans ce dépôt

- Dossier **`projetselem/projetselem/`** : copie dupliquée du projet.
- **`index.html`** et **`serve.ps1`** à la racine : maquette statique / mini-serveur PowerShell, sans lien avec l’app PHP sous XAMPP.

Si vous avez besoin d’une sauvegarde de l’ancienne maquette, récupérez-la depuis l’historique Git ou une copie locale.

---

## Licence / crédits

- Thème front : **FoodMart** (voir pied de page du site).
- Thème admin : **Argon Dashboard Tailwind** (dossier `argon-dashboard-tailwind-1.0.1`).
