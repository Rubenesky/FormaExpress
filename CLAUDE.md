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
- Backend: PHP 8 + Laravel, con **Blade tradicional** — NADA de frontend
  desacoplado (sin Vue, sin React, sin SPA). Esto es una decisión deliberada para
  mantener la instalación simple, no un descuido.
- Base de datos: MySQL 8.
- Frontend: HTML5, CSS3, Bootstrap. JS solo el mínimo para validar formularios.
- PDF de certificados: DomPDF.
- Tests: PHPUnit.
- Entorno local: Laragon. Control de versiones: Git/GitHub.

## Modelo de datos (mínimo ya cerrado, 8 tablas, ninguna aislada)
- empleados
- cursos
- lecciones (FK curso)
- progreso_lecciones (FK empleado + lección, fecha_finalizacion)
- evaluaciones (FK curso o lección)
- resultados_evaluacion (FK empleado + evaluación, nota)
- certificados (FK empleado + curso, fecha_emision)
- insignias (FK empleado, hito conseguido)

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

## Metodología
Incremental por módulos, en este orden: (1) cursos y lecciones, (2) progreso y
evaluaciones, (3) certificados e insignias. Cada módulo se prueba antes de pasar
al siguiente.