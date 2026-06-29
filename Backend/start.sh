#!/bin/bash

set -e

cd /var/www

# Créer un .env vide si absent (Laravel en a besoin pour booter)
if [ ! -f .env ]; then
    touch .env
fi

# Injecter toutes les variables d'environnement Render dans .env
# (config:cache lit le fichier .env, pas getenv() directement)
printenv | grep -E '^(APP_|DB_|REDIS_|QUEUE_|BROADCAST_|REVERB_|JWT_|MAIL_|GOOGLE_|MOBILE_|SESSION_|CACHE_|LOG_|TELESCOPE_|FIREBASE_|AI_SERVICE_|FILESYSTEM_|BCRYPT_|PORT)' > /tmp/render_env.txt

while IFS='=' read -r key rest; do
    value="${rest}"
    # Supprimer la ligne existante
    grep -v "^${key}=" .env > .env.tmp 2>/dev/null || true
    mv .env.tmp .env
    # Ajouter la nouvelle valeur
    printf '%s=%s\n' "$key" "$value" >> .env
done < /tmp/render_env.txt

# Vider les anciens caches (évite les caches corrompus entre déploiements)
php artisan config:clear --no-ansi 2>/dev/null || true
php artisan route:clear --no-ansi 2>/dev/null || true
php artisan cache:clear --no-ansi 2>/dev/null || true
php artisan view:clear --no-ansi 2>/dev/null || true

# Reconstruire le cache
php artisan config:cache --no-ansi
php artisan route:cache --no-ansi

# Storage link
php artisan storage:link --force --no-ansi 2>/dev/null || true

# Migrations
php artisan migrate --force --no-ansi

# Seed (à retirer après le premier déploiement réussi)
php artisan db:seed --force --no-ansi


# Démarrer supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf