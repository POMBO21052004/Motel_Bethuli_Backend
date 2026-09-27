FROM php:8.3-cli

# Dépendances système nécessaires aux extensions PHP
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    zip unzip libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif \
       pcntl bcmath gd zip

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copie des fichiers de dépendances en premier (cache Docker)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader \
    --prefer-dist --optimize-autoloader

# Copie du reste de l'application
COPY . .
RUN composer dump-autoload --optimize

# Permissions des dossiers Laravel
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

# Script de démarrage : migrations + lancement serveur
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
