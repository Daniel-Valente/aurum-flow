#!/bin/bash
set -e

cd /var/www/html

echo "🔧 Ajustando configuración de DB para Docker..."
if [ -f .env ]; then
    sed -i 's/^DB_HOST=.*/DB_HOST=postgres/' .env
    sed -i 's/^DB_PORT=.*/DB_PORT=5432/' .env
else
    echo "⚠️ .env no existe, usando variables del entorno"
fi

echo "🔑 Verificando APP_KEY..."
if [ -f .env ]; then
    if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
        php artisan key:generate --no-interaction
    fi
else
    echo "⚠️ Sin .env, APP_KEY debe venir del entorno"
fi

php artisan flux:activate "valente.gar.daniel@gmail.com" "a550edb3-99c9-430d-8af2-5abcd3820adb" --no-interaction

echo "📦 Regenerando manifest de paquetes..."
php artisan package:discover --ansi   # ✅ regenera sin paquetes dev

echo "⚡ Optimizando Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "📁 Creando directorios de storage..."

mkdir -p storage/app/private
mkdir -p storage/app/private/livewire-tmp
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs

echo "🗂️ Permisos de storage..."
chmod -R 775 storage/app
chown -R www-data:www-data storage bootstrap/cache
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

echo "🗑️ Recreando base de datos..."
php artisan permission:cachce-reset
php artisan migrate:fresh --seed

echo "🚀 Iniciando servidor Laravel..."
chmod -R 755 public/build

exec php artisan serve --host=0.0.0.0 --port=8000
