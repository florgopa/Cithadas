#!/usr/bin/env bash
set -e

# Vars por defecto (podés sobreescribirlas con env vars en Render)
DB_NAME="${DB_NAME:-cithadas_db}"
DB_USER="${DB_USER:-appuser}"
DB_PASS="${DB_PASS:-apppass}"

# Inicializar datos si no existe el datadir
if [ ! -d "/var/lib/mysql/mysql" ]; then
  echo "[Init] Inicializando datadir de MariaDB..."
  mariadb-install-db --user=mysql --datadir=/var/lib/mysql > /dev/null
fi

# Iniciar MariaDB
echo "[Start] Arrancando MariaDB..."
mysqld_safe --datadir=/var/lib/mysql --socket=/var/run/mysqld/mysqld.sock &
MYSQL_PID=$!

# Esperar a que esté listo
echo "[Wait] Esperando a MySQL..."
for i in {1..30}; do
  if mariadb -uroot -e "SELECT 1" >/dev/null 2>&1; then
    break
  fi
  sleep 1
done

# Crear DB y usuario si no existen
echo "[Setup] Creando DB y usuario si no existen..."
mariadb -uroot <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'%';
FLUSH PRIVILEGES;
SQL

# Importar .sql solo si la DB está vacía (sin tablas)
TABLES_COUNT=$(mariadb -N -uroot -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';")
if [ "$TABLES_COUNT" = "0" ] && [ -f "/var/www/html/cithadas_db.sql" ]; then
  echo "[Import] Importando cithadas_db.sql..."
  mariadb -uroot "${DB_NAME}" < /var/www/html/cithadas_db.sql
else
  echo "[Import] Saltando import (DB con tablas o .sql no encontrado)."
fi

# Ajuste de Apache (opcional)
export APACHE_DOCUMENT_ROOT=/var/www/html

echo "[Start] Iniciando Apache en primer plano..."
exec apache2-foreground
