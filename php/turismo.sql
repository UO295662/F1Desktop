-- Base de datos para Central de Reservas Turísticas de Oviedo
-- Usuario: DBUSER2025, Password: DBPWD2025

CREATE DATABASE IF NOT EXISTS turismo_oviedo;
USE turismo_oviedo;

-- --------------------------------------------------------
-- Tabla de tipos de recursos turísticos
-- --------------------------------------------------------
CREATE TABLE `tipos_recursos` (
  `id_tipo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_tipo` varchar(100) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla de recursos turísticos
-- --------------------------------------------------------
CREATE TABLE `recursos_turisticos` (
  `id_recurso` int(11) NOT NULL AUTO_INCREMENT,
  `id_tipo` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `plazas_disponibles` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_recurso`),
  KEY `id_tipo` (`id_tipo`),
  CONSTRAINT `recursos_ibfk_1` FOREIGN KEY (`id_tipo`) REFERENCES `tipos_recursos` (`id_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla de usuarios
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL UNIQUE,
  `telefono` varchar(20),
  `fecha_nacimiento` date,
  `ciudad` varchar(100),
  `codigo_postal` varchar(10),
  `password_hash` varchar(255) NOT NULL,
  `fecha_registro` timestamp DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla de reservas
-- --------------------------------------------------------
CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_recurso` int(11) NOT NULL,
  `numero_personas` int(11) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `fecha_reserva` timestamp DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
  `observaciones` text,
  PRIMARY KEY (`id_reserva`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_recurso` (`id_recurso`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_recurso`) REFERENCES `recursos_turisticos` (`id_recurso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla de pagos
-- --------------------------------------------------------
CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_reserva` int(11) NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `metodo_pago` enum('tarjeta','transferencia','efectivo','paypal') NOT NULL,
  `fecha_pago` timestamp DEFAULT CURRENT_TIMESTAMP,
  `estado_pago` enum('pendiente','completado','fallido','reembolsado') DEFAULT 'pendiente',
  `referencia_externa` varchar(100),
  PRIMARY KEY (`id_pago`),
  KEY `id_reserva` (`id_reserva`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Insertar datos iniciales
-- --------------------------------------------------------

-- Tipos de recursos
INSERT INTO `tipos_recursos` (`nombre_tipo`, `descripcion`) VALUES
('Museo', 'Espacios culturales y museos de la ciudad'),
('Ruta Turística', 'Recorridos guiados por la ciudad y alrededores'),
('Restaurante', 'Experiencias gastronómicas y cenas especiales'),
('Hotel', 'Alojamientos y estancias especiales'),
('Actividad Cultural', 'Eventos, espectáculos y actividades culturales');

-- Recursos turísticos
INSERT INTO `recursos_turisticos` (`id_tipo`, `nombre`, `descripcion`, `ubicacion`, `precio`, `plazas_disponibles`, `fecha_inicio`, `fecha_fin`) VALUES
(1, 'Museo de Bellas Artes de Asturias', 'Visita guiada al museo con las mejores obras de arte asturiano', 'Calle Santa Ana, 1', 12.50, 25, '2025-06-06 10:00:00', '2025-12-31 18:00:00'),
(1, 'Cámara Santa de la Catedral', 'Tour exclusivo por la Cámara Santa, Patrimonio de la Humanidad', 'Plaza Alfonso II el Casto', 8.00, 15, '2025-06-06 09:00:00', '2025-12-31 19:00:00'),
(2, 'Ruta de los Monumentos Prerrománicos', 'Recorrido por Santa María del Naranco y San Miguel de Lillo', 'Monte Naranco', 15.00, 20, '2025-06-06 09:30:00', '2025-12-31 17:30:00'),
(2, 'Ruta del Casco Histórico', 'Paseo guiado por el centro histórico de Oviedo', 'Plaza de la Constitución', 10.00, 30, '2025-06-06 10:00:00', '2025-12-31 20:00:00'),
(3, 'Cena Asturiana Tradicional', 'Experiencia gastronómica con fabada, cachopo y sidra', 'Calle Gascona, 15', 35.00, 12, '2025-06-06 20:00:00', '2025-12-31 23:00:00'),
(4, 'Hotel de la Reconquista - Noche Especial', 'Estancia de lujo en el hotel más emblemático', 'Calle Gil de Jaz, 16', 180.00, 5, '2025-06-06 15:00:00', '2025-12-31 12:00:00'),
(5, 'Concierto en el Teatro Campoamor', 'Espectáculo musical en el teatro más importante de Asturias', 'Calle Pelayo, s/n', 25.00, 50, '2025-06-15 20:30:00', '2025-06-15 23:00:00');

-- Usuario de ejemplo
INSERT INTO `usuarios` (`nombre`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `ciudad`, `codigo_postal`, `password_hash`) VALUES
('Juan', 'García López', 'juan.garcia@email.com', '985123456', '1985-03-15', 'Oviedo', '33001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('María', 'Fernández Suárez', 'maria.fernandez@email.com', '985654321', '1990-07-22', 'Gijón', '33201', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Reservas de ejemplo
INSERT INTO `reservas` (`id_usuario`, `id_recurso`, `numero_personas`, `precio_total`, `estado`) VALUES
(1, 1, 2, 25.00, 'confirmada'),
(2, 3, 4, 60.00, 'confirmada');

-- Pagos de ejemplo
INSERT INTO `pagos` (`id_reserva`, `importe`, `metodo_pago`, `estado_pago`) VALUES
(1, 25.00, 'tarjeta', 'completado'),
(2, 60.00, 'transferencia', 'completado');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
