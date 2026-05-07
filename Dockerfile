FROM php:8.3-cli

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl libpq-dev libpng-dev libonig-dev \
    libxml2-dev libxslt-dev libzip-dev libicu-dev \
    zip unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip fileinfo xsl soap intl \
    && docker-php-ext-enable opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurar OPcache
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Node.js 20.x
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ARG FLUX_EMAIL
ARG FLUX_KEY

ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV FLUX_EMAIL=${FLUX_EMAIL}
ENV FLUX_KEY=${FLUX_KEY}

# Copiar archivos de configuración primero
COPY composer.json composer.lock ./

# ✅ Instalar TODAS las dependencias (incluyendo dev) temporalmente
RUN composer install --no-interaction --prefer-dist

# Copiar el resto del proyecto
COPY . .

# ✅ Activar Flux ANTES de optimizar
RUN php artisan flux:activate "${FLUX_EMAIL}" "${FLUX_KEY}"

# ✅ Ahora sí optimizar autoloader sin dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend
RUN npm ci && npm run build && rm -rf node_modules

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
