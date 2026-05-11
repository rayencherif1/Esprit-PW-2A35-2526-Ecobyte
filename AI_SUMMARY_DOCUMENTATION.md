# Intégration du Résumé IA dans Ecobyte

## Vue d'ensemble
Cette fonctionnalité ajoute une génération automatique de résumés IA pour chaque post du blog. Les résumés sont générés via l'API OpenAI et affichés dynamiquement dans le front-office et peuvent être gérés dans le back-office admin.

## Architecture

### Fichiers ajoutés/modifiés

#### 1. **Base de données** (`migration_add_summary.sql`)
- Ajoute une colonne `summary` (TEXT) à la table `post`
- Permet de stocker les résumés générés

#### 2. **Modèle** (`model/post.php`)
- Ajoute la propriété `$summary`
- Ajoute les getters/setters `getSummary()` et `setSummary()`

#### 3. **Contrôleur IA** (`controller/ai_summary.php`)
- `generateSummary($postContent)` : génère un résumé via l'API OpenAI
- Utilise le modèle GPT-3.5-turbo
- Retourne un résumé de 2-3 phrases concises

#### 4. **Contrôleur Post** (`controller/post.controller.php`)
- Met à jour `addPost()` et `updatePost()` pour gérer les résumés
- Inclut les résumés dans les requêtes SQL INSERT/UPDATE

#### 5. **API Backend** (`api/get_summary.php`)
- Endpoint POST pour récupérer/générer les résumés
- Retourne un JSON avec le statut et le résumé

#### 6. **Front-office** (`blog.php`)
- Ajoute le lien vers le CSS et JS du résumé
- Ajoute un conteneur `ai-summary-container` après le contenu de chaque post

#### 7. **JavaScript Front-office** (`view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/js/ai-summary.js`)
- Charge les résumés via fetch API
- Affiche un loader pendant la génération
- Récupère les résumés en cache si disponibles

#### 8. **CSS Front-office** (`view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/css/ai-summary.css`)
- Styles pour le résumé IA (badge, conteneur, messages d'erreur)
- Animation loader pulse
- Design responsive

#### 9. **Back-office Admin** (`admin/summaries.php`)
- Page de gestion des résumés pour les admins
- Permet de voir le statut de chaque post
- Permet de régénérer les résumés à la demande

## Configuration

### 1. Migration de la base de données
```bash
# Exécuter la migration pour ajouter la colonne summary
mysql -u root < migration_add_summary.sql
```

### 2. Configuration de l'API OpenAI
Modifiez `controller/ai_summary.php` pour ajouter votre clé API :

```php
$apiKey = getenv('OPENAI_API_KEY') ?: 'sk-your-api-key-here';
```

Ou configurez via une variable d'environnement `.env` :
```
OPENAI_API_KEY=sk-your-api-key-here
```

### 3. Permission des fichiers
Assurez-vous que le dossier `view/uploads/` a les permissions d'écriture (755).

## Utilisation

### Front-office
- Les résumés s'affichent automatiquement sous chaque article du blog
- Si un résumé existe déjà en base, il est affiché directement
- Sinon, un loader s'affiche et le résumé est généré en arrière-plan
- Les résumés sont mis en cache dans la base de données

### Back-office
1. Accédez à `admin/summaries.php` (nécessite l'authentification admin)
2. Consultez le statut des résumés pour chaque post
3. Cliquez sur "Générer" ou "Régénérer" pour créer/mettre à jour un résumé
4. Les résumés se sauvegardent automatiquement en base de données

## Format du résumé
Les résumés sont générés avec le prompt suivant :
```
"Système: Vous êtes un assistant qui résume des articles sur la nutrition et l'écologie en 2-3 phrases concises."
```

## Gestion des erreurs

### Erreur API OpenAI (code 200 attendu)
- Retour à un résumé fallback : les 200 premiers caractères du contenu

### Pas assez de contenu (< 50 caractères)
- Aucun résumé n'est généré (retour vide)

### Erreur réseau
- Affichage d'un message d'erreur dans le front-office

## Performance

- Les résumés sont mis en cache après génération
- Les appels API ne se font qu'une seule fois par post (sauf régénération manuelle)
- Le JavaScript utilise fetch pour charger les résumés de manière asynchrone
- Aucun bloquage de l'affichage du post pendant la génération

## Sécurité

- Les résumés sont échappés via `htmlspecialchars()` pour éviter les injections XSS
- L'API `get_summary.php` valide l'ID du post avant de l'utiliser
- Les appels API OpenAI utilisent une authentification Bearer

## Statistiques/Monitoring

### À faire
- Ajouter des logs pour tracker les générations de résumés
- Ajouter des statistiques de coûts API
- Implémenter un rate limiting pour éviter les dépassements

## Exemple de résumé généré

**Article:** "10 aliments riches en protéines pour une alimentation équilibrée"

**Résumé IA:** "Cet article présente les meilleures sources de protéines naturelles pour optimiser votre nutrition. Les œufs, le poulet et les légumineuses sont recommandés pour leur profil nutritionnel complet. (Réponse générée automatiquement par l'IA Ecobyte.)"

## Dépannage

### Le résumé ne s'affiche pas
1. Vérifiez la clé API OpenAI dans `ai_summary.php`
2. Vérifiez les erreurs dans la console du navigateur
3. Vérifiez les logs serveur

### Erreur de connexion API
```
"Impossible de générer le résumé"
```
Solution: Vérifiez la clé API et la limite de requêtes API

### Colonne summary introuvable
```
SQL Error: Unknown column 'summary'
```
Solution: Exécutez le fichier `migration_add_summary.sql`

## Améliorations futures

- Utiliser des modèles locaux (transformers) pour réduire les coûts
- Ajouter des options de longueur de résumé
- Implémenter la traduction automatique des résumés
- Ajouter des résumés par langue
- Générer les résumés à la création du post (côté serveur)
