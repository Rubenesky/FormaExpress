-- =============================================================================
-- FormaExpress - Script de creación de la base de datos (Tarea 2)
--
-- Extraído del esquema real de MySQL 8 tras aplicar todas las migraciones de
-- Laravel (SHOW CREATE TABLE de cada tabla; las reglas de las claves foráneas
-- salen de information_schema.REFERENTIAL_CONSTRAINTS).
--
-- Contiene las 13 tablas de dominio y las 4 filas del catálogo de insignias.
-- NO contiene las tablas internas de Laravel (migrations, sessions, cache,
-- cache_locks, jobs, job_batches, failed_jobs, password_reset_tokens): para
-- instalar y ejecutar la aplicación se usa `php artisan migrate`, que crea
-- todo (ver docs/instalacion.md). Este script documenta el diseño y permite
-- crear el esquema de dominio sin Laravel.
--
-- Se ejecuta sobre un servidor sin la base de datos formaexpress (o con ella
-- vacía): si una tabla ya existe, su CREATE TABLE falla.
--
-- Claves foráneas: ninguna declara ON UPDATE, así que todas son ON UPDATE
-- NO ACTION (los id no se modifican nunca). Las reglas ON DELETE se explican
-- encima de cada tabla. Ninguna clave foránea usa RESTRICT de forma explícita.
-- =============================================================================

CREATE DATABASE IF NOT EXISTS formaexpress
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE formaexpress;

-- -----------------------------------------------------------------------------
-- empleados: Usuarios de la aplicación (tabla de login de Laravel). rol: administrador, empleado o encargado.
-- Sin claves foráneas.
-- -----------------------------------------------------------------------------
CREATE TABLE `empleados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('administrador','empleado','encargado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'empleado',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empleados_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- cursos: Cursos de formación.
-- Sin claves foráneas.
-- -----------------------------------------------------------------------------
CREATE TABLE `cursos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- lecciones: Lecciones de cada curso, ordenadas por "orden".
-- Claves foráneas:
--   curso_id -> cursos.id: ON DELETE CASCADE. Al borrar la fila de cursos,
--     se borran también las filas de esta tabla que la referencian.
-- -----------------------------------------------------------------------------
CREATE TABLE `lecciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `curso_id` bigint unsigned NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` smallint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lecciones_curso_id_foreign` (`curso_id`),
  CONSTRAINT `lecciones_curso_id_foreign` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- asignaciones_curso: Qué cursos tiene asignados cada empleado. Una fila por (empleado, curso).
-- Claves foráneas:
--   curso_id -> cursos.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     cursos mientras haya filas en esta tabla que la referencien.
--   empleado_id -> empleados.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     empleados mientras haya filas en esta tabla que la referencien.
-- -----------------------------------------------------------------------------
CREATE TABLE `asignaciones_curso` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `curso_id` bigint unsigned NOT NULL,
  `fecha_asignacion` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asignaciones_curso_empleado_id_curso_id_unique` (`empleado_id`,`curso_id`),
  KEY `asignaciones_curso_curso_id_foreign` (`curso_id`),
  CONSTRAINT `asignaciones_curso_curso_id_foreign` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  CONSTRAINT `asignaciones_curso_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- progreso_lecciones: Lecciones completadas por cada empleado. Una fila por (empleado, lección).
-- Claves foráneas:
--   empleado_id -> empleados.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     empleados mientras haya filas en esta tabla que la referencien.
--   leccion_id -> lecciones.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     lecciones mientras haya filas en esta tabla que la referencien.
-- -----------------------------------------------------------------------------
CREATE TABLE `progreso_lecciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `leccion_id` bigint unsigned NOT NULL,
  `fecha_finalizacion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `progreso_lecciones_empleado_id_leccion_id_unique` (`empleado_id`,`leccion_id`),
  KEY `progreso_lecciones_leccion_id_foreign` (`leccion_id`),
  CONSTRAINT `progreso_lecciones_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`),
  CONSTRAINT `progreso_lecciones_leccion_id_foreign` FOREIGN KEY (`leccion_id`) REFERENCES `lecciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- evaluaciones: Evaluación tipo test de un curso. Como mucho una por curso (único en curso_id). nota_minima de 0 a 10.
-- Claves foráneas:
--   curso_id -> cursos.id: ON DELETE CASCADE. Al borrar la fila de cursos,
--     se borran también las filas de esta tabla que la referencian.
-- -----------------------------------------------------------------------------
CREATE TABLE `evaluaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `curso_id` bigint unsigned NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nota_minima` decimal(4,2) NOT NULL DEFAULT '8.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evaluaciones_curso_id_unique` (`curso_id`),
  CONSTRAINT `evaluaciones_curso_id_foreign` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- preguntas: Preguntas de cada evaluación.
-- Claves foráneas:
--   evaluacion_id -> evaluaciones.id: ON DELETE CASCADE. Al borrar la fila de evaluaciones,
--     se borran también las filas de esta tabla que la referencian.
-- -----------------------------------------------------------------------------
CREATE TABLE `preguntas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `evaluacion_id` bigint unsigned NOT NULL,
  `enunciado` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `preguntas_evaluacion_id_foreign` (`evaluacion_id`),
  CONSTRAINT `preguntas_evaluacion_id_foreign` FOREIGN KEY (`evaluacion_id`) REFERENCES `evaluaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- opciones: Opciones de cada pregunta; exactamente una con es_correcta = 1 (lo valida la aplicación).
-- Claves foráneas:
--   pregunta_id -> preguntas.id: ON DELETE CASCADE. Al borrar la fila de preguntas,
--     se borran también las filas de esta tabla que la referencian.
-- -----------------------------------------------------------------------------
CREATE TABLE `opciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pregunta_id` bigint unsigned NOT NULL,
  `texto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `es_correcta` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `opciones_pregunta_id_foreign` (`pregunta_id`),
  CONSTRAINT `opciones_pregunta_id_foreign` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- resultados_evaluacion: Cada intento de un empleado en una evaluación (intentos ilimitados). aprobado se guarda con la nota mínima vigente en ese intento.
-- Claves foráneas:
--   empleado_id -> empleados.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     empleados mientras haya filas en esta tabla que la referencien.
--   evaluacion_id -> evaluaciones.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     evaluaciones mientras haya filas en esta tabla que la referencien.
-- -----------------------------------------------------------------------------
CREATE TABLE `resultados_evaluacion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `evaluacion_id` bigint unsigned NOT NULL,
  `nota` decimal(4,2) NOT NULL,
  `aprobado` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resultados_evaluacion_empleado_id_foreign` (`empleado_id`),
  KEY `resultados_evaluacion_evaluacion_id_foreign` (`evaluacion_id`),
  CONSTRAINT `resultados_evaluacion_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`),
  CONSTRAINT `resultados_evaluacion_evaluacion_id_foreign` FOREIGN KEY (`evaluacion_id`) REFERENCES `evaluaciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- certificados: Certificado de un empleado en un curso, emitido con su primer aprobado. Uno por (empleado, curso).
-- Claves foráneas:
--   curso_id -> cursos.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     cursos mientras haya filas en esta tabla que la referencien.
--   empleado_id -> empleados.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     empleados mientras haya filas en esta tabla que la referencien.
-- -----------------------------------------------------------------------------
CREATE TABLE `certificados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `curso_id` bigint unsigned NOT NULL,
  `fecha_emision` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificados_empleado_id_curso_id_unique` (`empleado_id`,`curso_id`),
  KEY `certificados_curso_id_foreign` (`curso_id`),
  CONSTRAINT `certificados_curso_id_foreign` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  CONSTRAINT `certificados_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- insignias_catalogo: Catálogo fijo de insignias. codigo es la clave estable que usa la aplicación.
-- Sin claves foráneas.
-- -----------------------------------------------------------------------------
CREATE TABLE `insignias_catalogo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `insignias_catalogo_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- empleado_insignia: Insignias concedidas: relación entre empleados e insignias_catalogo. Una por (empleado, insignia).
-- Claves foráneas:
--   empleado_id -> empleados.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     empleados mientras haya filas en esta tabla que la referencien.
--     (Conserva el nombre de cuando la tabla se llamaba "insignias": MySQL no
--     renombra las restricciones al renombrar una tabla.)
--   insignia_id -> insignias_catalogo.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     insignias_catalogo mientras haya filas en esta tabla que la referencien.
-- -----------------------------------------------------------------------------
CREATE TABLE `empleado_insignia` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `insignia_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empleado_insignia_empleado_id_insignia_id_unique` (`empleado_id`,`insignia_id`),
  KEY `empleado_insignia_insignia_id_foreign` (`insignia_id`),
  CONSTRAINT `empleado_insignia_insignia_id_foreign` FOREIGN KEY (`insignia_id`) REFERENCES `insignias_catalogo` (`id`),
  CONSTRAINT `insignias_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- registro_consultas: Registro RGPD: quién consultó la ficha de formación de qué empleado y cuándo. Solo se inserta.
-- Claves foráneas:
--   consultado_id -> empleados.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     empleados mientras haya filas en esta tabla que la referencien.
--   consultor_id -> empleados.id: ON DELETE NO ACTION (la regla por defecto de MySQL;
--     en InnoDB se comporta igual que RESTRICT). No se puede borrar la fila de
--     empleados mientras haya filas en esta tabla que la referencien.
-- -----------------------------------------------------------------------------
CREATE TABLE `registro_consultas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consultor_id` bigint unsigned NOT NULL,
  `consultado_id` bigint unsigned NOT NULL,
  `consultado_en` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `registro_consultas_consultor_id_foreign` (`consultor_id`),
  KEY `registro_consultas_consultado_id_consultado_en_index` (`consultado_id`,`consultado_en`),
  CONSTRAINT `registro_consultas_consultado_id_foreign` FOREIGN KEY (`consultado_id`) REFERENCES `empleados` (`id`),
  CONSTRAINT `registro_consultas_consultor_id_foreign` FOREIGN KEY (`consultor_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Catálogo de insignias (mismas filas que crea la migración). La aplicación las
-- busca por codigo, no por id.
-- -----------------------------------------------------------------------------
INSERT INTO insignias_catalogo (id, codigo, nombre, descripcion) VALUES
  (1, 'primer-certificado', 'Primer curso certificado', 'Obtén tu primer certificado.'),
  (2, 'cinco-certificados', '5 cursos certificados', 'Obtén 5 certificados.'),
  (3, 'nota-maxima', 'Nota máxima', 'Saca un 10 en una evaluación.'),
  (4, 'primer-intento', 'A la primera', 'Aprueba una evaluación en el primer intento.');
