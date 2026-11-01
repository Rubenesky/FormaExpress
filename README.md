# FormaExpress

LMS ligero de formación y certificación de personal para negocios con alta rotación (retail, logística). Un administrador crea cursos y evaluaciones, asigna cursos a empleados, y cada empleado estudia, aprueba y descarga su certificado en PDF.

**Demo:** https://formaexpress.gt.tc

Proyecto del módulo de Proyectos del CFGS DAW (IES Aguadulce). Nace de mi experiencia como formador de equipos en logística: incorporar a una persona nueva rápido, y poder demostrar que está formada.

## Funcionalidades

- **Tres roles con permisos por Gates**: empleado (estudia sus cursos), encargado (consulta de solo lectura del progreso de su equipo) y administrador (gestión completa).
- **Cursos y lecciones**: CRUD completo; el empleado solo ve los cursos que se le han asignado.
- **Asignación de cursos a empleados** por el administrador.
- **Progreso por lección**: el empleado marca lecciones como completadas.
- **Evaluación por curso**: preguntas con opciones, solo accesible cuando todas las lecciones están completadas; calificación automática.
- **Certificados en PDF** (DomPDF) al aprobar, e **insignias** por logros.
- **Registro RGPD de consultas**: cada consulta del encargado sobre un empleado queda registrada y es solo de lectura.
- **Confirmación explícita antes de borrar** (pantallas de confirmación, sin eliminar con un solo clic).

## Stack

PHP 8.3 · Laravel 13 (Blade + laravel/ui, Bootstrap 5) · MySQL 8 · DomPDF · PHPUnit

Decisión: vistas Blade sin frontend desacoplado. Para este alcance una SPA añadiría complejidad sin aportar valor, y la autorización queda en el servidor.

## Modelo de datos

Curso → Lección, Curso → Evaluación → Pregunta → Opción, Usuario ↔ Curso (asignación), ResultadoEvaluacion, Certificado, Insignia, RegistroConsulta.

## Seguridad

- Autorización por Gates y middleware en cada ruta (`estudiar`, `ver-curso`, `ver-leccion`, `hacer-evaluacion`, `consultar`, `administrar`).
- Tests de acceso con entrada manipulada (`EntradaManipuladaTest`) para comprobar que un usuario no accede a cursos o lecciones ajenos.

## Puesta en marcha

Requisitos: PHP 8.3, Composer, MySQL 8.

```bash
git clone https://github.com/Rubenesky/FormaExpress.git
cd FormaExpress
composer install
cp .env.example .env     # configura DB_* para tu MySQL
php artisan key:generate
php artisan migrate
php artisan serve
```

## Tests

```bash
composer test
```

Cubren autenticación, cursos, lecciones, asignaciones, progreso, evaluaciones (administración y realización), certificados, insignias, consulta del encargado, el calificador y entradas manipuladas.

## Documentación

- [Instalación](docs/instalacion.md)
- [Manual de usuario](docs/manual-usuario.md)
- [Pruebas](docs/pruebas.md)

## Limitaciones

- Despliegue en hosting gratuito (InfinityFree): sin CI/CD ni entorno de staging.
- Sin contenido multimedia en lecciones ni analítica avanzada.
