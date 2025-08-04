# Gestor de Turnos para Negocios "Cithadas"

Aplicación web desarrollada en PHP y MySQL que permite a distintos negocios (peluquerías, estéticas, barberías, etc.) ofrecer sus servicios, y a los clientes reservar turnos a través de esta.

## Funcionalidades

### Cliente
- Registro y login
- Búsqueda de servicios por palabra clave
- Visualización de servicios disponibles
- Reserva de turnos (solo si está logueado)

### Negocio
- Registro y login
- Carga y edición de servicios ofrecidos
- Eliminación de servicios
- Visualización de turnos reservados

### Administrador
- Acceso a la lista completa de usuarios y negocios registrados
- Eliminación de usuarios y servicios (moderación)

## Tecnologías utilizadas

- PHP puro
- MySQL (phpMyAdmin)
- HTML + CSS personalizado
- JavaScript para validaciones básicas y animaciones

## 🗃Base de datos

Incluye:
- archivo `cithadas_db.sql` con la estructura y datos básicos
- relaciones entre tablas: usuario, negocio, servicio, reserva

## Usuarios de prueba

### 🛠Administrador
- **Email**: `admin@cithadas.com`
- **Contraseña**: `123`

### Cliente
- **Email**: `cliente123@correo.com`
- **Contraseña**: `cliente123`

- **Email**: `marianbrook@gmail.com`
- **Contraseña**: `cliente123`

- **Email**: `gladysrussell@gmail.com`
- **Contraseña**: `cliente456`

### 🏪 Negocios
- **Dueño**: Salon Glamour  
  - **Email**: `salonglamour@gmail.com`  
  - **Contraseña**: `negocio456`

- **Dueño**: Spa Brillitos  
  - **Email**: `spabrillitos@gmail.com`  
  - **Contraseña**: `negocio789`

- **Dueño**: Barbería PuraVida  
  - **Email**: `puravidax@gmail.com`  
  - **Contraseña**: `negocioABC`

- **Email**: `negocio1@gmail.com`  
  - **Contraseña**: `negocio123`

## Requisitos para correr el proyecto

- Tener XAMPP o similar
- Clonar este repo en la carpeta `htdocs`
- Importar `cithadas_db.sql` en phpMyAdmin
- Iniciar Apache y MySQL desde XAMPP
- Acceder a `http://localhost/ACN2BV-GOMEZ_PACHECO-FINAL`

---

*Este proyecto fue desarrollado como trabajo final para la materia de Programación Web 2.*
