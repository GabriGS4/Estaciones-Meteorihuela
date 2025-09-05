# Imagen base con PHP, Composer y extensiones necesarias
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Generar la APP_KEY automáticamente
RUN php artisan key:generate

# Optimizar la configuración
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Puerto expuesto por Laravel
EXPOSE 8000

# Comando para iniciar la app en Render
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
