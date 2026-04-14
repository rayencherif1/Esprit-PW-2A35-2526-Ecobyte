# Architecture MVC - Gestion d'Utilisateurs

## 📋 Vue d'ensemble

Cette application est une implémentation complète du pattern MVC (Model-View-Controller) en PHP avec les contraintes suivantes:

- ✅ **PDO uniquement** - Requêtes préparées pour la sécurité
- ✅ **Validation côté serveur** - Effectuée dans les Controllers
- ✅ **Pas de validation HTML5** - Pas de `required`, `type="email"`, etc.
- ✅ **Séparation MVC stricte** - Model, View, Controller indépendants
- ✅ **Routage simple** - Variables `$_GET['action']` pour les actions

---

## 🗂️ Structure du Projet

```
rayench/
├── index.php                 # Point d'entrée principal + Routeur
├── config.php                # Configuration PDO et classe Database
├── database.sql              # Script SQL pour créer tables
│
├── model/
│   ├── User.php             # Model User (CRUD)
│   └── Profil.php           # Model Profil (CRUD)
│
├── controllers/
│   ├── UserController.php   # Controller User (validation + logique)
│   └── ProfilController.php # Controller Profil (validation + logique)
│
└── view/front/
    ├── home.php             # Page d'accueil
    ├── users.php            # Liste des utilisateurs
    ├── add-user.php         # Formulaire ajout/modification
    └── error.php            # Page d'erreur
```

---

## 🚀 Installation & Configuration

### 1. Créer la base de données

Exécutez le script SQL dans PhpMyAdmin ou MySQL:

```sql
-- Copier le contenu de database.sql dans PhpMyAdmin
```

Ou via ligne de commande:

```bash
mysql -u root < C:\xampp\htdocs\rayench\database.sql
```

### 2. Configurer `config.php`

Modifiez les variables de connexion si nécessaire:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'rayench_db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_PORT', '3306');
```

### 3. Accéder à l'application

```
http://localhost/rayench/
```

---

## 📚 Explication du Pattern MVC

### **Model** (`model/`)

Le Model gère **l'accès à la base de données** uniquement.

**`model/User.php`** - Exemple:

```php
class User {
    // ✅ Méthodes de base CRUD
    public function getAllUsers()    // SELECT *
    public function getUserById($id) // SELECT WHERE id
    public function createUser($data) // INSERT
    public function updateUser($id, $data) // UPDATE
    public function deleteUser($id)   // DELETE
    
    // ✅ Méthodes utilitaires
    public function emailExists($email, $excludeId = null)
}
```

**Responsabilités:**
- Requêtes PDO préparées
- Gestion des exceptions PDOException
- Pas de validation (c'est au Controller)
- Pas de logique métier complexe

### **Controller** (`controllers/`)

Le Controller gère la **validation** et la **logique métier**.

**`controllers/UserController.php`** - Exemple:

```php
class UserController {
    // ✅ Valide les données avant INSERT/UPDATE
    private function validateUserData($data, $userId = null)
    
    // ✅ Crée avec validation
    public function createUser($data)
    
    // ✅ Capture les erreurs
    public function getErrors()
    public function getSuccess()
}
```

**Responsabilités:**
- Valider les données (`$_POST`)
- Appeler les méthodes du Model
- Capturer les erreurs
- Passer les données à la View

### **View** (`view/front/`)

La View affiche **uniquement le HTML/CSS/JS**.

**`view/front/users.php`** - Exemple:

```php
<!-- ✅ Affichage uniquement -->
<?php foreach ($users as $user): ?>
    <div><?php echo htmlspecialchars($user['email']); ?></div>
<?php endforeach; ?>

<!-- ✅ Pas de logique complexe -->
<!-- ✅ Utilise les variables du Controller -->
```

**Responsabilités:**
- Affichage HTML avec PHP simple
- Utiliser `htmlspecialchars()` pour l'XSS
- Pas d'accès direct à la base de données
- Pas de validation

---

## 🎯 Flux d'une Requête

### Exemple: Ajouter un utilisateur

```
1. Utilisateur accède à: index.php?action=addUser

2. index.php (Routeur)
   ├─ Récupère action = "addUser"
   ├─ Si GET: affiche formulaire vierge
   └─ Si POST: appelle UserController

3. UserController::createUser($_POST)
   ├─ Valide le nom (obligatoire, 2-100 caractères)
   ├─ Valide le prénom (obligatoire, 2-100 caractères)
   ├─ Valide l'email (obligatoire, format valide, unique)
   ├─ Valide le téléphone (optionnel, 9-20 caractères)
   └─ Si erreurs: retourne false, stocke erreurs

4. Si validation OK
   ├─ Appelle User::createUser($cleanData)
   ├─ Model exécute INSERT avec PDO préparé
   └─ Retourne ID du nouvel utilisateur

5. index.php
   ├─ Récupère le succès/les erreurs du Controller
   ├─ Passe à la View: $success, $errors, $users
   └─ Affiche users.php avec message

6. View (users.php)
   ├─ Affiche message de succès
   ├─ Liste les utilisateurs
   └─ Permet les actions (modifier, supprimer)
```

---

## 🔐 Validation Côté Serveur

Toute validation se fait dans le **Controller**, pas en HTML5:

```php
// ❌ PAS DE CECI:
<input type="text" required maxlength="100">

// ✅ MAIS CECI DANS LE CONTROLLER:
if (empty(trim($data['nom']))) {
    $this->errors[] = "Le nom est obligatoire";
}
if (strlen(trim($data['nom'])) > 100) {
    $this->errors[] = "Le nom ne peut pas dépasser 100 caractères";
}
```

### Validations implémentées:

**User:**
1. Nom: obligatoire, 2-100 caractères
2. Prénom: obligatoire, 2-100 caractères
3. Email: obligatoire, format valide, email unique
4. Téléphone: optionnel, 9-20 caractères si fourni

**Profil:**
1. Bio: optionnel, max 500 caractères
2. Adresse: optionnel, max 200 caractères
3. Ville: optionnel, max 100 caractères
4. Code postal: optionnel, format valide si fourni

---

## 📝 Schéma de Base de Données

### Table `users`

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| nom | VARCHAR(100) | NOT NULL |
| prenom | VARCHAR(100) | NOT NULL |
| email | VARCHAR(120) | NOT NULL, UNIQUE |
| telephone | VARCHAR(20) | NULL |
| date_creation | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

### Table `profils`

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| user_id | INT | NOT NULL, UNIQUE, FOREIGN KEY |
| bio | TEXT | NULL |
| adresse | VARCHAR(200) | NULL |
| ville | VARCHAR(100) | NULL |
| code_postal | VARCHAR(10) | NULL |
| date_creation | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

---

## 📡 Routes de l'Application

| Action | URL | Méthode | Description |
|--------|-----|--------|-------------|
| home | `index.php?action=home` | GET | Page d'accueil |
| users | `index.php?action=users` | GET | Liste des utilisateurs |
| addUser | `index.php?action=addUser` | GET/POST | Formulaire + création |
| editUser | `index.php?action=editUser&id=X` | GET/POST | Formulaire + modification |
| deleteUser | `index.php?action=deleteUser&id=X` | GET | Suppression |

---

## 💡 Bonnes Pratiques Implémentées

✅ **PDO avec requêtes préparées**
```php
$stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);
```

✅ **Nettoyage des données**
```php
$email = trim($data['email']);
$data['telephone'] = !empty(trim($data['telephone'])) ? trim($data['telephone']) : null;
```

✅ **Validation stricte**
```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $this->errors[] = "Email invalide";
}
```

✅ **Protection XSS**
```php
echo htmlspecialchars($user['email']);
```

✅ **Gestion d'erreurs**
```php
try {
    $stmt->execute();
} catch (PDOException $e) {
    throw new Exception("Erreur DB: " . $e->getMessage());
}
```

✅ **Séparation des responsabilités**
- Model: accès DB
- Controller: validation + logique
- View: affichage

---

## 🔧 Extension de l'Application

### Ajouter une nouvelle action

**Exemple: Afficher le profil d'un utilisateur**

1. **Controller** (`controllers/UserController.php`):
```php
public function getUserProfile($id) {
    if (!$this->validateId($id)) {
        $this->errors[] = "ID invalide";
        return false;
    }
    return $this->userModel->getUserById($id);
}
```

2. **View** (`view/front/profile.php`):
```php
<?php if ($user): ?>
    <h1><?php echo htmlspecialchars($user['prenom']); ?></h1>
<?php endif; ?>
```

3. **Routeur** (`index.php`):
```php
case 'profile':
    $userController = new UserController();
    $user = $userController->getUserProfile($_GET['id']);
    require __DIR__ . '/view/front/profile.php';
    break;
```

---

## ⚠️ Dépannage

### Erreur: "Base de données non trouvée"
→ Vérifier que `database.sql` a été exécuté
→ Vérifier les identifiants dans `config.php`

### Erreur: "PDOException: SQLSTATE[HY000]"
→ Vérifier que MySQL est lancé (XAMPP)
→ Vérifier la configuration de la base de données

### Les données ne s'enregistrent pas
→ Vérifier les erreurs dans la page (affichées en rouge)
→ Ouvrir la console du navigateur (F12)
→ Vérifier que la vue affiche bien `<?php echo htmlspecialchars($error); ?>`

---

## 📞 Support

Pour modifier la validation, éditez les Controllers:
- `controllers/UserController.php` → `validateUserData()`
- `controllers/ProfilController.php` → `validateProfilData()`

Pour ajouter un champ à un formulaire:
1. Ajouter une colonne dans la base de données
2. Ajouter une validation dans le Controller
3. Ajouter un `<input>` dans la Vue
4. Mettre à jour le Model (CREATE/UPDATE)

---

**Architecture créée le**: 2026-04-13  
**Version PHP**: 7.4+ recommended  
**Serveur**: XAMPP MySQL
