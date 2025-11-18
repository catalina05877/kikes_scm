# Usar imagen base de PHP con Apache
FROM php:8.1-apache

# Instalar extensiones necesarias para PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Habilitar mod_rewrite para Apache (útil para URLs amigables)
RUN a2enmod rewrite

# Copiar archivos de configuración primero
COPY composer.json composer.lock /var/www/html/

# Instalar dependencias de PHP
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# Copiar el resto de los archivos del proyecto
COPY . /var/www/html/

# Cambiar permisos para que Apache pueda acceder
RUN chown -R www-data:www-data /var/www/html/

# Exponer el puerto 80
EXPOSE 80

# Comando por defecto para iniciar Apache
CMD ["apache2-foreground"]
