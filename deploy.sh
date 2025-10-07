#!/bin/bash

echo "🚀 Déploiement EcoRide sur Heroku"
echo "=================================="

# Vérifier que Heroku CLI est installé
if ! command -v heroku &> /dev/null; then
    echo "❌ Heroku CLI n'est pas installé. Installez-le d'abord."
    exit 1
fi

# Vérifier la connexion Heroku
if ! heroku auth:whoami &> /dev/null; then
    echo "❌ Vous n'êtes pas connecté à Heroku. Connectez-vous d'abord :"
    echo "   heroku login"
    exit 1
fi

# Demander le nom de l'application
read -p "📝 Entrez le nom de votre application Heroku (ex: ecoride-app): " APP_NAME

# Créer l'application Heroku
echo "🔧 Création de l'application Heroku..."
heroku create $APP_NAME

# Ajouter les addons PostgreSQL et MongoDB
echo "🗄️ Ajout des addons de base de données..."
heroku addons:create heroku-postgresql:mini --app $APP_NAME
heroku addons:create mongolab:sandbox --app $APP_NAME

# Configurer les variables d'environnement
echo "⚙️ Configuration des variables d'environnement..."
heroku config:set APP_ENV=prod --app $APP_NAME
heroku config:set APP_SECRET=$(openssl rand -hex 32) --app $APP_NAME
heroku config:set APP_TRUSTED_HOSTS="*.herokuapp.com" --app $APP_NAME
heroku config:set JWT_PASSPHRASE=$(openssl rand -hex 32) --app $APP_NAME
heroku config:set CORS_ALLOW_ORIGIN="https://$APP_NAME.herokuapp.com" --app $APP_NAME
heroku config:set DEFAULT_URI="https://$APP_NAME.herokuapp.com" --app $APP_NAME

# Déployer
echo "🚀 Déploiement en cours..."
git init
git add .
git commit -m "Initial commit for Heroku deployment"
git push heroku main

echo "✅ Déploiement terminé !"
echo "🌐 Votre application est disponible sur : https://$APP_NAME.herokuapp.com"
echo ""
echo "📋 Prochaines étapes :"
echo "1. Exécutez les migrations : heroku run php bin/console doctrine:migrations:migrate --app $APP_NAME"
echo "2. Configurez votre frontend pour pointer vers : https://$APP_NAME.herokuapp.com"
