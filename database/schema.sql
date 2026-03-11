-- ==============================================================================
-- Proyecto TFG: Sistema centralizado de automatización y gestión de espacios
-- Autor: Miguel Tárraga Martínez
-- Archivo: schema.sql (Base de datos principal)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS teatro_control_db;
USE teatro_control_db;

-- --------------------------------------------------------
-- 1. TABLA DE ROLES
-- Define los niveles de acceso al sistema (Q-SYS UCI)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar roles por defecto
INSERT INTO roles (nombre_rol) VALUES 
('Administrador'), 
('Técnico'), 
('Operario');

-- --------------------------------------------------------
-- 2. TABLA DE USUARIOS
-- Almacena las credenciales del personal del buque H6354
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- Hash de la contraseña (ej. SHA-256)
    id_rol INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar usuario administrador por defecto (Pass genérica para pruebas: 1234)
INSERT INTO usuarios (username, password_hash, id_rol) VALUES 
('admin_teatro', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 1);

-- --------------------------------------------------------
-- 3. TABLA DE EQUIPOS (Actualizada para el Modo Cine)
-- Almacena el inventario del hardware crítico controlado
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS equipos (
    id_equipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_equipo VARCHAR(100) NOT NULL,
    ip_control VARCHAR(15) NOT NULL,
    protocolo VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertamos los equipos reales del proyecto para que Lua los encuentre
INSERT INTO equipos (nombre_equipo, ip_control, protocolo) VALUES 
('Matriz Evertz Magnum', '192.168.10.10', 'TCP'),
('Procesador Meyer Galileo', '192.168.10.20', 'SNMP'),
('Controlador ETC Paradigm', '192.168.10.30', 'UDP'),
('Proyector Christie 4K13-HS', '192.168.10.40', 'TCP'),
('Relés Pantalla Stewart (QIO)', '192.168.10.41', 'GPIO');

-- --------------------------------------------------------
-- 4. TABLA DE RESERVAS
-- Gestiona el bloqueo de la sala y automatización energética
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado ENUM('Activa', 'Cancelada', 'Finalizada') DEFAULT 'Activa',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. TABLA DE LOGS_EVENTOS
-- Registro de auditoría y fallos de hardware (SNMP/TCP/UDP)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS logs_eventos (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NULL, 
    id_equipo INT NULL, -- Añadido para que coincida con la memoria
    accion_detalle VARCHAR(255) NOT NULL, 
    nivel_alerta INT NOT NULL CHECK (nivel_alerta IN (1, 2, 3)), 
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;