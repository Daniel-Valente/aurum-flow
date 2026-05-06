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

ARG FLUX_USERNAME
ARG FLUX_PASSWORD
RUN composer config --global http-basic.composer.fluxui.dev "$FLUX_USERNAME" "$FLUX_PASSWORD"

# ✅ Copiar todo el proyecto primero
COPY . .

# ✅ Luego instalar dependencias
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN npm ci

RUN npm run build && rm -rf node_modules

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
