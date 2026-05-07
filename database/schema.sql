-- ==============================================================================
-- Proyecto TFG: Sistema centralizado de automatización y gestión de espacios
-- Autor: Miguel Tárraga Martínez
-- Archivo: schema.sql (Base de datos principal)
-- ==============================================================================

-- Borrado de base de datos si existe
DROP DATABASE IF EXISTS teatro_control_db;

-- Crea la nueva base de datos
CREATE DATABASE teatro_control_db;
USE teatro_control_db;

-- --------------------------------------------------------
-- 1. TABLA DE ROL
-- Define los niveles de acceso al sistema (Q-SYS UCI)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO rol (nombre_rol) VALUES 
('Administrador'), 
('Técnico'), 
('Operario');

-- --------------------------------------------------------
-- 2. TABLA DE USUARIO
-- Almacena las credenciales encriptadas en SHA-256
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    clave_hash VARCHAR(255) NOT NULL, 
    id_rol INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. TABLA DE EQUIPO
-- Inventario de hardware integrado en la red
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS equipo (
    id_equipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_equipo VARCHAR(100) NOT NULL,
    ip_control VARCHAR(15) NOT NULL UNIQUE,
    protocolo VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO equipo (nombre_equipo, ip_control, protocolo) VALUES 
('Matriz Evertz', '192.168.10.15', 'SNMP'),
('Procesador Meyer Sound', '192.168.10.20', 'TCP'),
('Luces GrandMA3', '192.168.10.30', 'UDP/OSC'),
('Proyector Christie 4K13-HS', '192.168.10.40', 'TCP'),
('Relés Pantalla Stewart (QIO)', '192.168.10.41', 'GPIO');

-- --------------------------------------------------------
-- 4. TABLA DE RESERVA
-- Gestiona el bloqueo de la sala y automatización energética
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS reserva (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    inicio_datetime DATETIME NOT NULL,
    fin_datetime DATETIME NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. TABLA DE LOG_EVENTO
-- Registro de auditoría y fallos de hardware
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS log_evento (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    id_usuario INT NULL, 
    id_equipo INT NULL, 
    
    tipo_evento VARCHAR(50) NOT NULL, 
    resultado VARCHAR(20) NOT NULL,   
    accion_detalle VARCHAR(255) NOT NULL, 
    nivel_alerta INT NOT NULL CHECK (nivel_alerta BETWEEN 1 AND 5),
    
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (id_equipo) REFERENCES equipo(id_equipo) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;