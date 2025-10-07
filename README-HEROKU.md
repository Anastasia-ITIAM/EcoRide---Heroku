# EcoRide - Déploiement Heroku

## 🎯 Configuration

Ce dossier contient une version adaptée d'EcoRide pour le déploiement sur Heroku.

### 🔄 Différences avec la version Docker :

- **Base de données** : PostgreSQL (au lieu de MySQL)
- **MongoDB** : MongoDB Atlas via addon Heroku
- **Configuration** : Variables d'environnement Heroku
- **Serveur web** : Nginx via Heroku PHP buildpack

## 🚀 Déploiement rapide

```bash
# 1. Exécuter le script de déploiement
./deploy.sh

# 2. Suivre les instructions à l'écran
```

## 🔧 Déploiement manuel

```bash
# 1. Installer Heroku CLI
# https://devcenter.heroku.com/articles/heroku-cli

# 2. Se connecter à Heroku
heroku login

# 3. Créer l'application
heroku create votre-app-name

# 4. Ajouter PostgreSQL
heroku addons:create heroku-postgresql:mini

# 5. Ajouter MongoDB
heroku addons:create mongolab:sandbox

# 6. Configurer les variables d'environnement
heroku config:set APP_ENV=prod
heroku config:set APP_SECRET=$(openssl rand -hex 32)
heroku config:set APP_TRUSTED_HOSTS="*.herokuapp.com"
heroku config:set JWT_PASSPHRASE=$(openssl rand -hex 32)
heroku config:set CORS_ALLOW_ORIGIN="https://votre-app-name.herokuapp.com"
heroku config:set DEFAULT_URI="https://votre-app-name.herokuapp.com"

# 7. Déployer
git init
git add .
git commit -m "Initial commit"
git push heroku main

# 8. Exécuter les migrations
heroku run php bin/console doctrine:migrations:migrate
```

## 📋 Configuration Frontend

Après le déploiement, mettez à jour votre frontend pour pointer vers l'API Heroku :

```javascript
// Dans vos fichiers JS, remplacez :
const API_URL = 'http://localhost:8000';

// Par :
const API_URL = 'https://votre-app-name.herokuapp.com';
```

## 🔍 Vérification

```bash
# Vérifier les logs
heroku logs --tail

# Vérifier les variables d'environnement
heroku config

# Accéder à l'application
heroku open
```

## 🗄️ Bases de données

- **PostgreSQL** : Automatiquement configuré via `DATABASE_URL`
- **MongoDB** : Automatiquement configuré via `MONGODB_URL`

## 🔐 Sécurité

- Les clés JWT sont générées automatiquement
- Les mots de passe sont sécurisés
- CORS configuré pour votre domaine Heroku
