# Checklist d'intégration - Résumé IA Ecobyte

## ✅ Étapes d'installation

### 1. Base de données
- [ ] Exécuter `migration_add_summary.sql` pour ajouter la colonne `summary`
- [ ] Vérifier que la colonne est présente dans la table `post`
  ```sql
  DESCRIBE post;
  -- Doit afficher une ligne 'summary | TEXT'
  ```

### 2. Configuration API
- [ ] Créer un compte OpenAI (https://platform.openai.com)
- [ ] Générer une clé API
- [ ] Mettre à jour `config_ai.php` avec votre clé API
  ```php
  define('OPENAI_API_KEY', 'sk-your-actual-key-here');
  ```
- [ ] Tester la connectivité API (voir section Tests)

### 3. Fichiers créés
- [ ] `controller/ai_summary.php` - Contrôleur IA
- [ ] `config_ai.php` - Configuration IA
- [ ] `api/get_summary.php` - API backend pour les résumés
- [ ] `view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/js/ai-summary.js` - JavaScript
- [ ] `view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/css/ai-summary.css` - Styles
- [ ] `admin/summaries.php` - Page admin de gestion
- [ ] `AI_SUMMARY_DOCUMENTATION.md` - Documentation

### 4. Fichiers modifiés
- [ ] `model/post.php` - Ajout de la propriété `summary`
- [ ] `controller/post.controller.php` - Mise à jour des méthodes CRUD
- [ ] `blog.php` - Intégration du conteneur et imports

### 5. Permissions
- [ ] `view/uploads/` - Vérifier les permissions (755)
- [ ] `admin/summaries.php` - Accessible uniquement aux admins

---

## 🧪 Tests

### Test 1: Vérifier la structure de la base de données
```php
<?php
require_once 'config.php';
$db = config::getConnexion();
$result = $db->query("DESCRIBE post");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
var_dump($columns);
// Doit contenir une colonne 'summary'
?>
```

### Test 2: Tester la fonction de résumé
```php
<?php
require_once 'controller/ai_summary.php';

$testContent = "Les protéines sont essentielles pour la croissance musculaire. " . 
               "Elles se trouvent dans les œufs, le poulet, les légumineuses et le poisson. " .
               "Pour une nutrition équilibrée, consommez au moins 1,6g de protéines par kg de poids corporel.";

$summary = generateSummary($testContent);
echo "Résumé généré: " . $summary;
// Doit retourner un résumé sans erreur
?>
```

### Test 3: Tester l'API backend
```bash
curl -X POST http://localhost/api/get_summary.php \
  -H "Content-Type: application/json" \
  -d '{"post_id": 1}'

# Doit retourner un JSON comme:
# {"success":true,"summary":"...","cached":false}
```

### Test 4: Vérifier le front-office
1. Accédez à `blog.php`
2. Un résumé avec le badge 🤖 doit s'afficher sous chaque article
3. Vérifier la console du navigateur (F12) pour les erreurs

### Test 5: Vérifier le back-office
1. Accédez à `admin/summaries.php`
2. Vous devez voir la liste des posts avec leur statut de résumé
3. Cliquer sur "Générer" ou "Régénérer" pour tester

---

## 🔧 Dépannage

### Problème: "Erreur lors de la génération du résumé"
**Solution:**
1. Vérifier la clé API dans `config_ai.php`
2. Vérifier la limite d'utilisation API OpenAI
3. Vérifier la connexion Internet/cURL

### Problème: "Unknown column 'summary'"
**Solution:**
- Exécuter la migration SQL:
  ```sql
  ALTER TABLE `post` ADD COLUMN `summary` TEXT NULL DEFAULT NULL AFTER `image`;
  ```

### Problème: Le script JavaScript ne charge pas
**Solution:**
1. Vérifier les chemins du fichier CSS/JS
2. Vérifier la console du navigateur (F12) pour les erreurs 404
3. Rafraîchir la page (Ctrl+F5)

### Problème: Erreur CORS ou de sécurité
**Solution:**
1. Vérifier les headers CORS si nécessaire
2. S'assurer que `/api/get_summary.php` est accessible
3. Vérifier les logs du serveur

---

## 📊 Monitoring

### Vérifier les résumés générés
```sql
-- Voir tous les posts avec leurs résumés
SELECT id, titre, summary FROM post WHERE summary IS NOT NULL;

-- Voir les posts sans résumé
SELECT id, titre FROM post WHERE summary IS NULL;

-- Compter les résumés générés
SELECT COUNT(*) as total, SUM(CASE WHEN summary IS NOT NULL THEN 1 ELSE 0 END) as with_summary FROM post;
```

---

## 🚀 Optimisations future

- [ ] Implémenter un cache Redis pour les résumés
- [ ] Ajouter un job en background pour générer les résumés
- [ ] Utiliser des modèles locaux (transformers) pour réduire les coûts
- [ ] Implémenter un rate limiting
- [ ] Ajouter des logs pour tracker les générations

---

## 📝 Notes

- Les résumés sont générés **à la demande** via JavaScript
- Les résumés sont **mis en cache** après génération
- L'API OpenAI coûte environ **$0.0005 par résumé** (au tarif actuel GPT-3.5-turbo)
- Pour tester sans coûts, vous pouvez générer des résumés manuels au départ

---

## ✨ Fin de l'intégration

Une fois toutes les étapes complétées, votre système de résumé IA est prêt à l'emploi ! 🎉
