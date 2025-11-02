# PHP + Apache
FROM php:8.2-apache

# Paquetes base + MariaDB server & client
RUN apt-get update && apt-get install -y --no-install-recommends \
    mariadb-server mariadb-client \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Config MySQL básica (directorio de datos)
RUN mkdir -p /var/run/mysqld && chown -R mysql:mysql /var/run/mysqld \
    && chown -R mysql:mysql /var/lib/mysql

# Script de arranque (MySQL + import + Apache)
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
