#!/bin/bash

cd /var/www

# Générer la clé si manquante
php artisan key:generate --force

# Cache config
php artisan config:cache
php artisan route:cache

# Migrations
php artisan migrate --force

# Démarrer supervisor
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf