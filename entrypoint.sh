#!/bin/bash
set -e

echo "🔧 Generando auth.json para Flux UI..."

# Validar que las variables estén presentes
if [ -z "$FLUX_USERNAME" ] || [ -z "$FLUX_PASSWORD" ]; then
  echo "⚠️  ADVERTENCIA: FLUX_USERNAME o FLUX_PASSWORD no están definidos en el .env"
  echo "     El auth.json se generará vacío y composer puede fallar si Flux UI es requerido."
fi

# Crear auth.json en la raíz del proyecto con las credenciales del .env
cat > /var/www/html/auth.json <<EOF
{
    "http-basic": {
        "composer.fluxui.dev": {
            "username": "${FLUX_USERNAME}",
            "password": "${FLUX_PASSWORD}"
        }
    }
}
EOF

echo "✅ auth.json creado correctamente."

echo "📦 Instalando dependencias con Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "🔑 Generando APP_KEY si no existe..."
php artisan key:generate --no-interaction 2>/dev/null || true

echo "🧹 Limpiando caché de configuración..."
php artisan config:clear

echo "🧹 Limpiando caché de aplicación..."
php artisan cache:clear

echo "🔐 Reseteando caché de permisos..."
php artisan permission:cache-reset 2>/dev/null || echo "⚠️  permission:cache-reset omitido (¿está instalado spatie/laravel-permission?)"

if [ "${FRESH_DB}" = "true" ]; then
  echo "🗄️  Ejecutando migrate:fresh --seed (FRESH_DB=true)..."
  php artisan migrate:fresh --seed --no-interaction
else
  echo "🗄️  Ejecutando migrate (sin borrar datos)..."
  php artisan migrate --no-interaction --force
fi

echo "🚀 Iniciando servidor Laravel en puerto 8000..."
exec php artisan serve --host=0.0.0.0 --port=8000
