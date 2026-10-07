-- =============================================================================
-- FormaExpress - Datos de prueba (Tarea 2)
--
-- Se ejecuta DESPUÉS de 01_crear_base_de_datos.sql, sobre la base de datos
-- recién creada (vacía salvo el catálogo de insignias). Los id son fijos para
-- que las referencias entre tablas se puedan leer; si las tablas ya tienen
-- datos con esos id o emails, el script falla y no carga nada (transacción).
--
-- Contraseña de TODOS los usuarios: prueba1234 (hash bcrypt de Laravel).
-- Solo para pruebas en local: no usar en un despliegue real.
--
-- Registros por tabla:
--   empleados 8 · cursos 6 · lecciones 7 · asignaciones_curso 8
--   progreso_lecciones 8 · evaluaciones 5 · preguntas 5 · opciones 10
--   resultados_evaluacion 8 · certificados 5 · empleado_insignia 10
--   registro_consultas 6 · insignias_catalogo 4 (las crea el script 01:
--   el catálogo es fijo y no tiene más filas).
--
-- Coherencia (las mismas reglas que aplica la aplicación):
--   - Solo hay intentos de evaluación de empleados con el curso asignado y
--     todas sus lecciones completadas antes del intento.
--   - Cada evaluación tiene 1 pregunta, así que la nota es 0 o 10;
--     aprobado = nota >= nota_minima.
--   - Cada certificado corresponde al primer aprobado: fecha_emision es el día
--     de ese intento, y el empleado tiene el curso asignado y el 100 % de las
--     lecciones completadas.
--   - Las insignias son las que concede la aplicación con esos intentos:
--     "A la primera" si aprueba sin intentos previos en esa evaluación,
--     "Nota máxima" con un 10, "Primer curso certificado" con el primer
--     certificado. Nadie tiene 5 certificados, así que nadie tiene
--     "5 cursos certificados". Cada insignia se concede una vez, con la fecha
--     del intento que la consigue.
--   - insignia_id se busca en insignias_catalogo por codigo: si un código no
--     existiera, la subconsulta daría NULL y el INSERT fallaría.
--
-- Casos que cubren los datos:
--   - Ana: dos cursos certificados a la primera.
--   - Carlos: suspende y aprueba después (sin "A la primera"); además completa
--     "Uso de la transpaleta", que no tiene evaluación y por eso no certifica.
--   - Lucía y Javier: un certificado cada uno (Javier, tras un suspenso).
--   - Elena: curso a medias (1 de 2 lecciones), sin intentos.
--   - Pablo: completa el curso y suspende; sin certificado.
-- =============================================================================

SET NAMES utf8mb4;
USE formaexpress;

START TRANSACTION;

-- Usuarios: 1 administradora, 1 encargado y 6 empleados.
INSERT INTO empleados (id, name, email, email_verified_at, password, rol, created_at, updated_at) VALUES
  (1, 'Laura Gómez',    'laura.gomez@formaexpress.test',    '2026-09-01 08:00:00', '$2y$12$wiz2VY5v6RSBTYouPekiqeteS.j.jttdQDoZZ0LAtk1CSaFTKLfLO', 'administrador', '2026-09-01 08:00:00', '2026-09-01 08:00:00'),
  (2, 'Marcos Ruiz',    'marcos.ruiz@formaexpress.test',    '2026-09-01 08:00:00', '$2y$12$wiz2VY5v6RSBTYouPekiqeteS.j.jttdQDoZZ0LAtk1CSaFTKLfLO', 'encargado',     '2026-09-01 08:00:00', '2026-09-01 08:00:00'),
  (3, 'Ana López',      'ana.lopez@formaexpress.test',      '2026-09-01 08:00:00', '$2y$12$wiz2VY5v6RSBTYouPekiqeteS.j.jttdQDoZZ0LAtk1CSaFTKLfLO', 'empleado',      '2026-09-01 08:00:00', '2026-09-01 08:00:00'),
  (4, 'Carlos Pérez',   'carlos.perez@formaexpress.test',   '2026-09-01 08:00:00', '$2y$12$wiz2VY5v6RSBTYouPekiqeteS.j.jttdQDoZZ0LAtk1CSaFTKLfLO', 'empleado',      '2026-09-01 08:00:00', '2026-09-01 08:00:00'),
  (5, 'Lucía Martín',   'lucia.martin@formaexpress.test',   '2026-09-01 08:00:00', '$2y$12$wiz2VY5v6RSBTYouPekiqeteS.j.jttdQDoZZ0LAtk1CSaFTKLfLO', 'empleado',      '2026-09-01 08:00:00', '2026-09-01 08:00:00'),
  (6, 'Javier Sánchez', 'javier.sanchez@formaexpress.test', '2026-09-01 08:00:00', '$2y$12$wiz2VY5v6RSBTYouPekiqeteS.j.jttdQDoZZ0LAtk1CSaFTKLfLO', 'empleado',      '2026-09-01 08:00:00', '2026-09-01 08:00:00'),
  (7, 'Elena Torres',   'elena.torres@formaexpress.test',   '2026-09-01 08:00:00', '$2y$12$wiz2VY5v6RSBTYouPekiqeteS.j.jttdQDoZZ0LAtk1CSaFTKLfLO', 'empleado',      '2026-09-01 08:00:00', '2026-09-01 08:00:00'),
  (8, 'Pablo Navarro',  'pablo.navarro@formaexpress.test',  '2026-09-01 08:00:00', '$2y$12$wiz2VY5v6RSBTYouPekiqeteS.j.jttdQDoZZ0LAtk1CSaFTKLfLO', 'empleado',      '2026-09-01 08:00:00', '2026-09-01 08:00:00');

-- Cursos: el 6 no tiene evaluación (completarlo no da certificado).
INSERT INTO cursos (id, titulo, descripcion, created_at, updated_at) VALUES
  (1, 'Apertura de caja',                'Arqueo y fondo de caja al empezar el turno.', '2026-09-01 09:00:00', '2026-09-01 09:00:00'),
  (2, 'Prevención de riesgos laborales', 'Riesgos básicos en tienda y almacén.',        '2026-09-01 09:00:00', '2026-09-01 09:00:00'),
  (3, 'Recepción de mercancía',          'Cómo recibir y comprobar un pedido.',         '2026-09-01 09:00:00', '2026-09-01 09:00:00'),
  (4, 'Atención al cliente',             'Trato con el cliente en caja y en sala.',     '2026-09-01 09:00:00', '2026-09-01 09:00:00'),
  (5, 'Seguridad alimentaria',           'Conservación de productos refrigerados.',     '2026-09-01 09:00:00', '2026-09-01 09:00:00'),
  (6, 'Uso de la transpaleta',           'Manejo seguro de la transpaleta manual.',     '2026-09-01 09:00:00', '2026-09-01 09:00:00');

-- Lecciones: el curso 1 tiene 2; los demás, 1.
INSERT INTO lecciones (id, curso_id, titulo, contenido, orden, created_at, updated_at) VALUES
  (1, 1, 'Arqueo inicial',               'Cuenta el efectivo de la caja antes de abrir y anótalo.',          1, '2026-09-01 09:30:00', '2026-09-01 09:30:00'),
  (2, 1, 'Fondo de caja',                'Comprueba que el fondo de caja coincide con el importe fijado.',   2, '2026-09-01 09:30:00', '2026-09-01 09:30:00'),
  (3, 2, 'Riesgos en el almacén',        'Usa guantes y calzado de seguridad al manipular cajas pesadas.',   1, '2026-09-01 09:30:00', '2026-09-01 09:30:00'),
  (4, 3, 'Comprobar el albarán',         'Antes de firmar, comprueba que el albarán coincide con lo recibido.', 1, '2026-09-01 09:30:00', '2026-09-01 09:30:00'),
  (5, 4, 'Saludo y despedida',           'Saluda a cada cliente y avísale si tiene que esperar.',            1, '2026-09-01 09:30:00', '2026-09-01 09:30:00'),
  (6, 5, 'Cadena de frío',               'Los productos refrigerados se conservan a 4 °C como máximo.',      1, '2026-09-01 09:30:00', '2026-09-01 09:30:00'),
  (7, 6, 'Uso seguro de la transpaleta', 'Empuja la transpaleta, no tires de ella, y no la sobrecargues.',   1, '2026-09-01 09:30:00', '2026-09-01 09:30:00');

-- Asignaciones (una por empleado y curso).
INSERT INTO asignaciones_curso (id, empleado_id, curso_id, fecha_asignacion, created_at, updated_at) VALUES
  (1, 3, 2, '2026-09-01', '2026-09-01 10:00:00', '2026-09-01 10:00:00'),
  (2, 3, 3, '2026-09-01', '2026-09-01 10:00:00', '2026-09-01 10:00:00'),
  (3, 4, 2, '2026-09-02', '2026-09-02 10:00:00', '2026-09-02 10:00:00'),
  (4, 4, 6, '2026-09-02', '2026-09-02 10:00:00', '2026-09-02 10:00:00'),
  (5, 5, 4, '2026-09-03', '2026-09-03 10:00:00', '2026-09-03 10:00:00'),
  (6, 6, 5, '2026-09-03', '2026-09-03 10:00:00', '2026-09-03 10:00:00'),
  (7, 7, 1, '2026-09-04', '2026-09-04 10:00:00', '2026-09-04 10:00:00'),
  (8, 8, 3, '2026-09-04', '2026-09-04 10:00:00', '2026-09-04 10:00:00');

-- Lecciones completadas. Elena (7) solo ha hecho la 1 de las 2 del curso 1.
INSERT INTO progreso_lecciones (id, empleado_id, leccion_id, fecha_finalizacion, created_at, updated_at) VALUES
  (1, 3, 3, '2026-09-05 10:00:00', '2026-09-05 10:00:00', '2026-09-05 10:00:00'),
  (2, 3, 4, '2026-09-08 10:00:00', '2026-09-08 10:00:00', '2026-09-08 10:00:00'),
  (3, 4, 3, '2026-09-05 12:00:00', '2026-09-05 12:00:00', '2026-09-05 12:00:00'),
  (4, 4, 7, '2026-09-06 09:30:00', '2026-09-06 09:30:00', '2026-09-06 09:30:00'),
  (5, 5, 5, '2026-09-07 11:00:00', '2026-09-07 11:00:00', '2026-09-07 11:00:00'),
  (6, 6, 6, '2026-09-07 16:00:00', '2026-09-07 16:00:00', '2026-09-07 16:00:00'),
  (7, 7, 1, '2026-09-08 09:00:00', '2026-09-08 09:00:00', '2026-09-08 09:00:00'),
  (8, 8, 4, '2026-09-08 12:00:00', '2026-09-08 12:00:00', '2026-09-08 12:00:00');

-- Evaluaciones: una por curso, salvo el 6.
INSERT INTO evaluaciones (id, curso_id, titulo, nota_minima, created_at, updated_at) VALUES
  (1, 1, 'Evaluación: Apertura de caja',                8.00, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (2, 2, 'Evaluación: Prevención de riesgos laborales', 8.00, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (3, 3, 'Evaluación: Recepción de mercancía',          5.00, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (4, 4, 'Evaluación: Atención al cliente',             8.00, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (5, 5, 'Evaluación: Seguridad alimentaria',           7.50, '2026-09-01 11:00:00', '2026-09-01 11:00:00');

-- Una pregunta por evaluación.
INSERT INTO preguntas (id, evaluacion_id, enunciado, created_at, updated_at) VALUES
  (1, 1, '¿Qué se cuenta al abrir la caja?',                              '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (2, 2, '¿Qué hay que usar al manipular cajas pesadas?',                 '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (3, 3, '¿Qué se comprueba antes de firmar la recepción de un pedido?',  '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (4, 4, '¿Qué se hace si un cliente tiene que esperar?',                 '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (5, 5, '¿A qué temperatura máxima se conservan los refrigerados?',      '2026-09-01 11:00:00', '2026-09-01 11:00:00');

-- Dos opciones por pregunta; exactamente una correcta.
INSERT INTO opciones (id, pregunta_id, texto, es_correcta, created_at, updated_at) VALUES
  (1,  1, 'El fondo de caja',                            1, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (2,  1, 'Las ventas del día anterior',                 0, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (3,  2, 'Guantes y calzado de seguridad',              1, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (4,  2, 'Nada en especial',                            0, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (5,  3, 'Que el albarán coincide con lo recibido',     1, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (6,  3, 'El color de las cajas',                       0, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (7,  4, 'Saludarle y avisarle de que enseguida se le atiende', 1, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (8,  4, 'Seguir con lo que se estaba haciendo',        0, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (9,  5, '4 °C',                                        1, '2026-09-01 11:00:00', '2026-09-01 11:00:00'),
  (10, 5, '15 °C',                                       0, '2026-09-01 11:00:00', '2026-09-01 11:00:00');

-- Intentos. Siempre después de completar todas las lecciones del curso.
INSERT INTO resultados_evaluacion (id, empleado_id, evaluacion_id, nota, aprobado, created_at, updated_at) VALUES
  (1, 3, 2, 10.00, 1, '2026-09-05 10:30:00', '2026-09-05 10:30:00'),  -- Ana, PRL: aprueba a la primera
  (2, 3, 3, 10.00, 1, '2026-09-08 10:30:00', '2026-09-08 10:30:00'),  -- Ana, recepción: aprueba a la primera
  (3, 4, 2,  0.00, 0, '2026-09-05 12:30:00', '2026-09-05 12:30:00'),  -- Carlos, PRL: suspende
  (4, 4, 2, 10.00, 1, '2026-09-06 08:45:00', '2026-09-06 08:45:00'),  -- Carlos, PRL: aprueba en el 2.º intento
  (5, 5, 4, 10.00, 1, '2026-09-07 11:30:00', '2026-09-07 11:30:00'),  -- Lucía, atención: aprueba a la primera
  (6, 6, 5,  0.00, 0, '2026-09-07 16:30:00', '2026-09-07 16:30:00'),  -- Javier, seguridad: suspende
  (7, 6, 5, 10.00, 1, '2026-09-08 17:00:00', '2026-09-08 17:00:00'),  -- Javier, seguridad: aprueba en el 2.º intento
  (8, 8, 3,  0.00, 0, '2026-09-08 12:30:00', '2026-09-08 12:30:00');  -- Pablo, recepción: suspende

-- Certificados: uno por cada primer aprobado, con la fecha de ese intento.
INSERT INTO certificados (id, empleado_id, curso_id, fecha_emision, created_at, updated_at) VALUES
  (1, 3, 2, '2026-09-05', '2026-09-05 10:30:00', '2026-09-05 10:30:00'),
  (2, 3, 3, '2026-09-08', '2026-09-08 10:30:00', '2026-09-08 10:30:00'),
  (3, 4, 2, '2026-09-06', '2026-09-06 08:45:00', '2026-09-06 08:45:00'),
  (4, 5, 4, '2026-09-07', '2026-09-07 11:30:00', '2026-09-07 11:30:00'),
  (5, 6, 5, '2026-09-08', '2026-09-08 17:00:00', '2026-09-08 17:00:00');

-- Insignias concedidas, con insignia_id real del catálogo (buscado por codigo).
INSERT INTO empleado_insignia (id, empleado_id, insignia_id, created_at, updated_at) VALUES
  (1,  3, (SELECT id FROM insignias_catalogo WHERE codigo = 'primer-intento'),     '2026-09-05 10:30:00', '2026-09-05 10:30:00'),
  (2,  3, (SELECT id FROM insignias_catalogo WHERE codigo = 'nota-maxima'),        '2026-09-05 10:30:00', '2026-09-05 10:30:00'),
  (3,  3, (SELECT id FROM insignias_catalogo WHERE codigo = 'primer-certificado'), '2026-09-05 10:30:00', '2026-09-05 10:30:00'),
  (4,  4, (SELECT id FROM insignias_catalogo WHERE codigo = 'nota-maxima'),        '2026-09-06 08:45:00', '2026-09-06 08:45:00'),
  (5,  4, (SELECT id FROM insignias_catalogo WHERE codigo = 'primer-certificado'), '2026-09-06 08:45:00', '2026-09-06 08:45:00'),
  (6,  5, (SELECT id FROM insignias_catalogo WHERE codigo = 'primer-intento'),     '2026-09-07 11:30:00', '2026-09-07 11:30:00'),
  (7,  5, (SELECT id FROM insignias_catalogo WHERE codigo = 'nota-maxima'),        '2026-09-07 11:30:00', '2026-09-07 11:30:00'),
  (8,  5, (SELECT id FROM insignias_catalogo WHERE codigo = 'primer-certificado'), '2026-09-07 11:30:00', '2026-09-07 11:30:00'),
  (9,  6, (SELECT id FROM insignias_catalogo WHERE codigo = 'nota-maxima'),        '2026-09-08 17:00:00', '2026-09-08 17:00:00'),
  (10, 6, (SELECT id FROM insignias_catalogo WHERE codigo = 'primer-certificado'), '2026-09-08 17:00:00', '2026-09-08 17:00:00');

-- Registro RGPD: fichas consultadas por el encargado (2) y la administradora (1).
INSERT INTO registro_consultas (id, consultor_id, consultado_id, consultado_en) VALUES
  (1, 2, 3, '2026-09-10 09:00:00'),
  (2, 2, 4, '2026-09-10 09:05:00'),
  (3, 2, 5, '2026-09-10 09:10:00'),
  (4, 1, 6, '2026-09-11 10:00:00'),
  (5, 2, 7, '2026-09-11 10:15:00'),
  (6, 1, 8, '2026-09-12 08:30:00');

COMMIT;
