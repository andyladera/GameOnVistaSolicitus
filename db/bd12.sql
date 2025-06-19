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
  `premio_1` varchar(255) DEFAULT NULL,
  `premio_2` varchar(255) DEFAULT NULL,
  `premio_3` varchar(255) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.torneos: ~1 rows (aproximadamente)
INSERT INTO `torneos` (`id`, `nombre`, `descripcion`, `deporte_id`, `organizador_tipo`, `organizador_id`, `institucion_sede_id`, `max_equipos`, `equipos_inscritos`, `fecha_inicio`, `fecha_fin`, `fecha_inscripcion_inicio`, `fecha_inscripcion_fin`, `estado`, `modalidad`, `premio_1`, `premio_2`, `premio_3`, `costo_inscripcion`, `imagen_torneo`, `creado_en`, `actualizado_en`) VALUES
	(7, 'CHAMPIONS LEAGUE TACNA - APERTURA I', 'La mejor edición Champions Tacna, ven, juega y gana y demuestra tus habilidades con tu equipo!', 1, 'institucion', 1, 1, 4, 0, '2025-07-20', '2025-07-20', '2025-06-30', '2025-07-10', 'proximo', 'eliminacion_simple', 'Un arroz con Huevo', 'Un huevo sin arroz', 'Un arroz sin huevo', 120.00, 'https://i.ibb.co/BHQxk6nj/3d686d3b1636.jpg', '2025-06-19 03:00:18', '2025-06-19 03:06:10');

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

-- Volcando datos para la tabla gameon.torneos_equipos: ~0 rows (aproximadamente)

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
  `area_deportiva_id` int(11) DEFAULT NULL,
  `equipo_local_id` int(11) DEFAULT NULL,
  `equipo_visitante_id` int(11) DEFAULT NULL,
  `fase` enum('primera_ronda','segunda_ronda','tercera_ronda','cuartos','semifinal','final','tercer_lugar') NOT NULL,
  `numero_partido` int(11) DEFAULT NULL,
  `ronda` int(11) DEFAULT NULL,
  `descripcion_partido` varchar(255) DEFAULT NULL,
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
  KEY `torneos_partidos_ibfk_area` (`area_deportiva_id`),
  KEY `idx_torneo_area` (`torneo_id`,`area_deportiva_id`),
  KEY `idx_ronda_partido` (`ronda`,`numero_partido`),
  KEY `idx_torneo_ronda` (`torneo_id`,`ronda`),
  CONSTRAINT `torneos_partidos_ibfk_1` FOREIGN KEY (`torneo_id`) REFERENCES `torneos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `torneos_partidos_ibfk_2` FOREIGN KEY (`equipo_local_id`) REFERENCES `equipos` (`id`),
  CONSTRAINT `torneos_partidos_ibfk_3` FOREIGN KEY (`equipo_visitante_id`) REFERENCES `equipos` (`id`),
  CONSTRAINT `torneos_partidos_ibfk_4` FOREIGN KEY (`equipo_ganador_id`) REFERENCES `equipos` (`id`),
  CONSTRAINT `torneos_partidos_ibfk_area` FOREIGN KEY (`area_deportiva_id`) REFERENCES `areas_deportivas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.torneos_partidos: ~3 rows (aproximadamente)
INSERT INTO `torneos_partidos` (`id`, `torneo_id`, `area_deportiva_id`, `equipo_local_id`, `equipo_visitante_id`, `fase`, `numero_partido`, `ronda`, `descripcion_partido`, `numero_grupo`, `fecha_partido`, `resultado_local`, `resultado_visitante`, `equipo_ganador_id`, `estado_partido`, `observaciones`, `creado_en`) VALUES
	(20, 7, 16, NULL, NULL, 'primera_ronda', NULL, NULL, 'Partido 1', NULL, '2025-07-20 08:00:00', NULL, NULL, NULL, 'programado', NULL, '2025-06-19 03:00:18'),
	(21, 7, 17, NULL, NULL, 'primera_ronda', NULL, NULL, 'Partido 2', NULL, '2025-07-20 09:00:00', NULL, NULL, NULL, 'programado', NULL, '2025-06-19 03:00:18'),
	(22, 7, 16, NULL, NULL, 'segunda_ronda', NULL, NULL, 'Partido 3', NULL, '2025-07-20 10:00:00', NULL, NULL, NULL, 'programado', NULL, '2025-06-19 03:00:18');

