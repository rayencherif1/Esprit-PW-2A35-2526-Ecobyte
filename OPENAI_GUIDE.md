# 🚀 Guide d'utilisation - Système de résumé IA OpenAI

## 📋 Vue d'ensemble

Le système de résumé automatique IA utilise maintenant **l'API OpenAI réelle** pour générer des résumés intelligents et pertinents de vos articles sur la nutrition et l'écologie.

## 🔧 Configuration requise

### 1. Obtenir une clé API OpenAI
- Rendez-vous sur [OpenAI Platform](https://platform.openai.com/account/api-keys)
- Créez un compte ou connectez-vous
- Générez une nouvelle clé API
- **Important** : Gardez cette clé secrète !

### 2. Configurer la clé API
- Ouvrez [setup_openai.php](setup_openai.php)
- Saisissez votre clé API dans le champ prévu
- Cliquez sur "💾 Sauvegarder la clé API"
- Rechargez la page pour vérifier que la clé est bien sauvegardée

### 3. Tester la configuration
- Sur la page de configuration, cliquez sur "🚀 Tester la génération de résumé"
- Un résumé d'exemple devrait être généré automatiquement

## 🎯 Fonctionnalités

### Résumés automatiques sur le blog
- Les articles s'affichent avec leurs résumés IA
- **Première visite** : Génération automatique du résumé
- **Visites suivantes** : Résumé mis en cache pour rapidité

### Interface d'administration
- [admin/summaries.php](admin/summaries.php) : Gérez tous les résumés
- Régénération manuelle des résumés
- Consultation des statuts de génération

## ⚙️ Configuration technique

| Paramètre | Valeur | Description |
|-----------|--------|-------------|
| **Modèle** | `gpt-3.5-turbo` | Modèle OpenAI utilisé |
| **Max tokens** | `150` | Longueur maximale du résumé |
| **Température** | `0.5` | Créativité (0.0 = précis, 1.0 = créatif) |
| **Timeout** | `30s` | Délai d'attente API |

## 🧪 Tests et diagnostic

### Pages de test disponibles :
- [test_openai.php](test_openai.php) - Test de génération de résumé
- [setup_openai.php](setup_openai.php) - Configuration et test API
- [force_migration.php](force_migration.php) - Vérification base de données

### Dépannage courant :

#### ❌ "Clé API OpenAI non configurée"
**Solution** : Configurez votre clé dans [setup_openai.php](setup_openai.php)

#### ❌ "Erreur réseau"
**Solution** : Vérifiez votre connexion internet

#### ❌ "Erreur API OpenAI (HTTP 401)"
**Solution** : Vérifiez que votre clé API est valide et active

#### ❌ "Erreur API OpenAI (HTTP 429)"
**Solution** : Quota API dépassé, patientez ou mettez à niveau votre plan

## 💰 Coûts approximatifs

- **Modèle GPT-3.5-turbo** : ~0.002$ par résumé
- **Volume estimé** : 500 résumés = ~1$
- **Coûts réels** : Consultez votre [dashboard OpenAI](https://platform.openai.com/usage)

## 🔒 Sécurité

- ✅ Clés API chiffrées côté serveur
- ✅ Appels HTTPS sécurisés
- ✅ Gestion d'erreurs sans exposition des clés
- ✅ Timeouts pour éviter les blocages

## 🚀 Utilisation avancée

### Modification des paramètres :
Éditez `config_ai.php` pour ajuster :
- `OPENAI_MODEL` : Changement de modèle
- `SUMMARY_MAX_TOKENS` : Longueur des résumés
- `SUMMARY_TEMPERATURE` : Créativité

### Prompt personnalisé :
Modifiez le prompt système dans `controller/ai_summary.php` :
```php
'content' => 'Vous êtes un assistant expert qui...'
```

---

## 🎉 Prêt à utiliser !

Une fois votre clé API configurée, le système génère automatiquement des résumés intelligents pour tous vos articles. Les résumés sont pertinents, concis et adaptés au thème de l'écologie et la nutrition.

**Testez dès maintenant :** [Blog principal](blog.php)