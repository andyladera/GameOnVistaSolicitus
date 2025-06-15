-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.32-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para gameon
CREATE DATABASE IF NOT EXISTS `gameon` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `gameon`;

-- Volcando estructura para tabla gameon.amistades
CREATE TABLE IF NOT EXISTS `amistades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_solicitante_id` int(11) NOT NULL,
  `usuario_receptor_id` int(11) NOT NULL,
  `estado` enum('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_respuesta` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `amistad_unica` (`usuario_solicitante_id`,`usuario_receptor_id`),
  KEY `usuario_receptor_id` (`usuario_receptor_id`),
  CONSTRAINT `amistades_ibfk_1` FOREIGN KEY (`usuario_solicitante_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `amistades_ibfk_2` FOREIGN KEY (`usuario_receptor_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.amistades: ~2 rows (aproximadamente)
INSERT INTO `amistades` (`id`, `usuario_solicitante_id`, `usuario_receptor_id`, `estado`, `fecha_solicitud`, `fecha_respuesta`) VALUES
	(1, 2, 3, 'aceptada', '2025-06-02 19:24:11', '2025-06-02 19:24:13'),
	(3, 4, 2, 'aceptada', '2025-06-02 20:01:28', '2025-06-02 20:03:18');

-- Volcando estructura para tabla gameon.deportes
CREATE TABLE IF NOT EXISTS `deportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.deportes: ~3 rows (aproximadamente)
INSERT INTO `deportes` (`id`, `nombre`) VALUES
	(1, 'futbol'),
	(2, 'voley'),
	(3, 'basquet');

-- Volcando estructura para tabla gameon.disponibilidad
CREATE TABLE IF NOT EXISTS `disponibilidad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dia` varchar(15) NOT NULL,
  `franja_horaria` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.disponibilidad: ~0 rows (aproximadamente)

-- Volcando estructura para tabla gameon.equipos
CREATE TABLE IF NOT EXISTS `equipos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `deporte_id` int(11) NOT NULL,
  `creador_id` int(11) NOT NULL,
  `limite_miembros` int(11) NOT NULL DEFAULT 10,
  `privado` tinyint(1) NOT NULL DEFAULT 0,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `deporte_id` (`deporte_id`),
  KEY `creador_id` (`creador_id`),
  CONSTRAINT `equipos_ibfk_1` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipos_ibfk_2` FOREIGN KEY (`creador_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.equipos: ~3 rows (aproximadamente)
INSERT INTO `equipos` (`id`, `nombre`, `descripcion`, `deporte_id`, `creador_id`, `limite_miembros`, `privado`, `estado`, `creado_en`) VALUES
	(1, 'FC Vodka Juniors', 'La nueva generación de apasionados por el deporte y la chela', 1, 2, 10, 1, 1, '2025-06-02 19:38:20'),
	(2, 'Las Mariposas', 'Somos mariposas en la cancha de voley xd', 2, 2, 10, 1, 1, '2025-06-02 21:34:35'),
	(3, 'Los Negros James', 'Somos los mas negros de todo Tacna, tiemblen ante nuestro Bascket de negros', 3, 2, 10, 1, 1, '2025-06-03 21:53:45'),
	(4, 'Los cojos de EPIS', 'Somos mariposas', 1, 2, 10, 0, 1, '2025-06-06 00:03:00'),
	(5, 'mongosdeverdad', 'dffdfdf', 1, 2, 10, 0, 1, '2025-06-06 00:21:20');

-- Volcando estructura para tabla gameon.equipo_miembros
CREATE TABLE IF NOT EXISTS `equipo_miembros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipo_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `rol` enum('creador','administrador','miembro') NOT NULL DEFAULT 'miembro',
  `fecha_union` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `equipo_usuario_unico` (`equipo_id`,`usuario_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `equipo_miembros_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipo_miembros_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.equipo_miembros: ~7 rows (aproximadamente)
INSERT INTO `equipo_miembros` (`id`, `equipo_id`, `usuario_id`, `rol`, `fecha_union`) VALUES
	(1, 1, 2, 'creador', '2025-06-02 19:38:20'),
	(2, 2, 2, 'creador', '2025-06-02 21:34:35'),
	(3, 1, 3, 'miembro', '2025-06-03 21:31:29'),
	(4, 2, 4, 'miembro', '2025-06-03 21:31:35'),
	(5, 1, 4, 'miembro', '2025-06-03 21:33:52'),
	(6, 3, 2, 'creador', '2025-06-03 21:53:45'),
	(7, 3, 3, 'miembro', '2025-06-04 21:32:01'),
	(8, 4, 2, 'creador', '2025-06-06 00:03:00'),
	(9, 4, 4, 'miembro', '2025-06-06 00:03:24'),
	(10, 5, 2, 'creador', '2025-06-06 00:21:20'),
	(11, 5, 3, 'miembro', '2025-06-06 00:21:42'),
	(12, 5, 4, 'miembro', '2025-06-06 00:21:53');

-- Volcando estructura para tabla gameon.horarios_atencion
CREATE TABLE IF NOT EXISTS `horarios_atencion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institucion_deportiva_id` int(11) NOT NULL,
  `dia` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `hora_apertura` time NOT NULL,
  `hora_cierre` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `institucion_deportiva_id` (`institucion_deportiva_id`),
  CONSTRAINT `horarios_atencion_ibfk_1` FOREIGN KEY (`institucion_deportiva_id`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.horarios_atencion: ~21 rows (aproximadamente)
INSERT INTO `horarios_atencion` (`id`, `institucion_deportiva_id`, `dia`, `hora_apertura`, `hora_cierre`) VALUES
	(1, 1, 'Lunes', '07:00:00', '22:00:00'),
	(2, 1, 'Martes', '07:00:00', '22:00:00'),
	(3, 1, 'Miercoles', '07:00:00', '22:00:00'),
	(4, 1, 'Jueves', '07:00:00', '22:00:00'),
	(5, 1, 'Viernes', '07:00:00', '23:00:00'),
	(6, 1, 'Sabado', '08:00:00', '23:00:00'),
	(7, 1, 'Domingo', '08:00:00', '20:00:00'),
	(8, 2, 'Lunes', '06:00:00', '21:00:00'),
	(9, 2, 'Martes', '06:00:00', '21:00:00'),
	(10, 2, 'Miercoles', '06:00:00', '21:00:00'),
	(11, 2, 'Jueves', '06:00:00', '21:00:00'),
	(12, 2, 'Viernes', '06:00:00', '21:00:00'),
	(13, 2, 'Sabado', '08:00:00', '22:00:00'),
	(14, 2, 'Domingo', '08:00:00', '19:00:00'),
	(15, 3, 'Lunes', '08:00:00', '22:00:00'),
	(16, 3, 'Martes', '08:00:00', '22:00:00'),
	(17, 3, 'Miercoles', '08:00:00', '22:00:00'),
	(18, 3, 'Jueves', '08:00:00', '22:00:00'),
	(19, 3, 'Viernes', '08:00:00', '23:30:00'),
	(20, 3, 'Sabado', '09:00:00', '23:30:00'),
	(21, 3, 'Domingo', '09:00:00', '21:00:00');

-- Volcando estructura para tabla gameon.instalaciones_ocupaciones
CREATE TABLE IF NOT EXISTS `instalaciones_ocupaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institucion_deportiva_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `tipo_ocupacion` enum('reserva_individual','partido_equipos','torneo','mantenimiento','evento_especial') NOT NULL,
  `estado` enum('reservado','confirmado','en_curso','finalizado','cancelado') DEFAULT 'reservado',
  `usuario_reserva_id` int(11) DEFAULT NULL,
  `equipo_local_id` int(11) DEFAULT NULL,
  `equipo_visitante_id` int(11) DEFAULT NULL,
  `torneo_partido_id` int(11) DEFAULT NULL,
  `torneo_id` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT 0.00,
  `creado_por_usuario_id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_institucion_fecha` (`institucion_deportiva_id`,`fecha`),
  KEY `idx_fecha_hora` (`fecha`,`hora_inicio`,`hora_fin`),
  KEY `idx_tipo_estado` (`tipo_ocupacion`,`estado`),
  KEY `ocupaciones_ibfk_2` (`usuario_reserva_id`),
  KEY `ocupaciones_ibfk_3` (`equipo_local_id`),
  KEY `ocupaciones_ibfk_4` (`equipo_visitante_id`),
  KEY `ocupaciones_ibfk_5` (`torneo_partido_id`),
  KEY `ocupaciones_ibfk_6` (`torneo_id`),
  KEY `ocupaciones_ibfk_7` (`creado_por_usuario_id`),
  CONSTRAINT `ocupaciones_ibfk_1` FOREIGN KEY (`institucion_deportiva_id`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ocupaciones_ibfk_2` FOREIGN KEY (`usuario_reserva_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ocupaciones_ibfk_3` FOREIGN KEY (`equipo_local_id`) REFERENCES `equipos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ocupaciones_ibfk_4` FOREIGN KEY (`equipo_visitante_id`) REFERENCES `equipos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ocupaciones_ibfk_5` FOREIGN KEY (`torneo_partido_id`) REFERENCES `torneos_partidos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ocupaciones_ibfk_6` FOREIGN KEY (`torneo_id`) REFERENCES `torneos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ocupaciones_ibfk_7` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios_deportistas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.instalaciones_ocupaciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla gameon.instituciones_deportes
CREATE TABLE IF NOT EXISTS `instituciones_deportes` (
  `institucion_deportiva_id` int(11) NOT NULL,
  `deporte_id` int(11) NOT NULL,
  PRIMARY KEY (`institucion_deportiva_id`,`deporte_id`),
  KEY `deporte_id` (`deporte_id`),
  CONSTRAINT `instituciones_deportes_ibfk_1` FOREIGN KEY (`institucion_deportiva_id`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `instituciones_deportes_ibfk_2` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.instituciones_deportes: ~9 rows (aproximadamente)
INSERT INTO `instituciones_deportes` (`institucion_deportiva_id`, `deporte_id`) VALUES
	(1, 1),
	(2, 1),
	(2, 2),
	(2, 3),
	(3, 1),
	(3, 3),
	(4, 1),
	(4, 2),
	(4, 3);

-- Volcando estructura para tabla gameon.instituciones_deportivas
CREATE TABLE IF NOT EXISTS `instituciones_deportivas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_instalacion_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `tarifa` decimal(10,2) NOT NULL,
  `calificacion` decimal(3,2) DEFAULT 0.00,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_instalacion_id` (`usuario_instalacion_id`),
  CONSTRAINT `instituciones_deportivas_ibfk_1` FOREIGN KEY (`usuario_instalacion_id`) REFERENCES `usuarios_instalaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.instituciones_deportivas: ~4 rows (aproximadamente)
INSERT INTO `instituciones_deportivas` (`id`, `usuario_instalacion_id`, `nombre`, `direccion`, `latitud`, `longitud`, `tarifa`, `calificacion`, `telefono`, `email`, `descripcion`, `estado`, `creado_en`) VALUES
	(1, 1, 'Top Gol Tacna', 'Av. Bolognesi 1234, Tacna', -18.00660000, -70.24630000, 50.00, 4.50, '052123456', 'contacto@topgoltacna.com', 'Canchas de fútbol con césped sintético de primera calidad', 1, '2025-05-21 19:15:21'),
	(2, 1, 'Complejo Deportivo Municipal', 'Calle Patricio Meléndez 500, Tacna', -18.01220000, -70.25360000, 35.00, 4.20, '052987654', 'deportes@munitacna.gob.pe', 'Complejo deportivo municipal con múltiples canchas', 1, '2025-05-21 19:15:21'),
	(3, 1, 'Club Deportivo Tacna', 'Av. Cusco 750, Tacna', -18.00550000, -70.23980000, 65.00, 4.80, '052456789', 'info@clubdeportivotacna.com', 'Club exclusivo con instalaciones de primer nivel', 1, '2025-05-21 19:15:21'),
	(4, 2, 'IPD Tacna - Complejo Deportivo', 'Av. Gregorio Albarracín s/n, Tacna', -18.01500000, -70.25800000, 0.00, 5.00, '052-427070', 'ipd.tacna@ipd.gob.pe', 'Complejo deportivo del Instituto Peruano del Deporte', 1, '2025-06-04 20:03:09');

-- Volcando estructura para tabla gameon.reservas
CREATE TABLE IF NOT EXISTS `reservas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `deporte_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` varchar(20) DEFAULT 'pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_institucion` (`id_institucion`),
  KEY `deporte_id` (`deporte_id`),
  KEY `idx_fecha` (`fecha`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_ibfk_3` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.reservas: ~3 rows (aproximadamente)
INSERT INTO `reservas` (`id`, `id_usuario`, `id_institucion`, `deporte_id`, `fecha`, `hora_inicio`, `hora_fin`, `estado`, `creado_en`) VALUES
	(1, 2, 1, 1, '2025-06-15', '18:00:00', '20:00:00', 'confirmada', '2025-06-05 22:08:12'),
	(2, 2, 2, 2, '2025-06-20', '16:00:00', '18:00:00', 'confirmada', '2025-06-05 22:08:12'),
	(3, 2, 3, 1, '2025-06-25', '19:00:00', '21:00:00', 'pendiente', '2025-06-05 22:08:12');

-- Volcando estructura para tabla gameon.torneos
CREATE TABLE IF NOT EXISTS `torneos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `deporte_id` int(11) NOT NULL,
  `organizador_tipo` enum('institucion','ipd') NOT NULL,
  `organizador_id` int(11) NOT NULL,
  `institucion_sede_id` int(11) NOT NULL,
  `max_equipos` int(11) NOT NULL DEFAULT 16,
  `equipos_inscritos` int(11) NOT NULL DEFAULT 0,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_inscripcion_inicio` date NOT NULL,
  `fecha_inscripcion_fin` date NOT NULL,
  `estado` enum('proximo','inscripciones_abiertas','inscripciones_cerradas','activo','finalizado','cancelado') NOT NULL DEFAULT 'proximo',
  `modalidad` enum('eliminacion_simple','eliminacion_doble','todos_contra_todos','grupos_eliminatoria') NOT NULL DEFAULT 'eliminacion_simple',
  `premio_descripcion` text DEFAULT NULL,
  `costo_inscripcion` decimal(10,2) DEFAULT 0.00,
  `imagen_torneo` varchar(255) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_organizador` (`organizador_tipo`,`organizador_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_deporte` (`deporte_id`),
  KEY `idx_fechas` (`fecha_inicio`,`fecha_fin`),
  KEY `idx_sede` (`institucion_sede_id`),
  CONSTRAINT `torneos_ibfk_1` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `torneos_ibfk_2` FOREIGN KEY (`institucion_sede_id`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.torneos: ~3 rows (aproximadamente)
INSERT INTO `torneos` (`id`, `nombre`, `descripcion`, `deporte_id`, `organizador_tipo`, `organizador_id`, `institucion_sede_id`, `max_equipos`, `equipos_inscritos`, `fecha_inicio`, `fecha_fin`, `fecha_inscripcion_inicio`, `fecha_inscripcion_fin`, `estado`, `modalidad`, `premio_descripcion`, `costo_inscripcion`, `imagen_torneo`, `creado_en`, `actualizado_en`) VALUES
	(1, 'Copa Top Gol 2025', 'Torneo de fútbol amateur en canchas de césped sintético', 1, 'institucion', 1, 1, 16, 0, '2025-07-15', '2025-07-28', '2025-06-15', '2025-07-10', 'inscripciones_abiertas', 'eliminacion_simple', 'Trofeo y medallas para los 3 primeros lugares', 150.00, 'futbol1.jpg', '2025-06-04 20:03:09', '2025-06-04 20:44:48'),
	(2, 'Torneo IPD Voley Femenino', 'Campeonato oficial de voleibol femenino organizado por IPD', 2, 'ipd', 4, 4, 8, 0, '2025-08-01', '2025-08-15', '2025-07-01', '2025-07-25', 'proximo', 'todos_contra_todos', 'Copa IPD y reconocimientos oficiales', 0.00, 'voley1.jpg', '2025-06-04 20:03:09', '2025-06-04 20:44:53'),
	(3, 'Liga Basket Municipal', 'Torneo de básquet amateur en el complejo municipal', 3, 'institucion', 2, 2, 12, 0, '2025-06-20', '2025-07-05', '2025-06-01', '2025-06-15', 'inscripciones_abiertas', 'grupos_eliminatoria', 'Trofeos y medallas + entradas al cine', 100.00, 'basket1.jpg', '2025-06-04 20:03:09', '2025-06-04 20:44:58');

-- Volcando estructura para tabla gameon.torneos_equipos
CREATE TABLE IF NOT EXISTS `torneos_equipos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torneo_id` int(11) NOT NULL,
  `equipo_id` int(11) NOT NULL,
  `inscrito_por_usuario_id` int(11) NOT NULL,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_inscripcion` enum('pendiente','confirmada','rechazada','retirado') DEFAULT 'pendiente',
  `comentarios` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `equipo_torneo_unico` (`torneo_id`,`equipo_id`),
  KEY `idx_torneo` (`torneo_id`),
  KEY `idx_equipo` (`equipo_id`),
  KEY `torneos_equipos_ibfk_3` (`inscrito_por_usuario_id`),
  CONSTRAINT `torneos_equipos_ibfk_1` FOREIGN KEY (`torneo_id`) REFERENCES `torneos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `torneos_equipos_ibfk_2` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `torneos_equipos_ibfk_3` FOREIGN KEY (`inscrito_por_usuario_id`) REFERENCES `usuarios_deportistas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.torneos_equipos: ~2 rows (aproximadamente)
INSERT INTO `torneos_equipos` (`id`, `torneo_id`, `equipo_id`, `inscrito_por_usuario_id`, `fecha_inscripcion`, `estado_inscripcion`, `comentarios`) VALUES
	(1, 1, 1, 2, '2025-06-04 22:00:14', 'confirmada', NULL),
	(2, 2, 2, 2, '2025-06-04 22:00:14', 'confirmada', NULL);

-- Volcando estructura para tabla gameon.torneos_estadisticas
CREATE TABLE IF NOT EXISTS `torneos_estadisticas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torneo_id` int(11) NOT NULL,
  `equipo_id` int(11) NOT NULL,
  `partidos_jugados` int(11) DEFAULT 0,
  `partidos_ganados` int(11) DEFAULT 0,
  `partidos_perdidos` int(11) DEFAULT 0,
  `partidos_empatados` int(11) DEFAULT 0,
  `goles_favor` int(11) DEFAULT 0,
  `goles_contra` int(11) DEFAULT 0,
  `puntos` int(11) DEFAULT 0,
  `posicion_final` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `torneo_equipo_stats` (`torneo_id`,`equipo_id`),
  KEY `torneos_estadisticas_ibfk_2` (`equipo_id`),
  CONSTRAINT `torneos_estadisticas_ibfk_1` FOREIGN KEY (`torneo_id`) REFERENCES `torneos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `torneos_estadisticas_ibfk_2` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.torneos_estadisticas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla gameon.torneos_partidos
CREATE TABLE IF NOT EXISTS `torneos_partidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torneo_id` int(11) NOT NULL,
  `equipo_local_id` int(11) NOT NULL,
  `equipo_visitante_id` int(11) NOT NULL,
  `fase` enum('grupos','octavos','cuartos','semifinal','final','tercer_lugar') NOT NULL,
  `numero_grupo` int(11) DEFAULT NULL,
  `fecha_partido` datetime NOT NULL,
  `resultado_local` int(11) DEFAULT NULL,
  `resultado_visitante` int(11) DEFAULT NULL,
  `equipo_ganador_id` int(11) DEFAULT NULL,
  `estado_partido` enum('programado','en_curso','finalizado','suspendido','cancelado') DEFAULT 'programado',
  `observaciones` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_torneo_fase` (`torneo_id`,`fase`),
  KEY `idx_fecha` (`fecha_partido`),
  KEY `torneos_partidos_ibfk_2` (`equipo_local_id`),
  KEY `torneos_partidos_ibfk_3` (`equipo_visitante_id`),
  KEY `torneos_partidos_ibfk_4` (`equipo_ganador_id`),
  CONSTRAINT `torneos_partidos_ibfk_1` FOREIGN KEY (`torneo_id`) REFERENCES `torneos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `torneos_partidos_ibfk_2` FOREIGN KEY (`equipo_local_id`) REFERENCES `equipos` (`id`),
  CONSTRAINT `torneos_partidos_ibfk_3` FOREIGN KEY (`equipo_visitante_id`) REFERENCES `equipos` (`id`),
  CONSTRAINT `torneos_partidos_ibfk_4` FOREIGN KEY (`equipo_ganador_id`) REFERENCES `equipos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.torneos_partidos: ~3 rows (aproximadamente)
INSERT INTO `torneos_partidos` (`id`, `torneo_id`, `equipo_local_id`, `equipo_visitante_id`, `fase`, `numero_grupo`, `fecha_partido`, `resultado_local`, `resultado_visitante`, `equipo_ganador_id`, `estado_partido`, `observaciones`, `creado_en`) VALUES
	(5, 1, 1, 3, 'grupos', NULL, '2025-06-18 16:00:00', NULL, NULL, NULL, 'programado', NULL, '2025-06-04 22:12:34'),
	(6, 1, 3, 1, 'grupos', NULL, '2025-06-25 20:00:00', NULL, NULL, NULL, 'programado', NULL, '2025-06-04 22:12:34'),
	(7, 3, 3, 2, 'grupos', NULL, '2025-06-28 19:00:00', NULL, NULL, NULL, 'programado', NULL, '2025-06-04 22:12:34');

-- Volcando estructura para tabla gameon.usuarios_deportes
CREATE TABLE IF NOT EXISTS `usuarios_deportes` (
  `usuario_id` int(11) NOT NULL,
  `deporte_id` int(11) NOT NULL,
  PRIMARY KEY (`usuario_id`,`deporte_id`),
  KEY `deporte_id` (`deporte_id`),
  CONSTRAINT `usuarios_deportes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuarios_deportes_ibfk_2` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.usuarios_deportes: ~0 rows (aproximadamente)
INSERT INTO `usuarios_deportes` (`usuario_id`, `deporte_id`) VALUES
	(2, 3);

-- Volcando estructura para tabla gameon.usuarios_deportistas
CREATE TABLE IF NOT EXISTS `usuarios_deportistas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL DEFAULT '0',
  `apellidos` varchar(100) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL DEFAULT '0',
  `telefono` varchar(20) NOT NULL DEFAULT '0',
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('Masculino','Feminino','Otro') NOT NULL,
  `nivel_habilidad` enum('Principiante','Intermedio','Avanzado') NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.usuarios_deportistas: ~3 rows (aproximadamente)
INSERT INTO `usuarios_deportistas` (`id`, `nombre`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `genero`, `nivel_habilidad`, `username`, `password`, `estado`, `creado_en`) VALUES
	(2, 'Sebastian', 'Fuentes Avalos', 'fuentessebastiansa4s@gmail.com', '946143071', '2005-01-18', 'Masculino', 'Intermedio', 'Chevi10', '$2y$10$IJrd1jNkOJNb73BS68/c.OeQG2R7NQmcuNoktqQINYtBYo1C4moOG', 1, '2025-05-19 19:10:38'),
	(3, 'Gabriela', 'Gutierrez Mamane', 'gabrielaga@gmail.com', '946143071', '2002-11-03', 'Feminino', 'Principiante', 'GabyGol', '$2y$10$DetTzM9npZHxn9dufxtAoekAOZBzfmlQ568JEkpg4wIc3VrLJ6XEO', 1, '2025-05-19 20:24:19'),
	(4, 'Victor', 'Cruz Mamani', 'victor@gmail.com', '946143072', '2001-06-02', 'Masculino', 'Principiante', 'Chamo', '$2y$10$DetTzM9npZHxn9dufxtAoekAOZBzfmlQ568JEkpg4wIc3VrLJ6XEO', 1, '2025-06-02 19:48:23');

-- Volcando estructura para tabla gameon.usuarios_disponibilidad
CREATE TABLE IF NOT EXISTS `usuarios_disponibilidad` (
  `usuario_id` int(11) NOT NULL,
  `disponibilidad_id` int(11) NOT NULL,
  PRIMARY KEY (`usuario_id`,`disponibilidad_id`),
  KEY `disponibilidad_id` (`disponibilidad_id`),
  CONSTRAINT `usuarios_disponibilidad_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuarios_disponibilidad_ibfk_2` FOREIGN KEY (`disponibilidad_id`) REFERENCES `disponibilidad` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.usuarios_disponibilidad: ~0 rows (aproximadamente)

-- Volcando estructura para tabla gameon.usuarios_instalaciones
CREATE TABLE IF NOT EXISTS `usuarios_instalaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo_usuario` enum('privado','ipd') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.usuarios_instalaciones: ~2 rows (aproximadamente)
INSERT INTO `usuarios_instalaciones` (`id`, `username`, `password`, `estado`, `created_at`, `tipo_usuario`) VALUES
	(1, 'topgol', '$2y$10$DetTzM9npZHxn9dufxtAoekAOZBzfmlQ568JEkpg4wIc3VrLJ6XEO', 1, '2025-05-19 17:20:31', 'privado'),
	(2, 'ipd_tacna', '$2y$10$DetTzM9npZHxn9dufxtAoekAOZBzfmlQ568JEkpg4wIc3VrLJ6XEO', 1, '2025-06-04 20:03:09', 'ipd');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
