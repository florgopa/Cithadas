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
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(255),
    descripcion TEXT,
    email VARCHAR (100) NOT NULL UNIQUE, 
    telefono VARCHAR(20)
);

-- Tabla de servicios
CREATE TABLE servicio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_negocio INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    categoria ENUM('peluquería','depilación','tatuajes','cosmetologia facial', 'estetica corporal', 'aparatologia', 'piercings') NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2),
    duracion_min INT,
    FOREIGN KEY (id_negocio) REFERENCES negocios(id) ON DELETE CASCADE
);

-- Tabla de turnos
CREATE TABLE turno (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_servicio INT NOT NULL,
    fecha_turno DATE NOT NULL,
    hora_turno TIME NOT NULL,
    estado ENUM('pendiente','confirmado','cancelado') DEFAULT 'pendiente',
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (id_servicio) REFERENCES servicio(id) ON DELETE CASCADE
);