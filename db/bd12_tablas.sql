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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla gameon.areas_deportivas
CREATE TABLE IF NOT EXISTS `areas_deportivas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institucion_deportiva_id` int(11) NOT NULL,
  `deporte_id` int(11) NOT NULL,
  `nombre_area` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `capacidad_jugadores` int(11) DEFAULT NULL,
  `tarifa_por_hora` decimal(10,2) NOT NULL,
  `estado` enum('activa','mantenimiento','inactiva') DEFAULT 'activa',
  `imagen_area` varchar(255) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `institucion_deportiva_id` (`institucion_deportiva_id`),
  KEY `deporte_id` (`deporte_id`),
  CONSTRAINT `areas_deportivas_ibfk_1` FOREIGN KEY (`institucion_deportiva_id`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `areas_deportivas_ibfk_2` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla gameon.areas_horarios
CREATE TABLE IF NOT EXISTS `areas_horarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area_deportiva_id` int(11) NOT NULL,
  `dia` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `hora_apertura` time NOT NULL,
  `hora_cierre` time NOT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `area_deportiva_id` (`area_deportiva_id`),
  CONSTRAINT `areas_horarios_ibfk_1` FOREIGN KEY (`area_deportiva_id`) REFERENCES `areas_deportivas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla gameon.deportes
CREATE TABLE IF NOT EXISTS `deportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

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

-- La exportación de datos fue deseleccionada.

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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla gameon.instituciones_deportes
CREATE TABLE IF NOT EXISTS `instituciones_deportes` (
  `institucion_deportiva_id` int(11) NOT NULL,
  `deporte_id` int(11) NOT NULL,
  PRIMARY KEY (`institucion_deportiva_id`,`deporte_id`),
  KEY `deporte_id` (`deporte_id`),
  CONSTRAINT `instituciones_deportes_ibfk_1` FOREIGN KEY (`institucion_deportiva_id`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `instituciones_deportes_ibfk_2` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla gameon.instituciones_deportivas
CREATE TABLE IF NOT EXISTS `instituciones_deportivas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_instalacion_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla gameon.reservas
CREATE TABLE IF NOT EXISTS `reservas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `area_deportiva_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `area_deportiva_id` (`area_deportiva_id`),
  CONSTRAINT `reservas_ibfk_area` FOREIGN KEY (`area_deportiva_id`) REFERENCES `areas_deportivas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_ibfk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

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

-- La exportación de datos fue deseleccionada.

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

-- La exportación de datos fue deseleccionada.

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

-- La exportación de datos fue deseleccionada.

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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla gameon.usuarios_deportes
CREATE TABLE IF NOT EXISTS `usuarios_deportes` (
  `usuario_id` int(11) NOT NULL,
  `deporte_id` int(11) NOT NULL,
  PRIMARY KEY (`usuario_id`,`deporte_id`),
  KEY `deporte_id` (`deporte_id`),
  CONSTRAINT `usuarios_deportes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuarios_deportes_ibfk_2` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- La exportación de datos fue deseleccionada.

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

-- La exportación de datos fue deseleccionada.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
