<?php
/**
 * Fichier de configuration central — adaptez les constantes sur chaque PC (XAMPP).
 * Ce fichier est inclus au tout début de chaque point d’entrée (public/index.php, etc.).
 */

declare(strict_types=1);

// -------------------------------------------------------------------------
// Affichage des erreurs PHP : en développement true, en production false.
// -------------------------------------------------------------------------
const APP_DEBUG = true;

if (APP_DEBUG) {
    error_reporting(E_ALL); // On signale toutes les erreurs
    ini_set('display_errors', '1'); // On les affiche dans la page (dev seulement)
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// -------------------------------------------------------------------------
// Connexion MySQL — PDO uniquement (mysqli interdit selon le cahier des charges).
// Remplacez utilisateur / mot de passe si votre XAMPP diffère.
// -------------------------------------------------------------------------
const DB_HOST = '127.0.0.1'; // Hôte MySQL (localhost)
const DB_NAME = 'nutrition_sante'; // Nom de la base (créez-la avant import SQL)
const DB_USER = 'root'; // Utilisateur MySQL par défaut sous XAMPP
const DB_PASS = ''; // Mot de passe (souvent vide sur XAMPP Windows)
const DB_CHARSET = 'utf8mb4'; // Jeu de caractères recommandé pour le français

// -------------------------------------------------------------------------
// Chemins fichiers (pour require) — ne pas mettre de slash final.
// -------------------------------------------------------------------------
define('ROOT_PATH', dirname(__DIR__)); // Racine du projet (parent de /config)
define('APP_PATH', ROOT_PATH . '/app'); // Dossier MVC
define('VIEW_PATH', APP_PATH . '/Views'); // Vues PHP

// -------------------------------------------------------------------------
// URLs publiques — À MODIFIER quand vous copiez le projet sur un autre PC.
// Exemple si le dossier s’appelle "projetselem" dans htdocs et point de terminaison "public" :
//   http://localhost/projetselem/public/
// -------------------------------------------------------------------------
define('BASE_URL', 'http://localhost/projetselem/public'); // Sans slash final
define('ADMIN_URL', BASE_URL . '/admin'); // Espace d’administration

// -------------------------------------------------------------------------
// Template FoodMart (front) : dossier à la racine du projet, sans slash final.
// Ex. c:\projetselem\FoodMart-1.0.0\FoodMart-1.0.0\
// -------------------------------------------------------------------------
define(
    'URL_FOODMART',
    'http://localhost/projetselem/FoodMart-1.0.0/FoodMart-1.0.0'
);

// -------------------------------------------------------------------------
// Template Argon Dashboard Tailwind (back-office) : dossier build/assets.
// Ex. c:\projetselem\argon-dashboard-tailwind-1.0.1\argon-dashboard-tailwind-1.0.1\build\assets
// -------------------------------------------------------------------------
define(
    'URL_ARGON_ASSETS',
    'http://localhost/projetselem/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/assets'
);

// -------------------------------------------------------------------------
// Types d’exercices / programmes (énumération métier, alignée sur la base).
// -------------------------------------------------------------------------
const TYPES_ENTRAINEMENT = ['musculation', 'cardio', 'perte_de_poids'];

// -------------------------------------------------------------------------
// Muscles wger (API alternatives) — clé = id API, valeur = libellé français.
// -------------------------------------------------------------------------
/*
 * -------------------------------------------------------------------------
 * INSTALLATION RAPIDE (XAMPP) — à refaire sur l’autre PC après copie du dossier
 * -------------------------------------------------------------------------
 * 1. Créer la base MySQL `nutrition_sante` (utf8mb4).
 * 2. Importer le fichier `database/schema.sql` via phpMyAdmin.
 * 3. Vérifier DB_USER / DB_PASS dans ce fichier si votre MySQL n’est pas root sans mot de passe.
 * 4. Ajuster BASE_URL, ADMIN_URL, URL_FOODMART et URL_ARGON_ASSETS selon htdocs.
 * 5. Front : {BASE_URL}/index.php?action=home — Admin : {ADMIN_URL}/index.php?action=dashboard
 * 6. Gardez les dossiers FoodMart-1.0.0 et argon-dashboard-tailwind-1.0.1 à côté de /public.
 * -------------------------------------------------------------------------
 */

const WGER_MUSCLES = [
    1 => 'Biceps',
    2 => 'Épaules',
    3 => 'Serratus',
    4 => 'Pectoraux',
    5 => 'Triceps',
    6 => 'Abdominaux',
    7 => 'Mollets',
    8 => 'Fessiers',
    9 => 'Trapèzes',
    10 => 'Quadriceps',
    11 => 'Ischio-jambiers',
    12 => 'Grands dorsaux',
    13 => 'Brachial',
    14 => 'Obliques',
    15 => 'Soléaire',
];
