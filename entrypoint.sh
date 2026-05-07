#!/bin/bash
set -e

cd /var/www/html

echo "🔧 Ajustando configuración de DB para Docker..."
if [ -f .env ]; then
    echo "🔧 Ajustando configuración de DB para Docker..."
    sed -i 's/^DB_HOST=.*/DB_HOST=postgres/' .env
    sed -i 's/^DB_PORT=.*/DB_PORT=5432/' .env
else
    echo "⚠️ .env no existe, usando variables del entorno"
fi

echo "🔑 Verificando APP_KEY..."
if [ -f .env ]; then
    echo "🔑 Verificando APP_KEY..."

    if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
        php artisan key:generate --no-interaction
    fi
else
    echo "⚠️ Sin .env, APP_KEY debe venir del entorno"
fi

echo "📦 Regenerando manifest de paquetes..."
php artisan package:discover --ansi   # ✅ regenera sin paquetes dev

echo "🔑 Activando Flux UI..."
echo "🔑 Activando Flux UI..."
php artisan flux:activate --email="${FLUX_EMAIL}" --key="${FLUX_KEY}" --no-interaction

echo "⚡ Optimizando Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "🗂️ Permisos de storage..."
chmod -R 775 storage bootstrap/cache

echo "⏳ Esperando PostgreSQL y migrando..."
max_attempts=30
attempt=0
until php artisan migrate --no-interaction --force 2>/dev/null; do
    attempt=$((attempt + 1))
    if [ $attempt -eq $max_attempts ]; then
        echo "❌ No se pudo conectar a PostgreSQL después de $max_attempts intentos"
        exit 1
    fi
    echo "PostgreSQL no está listo, reintentando... ($attempt/$max_attempts)"
    sleep 2
done

if [ "${FRESH_DB}" = "true" ]; then
    echo "🗑️ Recreando base de datos..."
    php artisan migrate:fresh --seed --no-interaction
fi

echo "🚀 Iniciando servidor Laravel..."
exec php artisan serve --host=0.0.0.0 --port=8000
