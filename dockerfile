# Imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones necesarias (ejemplo: mysqli, pdo, pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos del proyecto al directorio raíz de Apache
COPY . /var/www/html/

# Establecer permisos correctos para Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exponer el puerto
EXPOSE 80
