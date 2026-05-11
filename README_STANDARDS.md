# 🚀 Guide de Standardisation pour le Merge Final (EcoByte)

Ce document contient les règles strictes à suivre pour que la fusion (merge) de toutes les branches (Mohamed, Ilyes, Rayen, Selem) se passe sans erreurs et que le projet fonctionne comme un tout cohérent.

---

## 🏗️ 1. Structure Globale & Naming (Anti-Conflits)
*Pour éviter que les fichiers ne s'écrasent lors du merge.*

1.  **Préfixage des Classes** : Ne nommez pas vos fichiers `Controller.php`. Utilisez un préfixe lié à votre module.
    - ✅ `RecetteController.php`, `FitnessController.php`, `SanteController.php`.
    - ❌ `Controller.php`, `AdminController.php`.
2.  **Modèles** : Même règle pour les modèles.
    - ✅ `Recette.php`, `ProgrammeFitness.php`.
3.  **Vues** : Gardez vos vues dans des sous-dossiers spécifiques si possible, ou nommez-les clairement.

---

## 🧭 2. Chemins & Liens (Routing)
*Pour que les liens ne se cassent pas après la fusion.*

1.  **Liens HTML (href, src)** : Utilisez toujours des chemins absolus par rapport à la racine du projet (`/2int/`).
    - ✅ `<a href="/2int/view/front/front.php">`
    - ❌ `<a href="front.php">` (ne fonctionne plus si on change de dossier).
2.  **Inclusions PHP (require/include)** : Utilisez toujours `__DIR__` pour sécuriser les chemins.
    - ✅ `require_once __DIR__ . '/../../config/database.php';`

---

## 🎨 3. UI Unique (Sidebar & Hub)
*Pour que l'utilisateur ne sente pas qu'il change de site.*

1.  **Sidebar Commune** : Le fichier `view/back/sidebar.php` est le fichier de référence pour **tous**.
    - Lors du merge, ce fichier contiendra tous les liens de tous les modules.
    - En attendant, chaque branche doit avoir la même structure de sidebar.
2.  **Hub (`index.php`)** : Il reste à la racine. Il contient les liens vers `/2int/view/front/front.php`, `/2int/public/index.php`, etc.

---

## 🗄️ 4. Base de Données Unique
*Toutes les branches doivent travailler sur la même base.*

1.  **Database** : `gestion_allergie`.
2.  **Connexion** : Un seul fichier de config `config/database.php` pour tout le monde.
3.  **Tables** : Ne modifiez pas les tables des autres. Si vous avez besoin de nouvelles tables, ajoutez-les avec un nom explicite (ex: `fitness_exercices`).

---

## 🛡️ 5. Isolation Front/Back (Règle d'Or)
1.  **Front-Office** : Aucune trace d'administration. Pas de bouton "Admin", pas de lien vers le dashboard.
2.  **Back-Office** : Accessible uniquement via `/2int/view/back/back.php`.

---

## 📝 Checklist de Validation avant Merge :
- [ ] Mes contrôleurs sont préfixés (ex: `Recette...`).
- [ ] Tous mes liens `href` commencent par `/2int/`.
- [ ] J'utilise `__DIR__` pour mes `require`.
- [ ] Mon module utilise la base de données `gestion_allergie`.
- [ ] J'ai testé ma navigation depuis le Hub principal.

---
*En respectant ces règles, le merge final sera instantané et sans bugs.*
