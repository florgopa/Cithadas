# Imagen oficial de PHP con Apache
FROM php:8.2-apache

# Copiar archivos del proyecto al directorio raíz de Apache
COPY . /var/www/html/

# Habilitar extensiones si las necesitás (ejemplo: mysqli)
RUN docker-php-ext-install mysqli

# Exponer el puerto
EXPOSE 80
