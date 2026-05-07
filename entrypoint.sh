#!/bin/bash
set -e

# 1. Permisos (Crucial para que Laravel escriba los caches)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# 2. Generar llave si no existe
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null && [ -z "$APP_KEY" ]; then
    php artisan key:generate --no-interaction
fi

# 3. ACTIVAR FLUX (Esto registra los componentes en el sistema)
echo "🔑 Activando Flux..."
php artisan flux:activate "${FLUX_EMAIL}" "${FLUX_KEY}" --no-interaction

# 4. OPTIMIZAR (Ahora que Flux ya está registrado, no fallará)
echo "⚡ Optimizando..."
php artisan config:cache
php artisan route:cache
php artisan view:cache # <--- Si Flux está activo, esto ya no da error

# 5. Migraciones
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=8000
