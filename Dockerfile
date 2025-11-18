# Usar imagen base de PHP con Apache
FROM php:8.1-apache

# Instalar dependencias del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libssl-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    curl \
    openssl \
    mbstring \
    iconv \
    ctype \
    filter \
    hash \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Habilitar mod_rewrite para Apache (útil para URLs amigables)
RUN a2enmod rewrite

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de configuración primero
COPY composer.json composer.lock ./

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copiar el resto de los archivos del proyecto
COPY . .

# Cambiar permisos para que Apache pueda acceder
RUN chown -R www-data:www-data /var/www/html/

# Exponer el puerto 80
EXPOSE 80

# Comando por defecto para iniciar Apache
CMD ["apache2-foreground"]
