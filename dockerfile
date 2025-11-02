# Imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql pdo_sqlite

# Copiar archivos del proyecto al directorio raíz de Apache
COPY . /var/www/html/

# Dar permisos al directorio de la base de datos (por si usás SQLite)
RUN chmod -R 777 /var/www/html/cithadas_db || true

# Establecer permisos generales para Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configurar el DocumentRoot de Apache (opcional, si usás index.php en raíz)
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# Exponer el puerto
EXPOSE 80

# Iniciar Apache
CMD ["apache2-foreground"]
