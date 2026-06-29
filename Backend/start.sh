#!/bin/bash

set -e

cd /var/www

# Créer un .env vide si absent
if [ ! -f .env ]; then
    touch .env
fi

# Injecter les variables Render dans .env
printenv | grep -E '^(APP_|DB_|REDIS_|QUEUE_|BROADCAST_|REVERB_|JWT_|MAIL_|GOOGLE_|MOBILE_|SESSION_|CACHE_|LOG_|TELESCOPE_|FIREBASE_|AI_SERVICE_|FILESYSTEM_|BCRYPT_|PORT)' > /tmp/render_env.txt

while IFS='=' read -r key rest; do
    value="${rest}"
    grep -v "^${key}=" .env > .env.tmp 2>/dev/null || true
    mv .env.tmp .env
    printf '%s=%s\n' "$key" "$value" >> .env
done < /tmp/render_env.txt

# Vider les anciens caches
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

# Lancer les seeders EN ARRIÈRE-PLAN après démarrage supervisord
# firstOrCreate évite les doublons — safe à relancer
(
    sleep 15  # Attendre que supervisord + php-fpm soient prêts
    cd /var/www
    php artisan db:seed --class=UserSeeder --force --no-ansi
    php artisan db:seed --class=ZoneSeeder --force --no-ansi
    php artisan db:seed --class=IncidentSeeder --force --no-ansi
    php artisan db:seed --class=AssignmentSeeder --force --no-ansi
) &

# Démarrer supervisor (au premier plan — ouvre le port immédiatement)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf