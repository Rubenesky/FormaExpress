# FormaExpress

## Qué es
Proyecto académico del módulo Proyectos (CFGS DAW, IES Aguadulce, profesor Víctor
Valverde). Tarea 1 (propuesta + informe de mercado) ya entregada y en trámite de
aprobación. Este repo arranca la Tarea 2 (diseño de BD) y preparará la Tarea 3
(desarrollo).

LMS pequeño para formación y certificación de personal con alta rotación
(retail/logística). Nace de mi experiencia real como formador en Lidl desde 2020:
la formación hoy es oral, no queda registrada, y se repite desde cero con cada
cambio de personal.

## Roles
- **Administrador**: crea cursos, lecciones y evaluaciones; consulta el progreso
  de toda la plantilla.
- **Empleado**: ve sus cursos asignados, completa lecciones, responde evaluaciones.
- **Encargado (solo consulta)**: comprueba si un empleado concreto está certificado
  en algo, sin editar nada.

## Stack (cerrado, no proponer alternativas sin preguntar)
- Backend: PHP 8.3 + Laravel 13, con **Blade tradicional** — NADA de frontend
  desacoplado (sin Vue, sin React, sin SPA). Esto es una decisión deliberada para
  mantener la instalación simple, no un descuido.
- Base de datos: MySQL 8.
- Frontend: HTML5, CSS3, Bootstrap 5.3 **desde CDN** (jsDelivr, con SRI) en
  `layouts/app.blade.php`. JS solo el mínimo para validar formularios.
  **Sin Node, npm ni Vite**: la instalación solo necesita PHP y MySQL.
  **Sin Tailwind.**
- Idioma: español (`APP_LOCALE=es`). Textos de la interfaz en `lang/es.json`,
  mensajes de validación/auth en `lang/es/*.php`.
- Auth: laravel/ui con preset Bootstrap (no Breeze, que solo genera Tailwind).
- PDF de certificados: DomPDF.
- Tests: PHPUnit.
- Entorno local: Laragon. Control de versiones: Git/GitHub.

## Modelo de datos (11 tablas de dominio, ninguna aislada)
Ampliado de 8 a 11 al escribir las migraciones; se formaliza en la Tarea 2.
- empleados — **es la tabla de usuarios** (login). Columnas de auth en inglés
  (name, email, password) porque Laravel las espera; `rol` ENUM
  administrador/empleado/encargado. El modelo es `App\Models\User` con
  `$table = 'empleados'`.
- cursos
- lecciones (FK curso)
- asignaciones_curso (FK empleado + curso, fecha_asignacion; única por par)
- progreso_lecciones (FK empleado + lección, fecha_finalizacion)
- evaluaciones (FK **solo curso**, **única por curso**: índice único en
  curso_id; nota_minima en escala 0-10, **8.00 por defecto**, ajustable por
  evaluación). **Decisión del usuario, cerrada: las evaluaciones van SOLO por
  curso, nunca por lección. No reabrir.**
- preguntas (FK evaluación)
- opciones (FK pregunta, es_correcta)
- resultados_evaluacion (FK empleado + evaluación, nota, **aprobado**). Intentos
  **ilimitados** (confirmado): se guarda cada intento; el certificado se emite
  con el primer aprobado.

Migraciones añadidas en el módulo 2 (decididas por el usuario):
`2026_09_28_000001` unique(evaluaciones.curso_id) y `2026_09_28_000002`
resultados_evaluacion.aprobado (boolean: se guarda con la nota mínima vigente
en ese intento, así que cambiar nota_minima no altera intentos pasados).
- certificados (FK empleado + curso, fecha_emision; único por par)
- insignias (FK empleado, hito conseguido)

Borrados: cascada dentro de la estructura del curso (lecciones, evaluaciones,
preguntas, opciones); el historial del empleado bloquea borrados (FK RESTRICT:
el controlador captura el error 23000 y muestra un mensaje, nunca un 500).
Excepción: **ninguna FK impide desasignar un curso con progreso**; se comprueba
en la app (`Curso::tieneProgresoDe()`), decisión del usuario.

Pendiente para la Tarea 2: tabla de registro de consultas (RGPD), insignias
como catálogo + relación, usuario MySQL propio para la app en vez de root.

## Funcionalidad
- CRUD de cursos y lecciones (administrador).
- Asignación de cursos a empleados.
- Seguimiento de progreso lección a lección.
- Evaluación tipo test con corrección automática.
- Certificado en PDF al superar un curso.
- Insignias automáticas por hitos (ej. completar 5 cursos, nota máxima en una
  evaluación) — lógica en SQL/PHP, sin IA ni servicios externos.

## Estado por módulos (Tarea 3)
- **Módulo 1 — cursos y lecciones: hecho.**
- **Módulo 2 — asignación, progreso y evaluaciones: hecho.**
- **Módulo 3 — certificados e insignias: hecho** (sin rol encargado, ver abajo).

### Certificados e insignias (módulo 3, decisiones del usuario)
- Certificado **automático con el primer aprobado** de la evaluación del curso
  (`App\Support\Logros`, en la misma transacción que guarda el intento).
  **Un curso sin evaluación no certifica.** fecha_emision = día del primer
  aprobado; un aprobado posterior no duplica ni cambia la fecha (único por
  empleado + curso).
- PDF con DomPDF (`barryvdh/laravel-dompdf`), A4 apaisado, fuente DejaVu Sans
  con subsetting (~17 KB). Código visible `FX-000001` (id con ceros).
  Descarga: el propio empleado o un administrador (Gate `ver-certificado`).
- Insignias automáticas, catálogo fijo en `Insignia::CATALOGO`:
  `primer-certificado`, `cinco-certificados`, `nota-maxima` (un 10) y
  `primer-intento` (aprobar sin suspensos previos en esa evaluación).
  Migración `2026_09_29_000001`: unique(insignias.empleado_id, hito).
  El catálogo como tabla propia sigue pendiente para la Tarea 2.
- Pantallas: "Mis logros" (empleado: certificados con PDF e insignias
  conseguidas/pendientes), aviso de logros nuevos tras cada intento, certificado
  en el detalle del curso y columna "Certificado" en las asignaciones (admin).
- **Fuera de este módulo, por decisión del usuario**: la pantalla del rol
  encargado y el registro RGPD de consultas (tabla nueva): van juntos después
  de la Tarea 2.

### Autorización (Gates en `AppServiceProvider`, aplicados con `can:` en rutas)
- `administrar`: rol administrador (cursos, lecciones, asignaciones, evaluación).
- `estudiar`: rol empleado ("Mis cursos").
- `ver-curso` / `ver-leccion`: empleado con el curso asignado.
- `hacer-evaluacion`: empleado con el curso asignado, curso con **al menos una
  lección** (decisión del usuario), evaluación con al menos una pregunta y
  **todas las lecciones completadas**.
- `ver-certificado`: el empleado dueño del certificado o un administrador.
- El rol encargado aún no tiene pantallas (403 en todo).

### Reglas de negocio de las evaluaciones (fijadas por el usuario)
- Una evaluación por curso.
- Solo preguntas de opción única: mínimo 2 opciones y exactamente 1 correcta
  (lo valida `PreguntaRequest`; el formulario tiene 6 huecos y un radio).
- Nota = aciertos / total de preguntas × 10, redondeada a 2 decimales, sin
  penalización por fallo. Aprobado si nota >= nota_minima (comparado en
  céntimos enteros: `Evaluacion::aprueba()`).
- Intentos ilimitados; cada intento es una fila de resultados_evaluacion.
- Solo se puede hacer con el curso asignado, con al menos una lección y el 100%
  de las lecciones completadas (un curso sin lecciones no ofrece la evaluación). Todas las preguntas son obligatorias (un envío incompleto no
  cuenta como intento).
- Corrección en servidor (`Evaluacion::corregir()`): ni el HTML ni ningún dato
  enviado al empleado contiene `es_correcta` (además está en `#[Hidden]`).
- Tras enviar: nota, aprobado/suspendido y qué preguntas falló, sin decir cuál
  era la correcta (se muestra una vez, vía flash).

### Limitaciones conocidas
- **Editar (o borrar) preguntas de una evaluación con resultados existentes
  está permitido y cambia el significado de las notas pasadas.** Las pantallas
  del administrador lo avisan, pero no lo impiden.
- Salida siempre con `{{ }}`; los saltos de línea se conservan con CSS
  `white-space: pre-line`, no con `nl2br` (que obligaría a usar `{!! !!}`).

### Resuelto (no reabrir)
- **Formularios con arrays manipulados** (`titulo[]=x`): al fallar la
  validación, `bootstrap/app.php` guarda para `old()` solo lo que encaja con las
  reglas (`App\Support\EntradaAntigua`): nunca arrays en campos de texto ni
  contraseñas. Vale para todos los formularios web (también login y registro)
  sin tocar las vistas. Tests en `EntradaManipuladaTest`.
- **Zona horaria `Europe/Madrid`** (`APP_TIMEZONE` en `.env`; por defecto en
  `config/app.php`). Las fechas se guardan y se muestran en hora de Madrid.

## Fuera de alcance (no lo construyas aunque parezca fácil)
- Nóminas, turnos, cualquier otro proceso de RR. HH.
- Cualquier framework JS adicional.
- Cualquier funcionalidad de IA.

## Filosofía del proyecto
El profesor ha insistido explícitamente en evitar complejidad no justificada:
prioriza siempre la solución simple y bien documentada frente a la elegante.
La guía de instalación tiene que poder seguirla alguien en un ordenador distinto
al mío sin ayuda. Los criterios de corrección hacen referencia expresa a PHP,
así que el backend no se toca de lenguaje bajo ningún concepto.

## Seguridad y RGPD
- Contraseñas con el hash por defecto de Laravel (bcrypt).
- Control de acceso por rol en cada ruta/controlador.
- Registro de quién consulta datos de formación de quién.
- La app trata datos personales de empleados (RGPD/LOPDGDD) — no hace falta
  banner de cookies (solo cookie técnica de sesión).

## Calendario (módulo Proyectos)
- Ahora: Tarea 2 — diagrama E-R, paso a modelo relacional, script de creación
  de tablas, script de carga de datos de prueba (5-10 registros/tabla),
  usuarios/roles/privilegios de MySQL.
- Más adelante (Unidad 3+): Tarea 3 — desarrollo completo, guía de instalación,
  manual de usuario, batería de pruebas.

## Entorno de este equipo (no es la guía de instalación)
- PHP del proyecto: `C:\laragon\bin\php\php-8.3.35-Win32-vs16-x64\php.exe`.
  El `php` del PATH es el 8.1 de XAMPP (lo usan otros proyectos) y **no sirve**
  para Laravel 13: invocar siempre con la ruta completa.
- Composer: `<php 8.3> C:\composer\composer.phar ...`.
- MySQL en 127.0.0.1:3306 es el servicio de Windows `MySQL80` (8.0.34), no el de
  Laragon. BD `formaexpress`, usuario root sin contraseña (solo local).
- Administrador de prueba: `php artisan db:seed` crea admin@formaexpress.test /
  admin1234 (el registro público solo crea empleados).
- Tests: `artisan test` usa SQLite en memoria (pdo_sqlite activado en el php.ini
  de PHP 8.3).

## Cómo proponer siguientes pasos
Cuando termines un módulo y quieras proponer el siguiente paso, hazlo como
pregunta explícita ('¿Empiezo el módulo X, sí o no?'), nunca como anuncio de lo
que vas a hacer a continuación. Un 'sigue' o 'sí' mío después de eso responde a
la pregunta inmediatamente anterior que te haya hecho, no autoriza un módulo
entero ni ninguna decisión de diseño no formulada explícitamente. Si tienes
dudas de qué estoy confirmando, pregunta qué estoy confirmando antes de
escribir código.

## Metodología
Incremental por módulos, en este orden: (1) cursos y lecciones, (2) progreso y
evaluaciones, (3) certificados e insignias. Cada módulo se prueba antes de pasar
al siguiente.