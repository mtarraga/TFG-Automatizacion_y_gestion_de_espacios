-- ==============================================================================
-- Proyecto TFG: Sistema centralizado de automatización y gestión de espacios
-- Autor: Miguel Tárraga Martínez
-- Archivo: schema.sql (Base de datos principal - Versión con 6 usuarios)
-- ==============================================================================

-- Borrado de base de datos si existe
DROP DATABASE IF EXISTS teatro_control_db;

-- Crea la nueva base de datos
CREATE DATABASE teatro_control_db;
USE teatro_control_db;

-- --------------------------------------------------------
-- 1. TABLA DE ROLES
-- Define los niveles de acceso al sistema (Q-SYS UCI)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (nombre_rol) VALUES 
('Administrador'), 
('Técnico'), 
('Operario');

-- --------------------------------------------------------
-- 2. TABLA DE USUARIOS
-- Almacena las credenciales encriptadas en SHA-256
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    clave_hash VARCHAR(255) NOT NULL, 
    id_rol INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertamos 6 usuarios de prueba usando la función de cifrado SHA2 de MySQL
INSERT INTO usuarios (nombre_usuario, clave_hash, id_rol) VALUES 
('admin_principal', SHA2('1234', 256), 1),     -- PIN: 1234 (Administrador)
('admin_sistemas', SHA2('9999', 256), 1),      -- PIN: 9999 (Administrador)
('tecnico_av', SHA2('5678', 256), 2),          -- PIN: 5678 (Técnico)
('tecnico_luces', SHA2('4321', 256), 2),       -- PIN: 4321 (Técnico)
('operario_mañana', SHA2('1111', 256), 3),     -- PIN: 1111 (Operario)
('operario_tarde', SHA2('2222', 256), 3);      -- PIN: 2222 (Operario)

-- --------------------------------------------------------
-- 3. TABLA DE EQUIPOS
-- Almacena el inventario del hardware crítico controlado
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS equipos (
    id_equipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_equipo VARCHAR(100) NOT NULL,
    ip_control VARCHAR(15) NOT NULL,
    protocolo VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    inicio_datetime DATETIME NOT NULL,
    fin_datetime DATETIME NOT NULL,
    estado ENUM('Activa', 'Cancelada', 'Finalizada') DEFAULT 'Activa',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. TABLA DE LOGS_EVENTOS
-- Registro de auditoría y fallos de hardware
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS logs_eventos (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    id_usuario INT NULL, 
    id_equipo INT NULL, 
    
    tipo_evento VARCHAR(50) NOT NULL, 
    resultado VARCHAR(20) NOT NULL,   
    accion_detalle VARCHAR(255) NOT NULL, 
    nivel_alerta INT NOT NULL CHECK (nivel_alerta IN (1, 2, 3)), 
    
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;