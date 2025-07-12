-- Desactiva temporalmente la verificación de claves foráneas para permitir la creación de tablas en cualquier orden
SET FOREIGN_KEY_CHECKS = 0;

-- Elimina la base de datos si existe y la crea de nuevo para una importación limpia
DROP DATABASE IF EXISTS cithadas_db;
CREATE DATABASE IF NOT EXISTS cithadas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cithadas_db;

-- Tabla de usuarios
CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'negocio', 'cliente') NOT NULL DEFAULT 'cliente',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de negocios
CREATE TABLE negocio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT, -- Si un negocio está asociado a un usuario (dueño)
    nombre_negocio VARCHAR(100) NOT NULL, -- Cambiado a nombre_negocio para mayor claridad
    slogan VARCHAR(255), -- Añadido para el perfil del negocio
    direccion VARCHAR(255),
    descripcion TEXT,
    email_negocio VARCHAR(100) NOT NULL UNIQUE, -- Cambiado a email_negocio
    telefono VARCHAR(20),
    whatsapp VARCHAR(20), -- Añadido para contacto rápido
    rating DECIMAL(2,1) DEFAULT 0.0, -- Para calificaciones
    reviews_count INT DEFAULT 0, -- Para número de reseñas
    cover_image_path VARCHAR(255) NULL, -- Ruta a la imagen de portada
    logo_image_path VARCHAR(255) NULL,  -- Ruta a la imagen del logo
    estado ENUM('pendiente', 'activo', 'inactivo', 'rechazado') DEFAULT 'pendiente', -- Estado del negocio
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE SET NULL -- Si el usuario se borra, el negocio no se borra
);

-- Tabla de profesionales (empleados que ofrecen servicios)
CREATE TABLE profesional (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    especialidad VARCHAR(100), -- Ej: "Estilista", "Masajista", "Colorista"
    email VARCHAR(100) UNIQUE,
    telefono VARCHAR(20),
    FOREIGN KEY (negocio_id) REFERENCES negocio(id) ON DELETE CASCADE
);

-- Tabla de servicios
CREATE TABLE servicio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id INT NOT NULL, -- Referencia al negocio que ofrece el servicio
    nombre_servicio VARCHAR(100) NOT NULL, -- Cambiado a nombre_servicio
    categoria ENUM('Estética Corporal', 'Estética Facial', 'Masajes', 'Depilación', 'Peluquería', 'Uñas', 'Spa', 'Fitness', 'Otros') NOT NULL, -- Categorías alineadas con manage_services.php
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    duracion_estimada VARCHAR(50), -- Cambiado a VARCHAR para almacenar etiquetas como '30min-1h'
    estado ENUM('activo', 'inactivo') DEFAULT 'activo', -- Estado del servicio
    FOREIGN KEY (negocio_id) REFERENCES negocio(id) ON DELETE CASCADE
);

-- Tabla de turnos
CREATE TABLE turno (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL, -- Cliente que reserva el turno
    servicio_id INT NOT NULL, -- Servicio que se va a reservar
    profesional_id INT NULL, -- Profesional que realizará el servicio (opcional)
    fecha_turno DATE NOT NULL,
    hora_turno TIME NOT NULL,
    estado ENUM('pendiente','confirmado','cancelado','completado') DEFAULT 'pendiente', -- Añadido 'completado'
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicio(id) ON DELETE CASCADE,
    FOREIGN KEY (profesional_id) REFERENCES profesional(id) ON DELETE SET NULL -- Si el profesional se elimina, el turno se mantiene pero sin profesional asignado
);

-- Reactiva la verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;