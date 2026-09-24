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
- evaluaciones (FK **solo curso** — confirmado, no por lección; nota_minima
  en escala 0-10, **8.00 por defecto**, ajustable por evaluación)
- preguntas (FK evaluación)
- opciones (FK pregunta, es_correcta)
- resultados_evaluacion (FK empleado + evaluación, nota). Intentos
  **ilimitados** (confirmado): se guarda cada intento; el certificado se emite
  con el primer aprobado.
- certificados (FK empleado + curso, fecha_emision; único por par)
- insignias (FK empleado, hito conseguido)

Borrados: cascada dentro de la estructura del curso (lecciones, evaluaciones,
preguntas, opciones); el historial del empleado bloquea borrados.

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

## Metodología
Incremental por módulos, en este orden: (1) cursos y lecciones, (2) progreso y
evaluaciones, (3) certificados e insignias. Cada módulo se prueba antes de pasar
al siguiente.