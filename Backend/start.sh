#!/bin/bash

cd /var/www

# Créer .env depuis les variables d'environnement
cp .env.example .env

# Générer la clé
php artisan key:generate --force

# Cache
php artisan config:cache
php artisan route:cache

# Migrations
php artisan migrate --force

# Démarrer supervisor
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf