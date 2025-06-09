-- Crear base de datos
CREATE DATABASE IF NOT EXISTS turismo_oviedo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE turismo_oviedo;

-- Tabla de tipos de recursos
CREATE TABLE tipos_recursos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    icono VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    fecha_nacimiento DATE,
    ciudad VARCHAR(100),
    codigo_postal VARCHAR(10),
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de recursos turísticos
CREATE TABLE recursos_turisticos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    tipo_recurso_id INT,
    ubicacion VARCHAR(300),
    direccion TEXT,
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    capacidad_maxima INT NOT NULL DEFAULT 1,
    precio DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    duracion_horas DECIMAL(4, 2) DEFAULT 1.00,
    requisitos TEXT,
    imagenes TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_recurso_id) REFERENCES tipos_recursos(id) ON DELETE SET NULL
);

-- Tabla de horarios de recursos
CREATE TABLE horarios_recursos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recurso_id INT NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    plazas_totales INT NOT NULL,
    plazas_disponibles INT NOT NULL,
    precio_especial DECIMAL(10, 2) DEFAULT NULL,
    observaciones TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recurso_id) REFERENCES recursos_turisticos(id) ON DELETE CASCADE
);

-- Tabla de reservas
CREATE TABLE reservas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    recurso_id INT NOT NULL,
    horario_id INT NOT NULL,
    numero_personas INT NOT NULL DEFAULT 1,
    precio_total DECIMAL(10, 2) NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'cancelada', 'completada') DEFAULT 'confirmada',
    comentarios TEXT,
    fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_cancelacion TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (recurso_id) REFERENCES recursos_turisticos(id) ON DELETE CASCADE,
    FOREIGN KEY (horario_id) REFERENCES horarios_recursos(id) ON DELETE CASCADE
);

-- Índices para optimizar consultas
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_recursos_tipo ON recursos_turisticos(tipo_recurso_id);
CREATE INDEX idx_recursos_activo ON recursos_turisticos(activo);
CREATE INDEX idx_horarios_recurso ON horarios_recursos(recurso_id);
CREATE INDEX idx_horarios_fecha ON horarios_recursos(fecha_inicio, fecha_fin);
CREATE INDEX idx_reservas_usuario ON reservas(usuario_id);
CREATE INDEX idx_reservas_estado ON reservas(estado);