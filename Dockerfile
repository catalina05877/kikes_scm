# Usar imagen base de PHP con Apache
FROM php:8.1-apache

# Instalar extensiones necesarias para PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar mod_rewrite para Apache (útil para URLs amigables)
RUN a2enmod rewrite

# Copiar los archivos del proyecto al directorio web de Apache
COPY . /var/www/html/

# Cambiar permisos para que Apache pueda acceder
RUN chown -R www-data:www-data /var/www/html/

# Exponer el puerto 80
EXPOSE 80

# Comando por defecto para iniciar Apache
CMD ["apache2-foreground"]
