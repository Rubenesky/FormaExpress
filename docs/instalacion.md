# Guía de instalación de FormaExpress

Esta guía explica cómo poner FormaExpress en marcha en un equipo nuevo para
desarrollo o evaluación en local. Sirve igual en Windows, macOS y Linux.

> **Despliegue online: pendiente.** Todavía no existe ninguna versión publicada
> en internet ni URL de acceso. Esta guía solo cubre la instalación en local.

---

## 1. Requisitos

| Programa | Versión | Para qué |
|---|---|---|
| PHP | **8.3 o superior** (línea de comandos) | Ejecutar la aplicación (Laravel 13) |
| Composer | 2.x | Instalar las dependencias de PHP |
| MySQL | 8.x | Base de datos |
| Git | cualquiera reciente | Descargar el proyecto |

**No hace falta Node.js ni npm.** Bootstrap se carga desde un CDN, así que no hay
ningún paso de compilación de JavaScript o CSS. El navegador sí necesita
conexión a internet para cargar Bootstrap.

### Extensiones de PHP necesarias

Comprueba que están activas con `php -m`:

- `pdo_mysql` (conexión con MySQL)
- `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `ctype`, `iconv`, `filter`,
  `hash`, `session`, `json`, `pcre`
- `dom` y `libxml` (las usa la generación de certificados en PDF)
- `zip` (o tener el programa `unzip`), para que Composer pueda descargar paquetes

Si alguna falta, actívala en el `php.ini` de tu instalación de PHP (en Windows,
quitando el `;` delante de la línea `extension=...` correspondiente).

Comprueba la versión de PHP antes de empezar:

```bash
php -v        # debe mostrar 8.3 o superior
composer -V
mysql --version
git --version
```

> Si tienes varias versiones de PHP instaladas, asegúrate de que el `php` que
> responde en la terminal es el 8.3 o superior: Composer y Artisan usan ese.

---

## 2. Descargar el proyecto

```bash
git clone <URL-del-repositorio> formaexpress
cd formaexpress
```

> `<URL-del-repositorio>` es la dirección del repositorio en GitHub. **Pendiente:**
> el repositorio todavía no está publicado; sustitúyela cuando lo esté.

Todas las órdenes siguientes se ejecutan **dentro de la carpeta `formaexpress`**.

---

## 3. Instalar las dependencias de PHP

```bash
composer install
```

Descarga Laravel y el resto de librerías en la carpeta `vendor/`. Tarda unos
minutos la primera vez.

---

## 4. Crear el archivo de configuración `.env`

Copia la plantilla:

```bash
# Linux / macOS / Git Bash
cp .env.example .env

# Windows (símbolo del sistema)
copy .env.example .env
```

Abre `.env` con un editor de texto y revisa estas variables. **Solo es
obligatorio rellenar las de la base de datos**; el resto ya trae un valor válido
para local.

| Variable | Valor | Notas |
|---|---|---|
| `DB_HOST` | `127.0.0.1` | Dirección del servidor MySQL |
| `DB_PORT` | `3306` | Puerto de MySQL |
| `DB_DATABASE` | `formaexpress` | Nombre de la base de datos (se crea en el paso 6) |
| `DB_USERNAME` | **tu usuario de MySQL** | Debe poder crear tablas en esa base de datos |
| `DB_PASSWORD` | **su contraseña** | Déjalo vacío si el usuario no tiene contraseña |
| `APP_URL` | `http://localhost:8000` | Dirección con la que abrirás la aplicación |
| `APP_TIMEZONE` | `Europe/Madrid` | Ya viene puesto: fechas y horas en hora de Madrid |
| `APP_LOCALE` | `es` | Ya viene puesto: interfaz en español |
| `APP_KEY` | *(vacío)* | **No lo rellenes a mano**: se genera en el paso 5 |

No hace falta configurar correo, Redis ni nada más: las sesiones, la caché y las
colas se guardan en la propia base de datos.

---

## 5. Generar la clave de la aplicación

```bash
php artisan key:generate
```

Rellena `APP_KEY` en el `.env`. Sin ella, la aplicación no arranca.

---

## 6. Crear la base de datos en MySQL

Entra en MySQL con tu usuario (o con uno que tenga permiso para crear bases de
datos):

```bash
mysql -u TU_USUARIO -p
```

y crea la base de datos con codificación UTF-8 completa:

```sql
CREATE DATABASE formaexpress CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

Si usaste otro nombre en `DB_DATABASE`, usa ese mismo aquí.

> La creación de un usuario de MySQL propio para la aplicación, con permisos
> solo sobre esta base de datos, forma parte de la Tarea 2 y todavía no está
> documentada. Mientras tanto, usa un usuario existente con permisos sobre
> `formaexpress`.

---

## 7. Crear las tablas

```bash
php artisan migrate
```

Crea todas las tablas (cursos, lecciones, evaluaciones, certificados, etc.).
Debe terminar sin errores, con cada migración marcada como `DONE`.

---

## 8. Cargar los usuarios iniciales (seeders)

### 8.1. Obligatorio: el administrador

```bash
php artisan db:seed
```

Crea el **administrador** (`admin@formaexpress.test` / `admin1234`). Es
imprescindible: desde el formulario de registro solo se pueden crear empleados,
así que sin este paso nadie podría crear cursos.

### 8.2. Opcional, solo de demostración: un empleado y un encargado

```bash
php artisan db:seed --class=UsuariosDemostracionSeeder
```

Crea dos usuarios para probar los otros perfiles:

| Perfil | Email | Contraseña |
|---|---|---|
| Empleado | `empleado@formaexpress.test` | `empleado1234` |
| Encargado | `encargado@formaexpress.test` | `encargado1234` |

Este seeder **solo** crea esos dos usuarios: no crea cursos ni ningún otro dato.
No es el conjunto de datos de prueba de la Tarea 2 (pendiente).

Los dos seeders se pueden ejecutar más de una vez sin duplicar usuarios.

> **Aviso de seguridad:** todas estas contraseñas son públicas y solo valen para
> evaluar el proyecto en local. **No las uses si el proyecto se despliega de
> verdad.** La aplicación todavía no tiene una pantalla para cambiar la
> contraseña, así que en un despliegue real habría que crear las cuentas con
> contraseñas propias en lugar de usar estos seeders.

---

## 9. Arrancar la aplicación

```bash
php artisan serve
```

Abre en el navegador la dirección que indica (por defecto
**http://127.0.0.1:8000**) e inicia sesión con alguno de los usuarios del paso 8.

Para detener el servidor, pulsa `Ctrl + C` en la terminal.

### Comprobación rápida

1. Inicia sesión como `admin@formaexpress.test` / `admin1234`: en el menú deben
   aparecer **Consulta**, **Cursos** y **Registro de consultas**.
2. Si cargaste los usuarios de demostración, el empleado ve **Mis cursos** y
   **Mis logros**, y el encargado solo **Consulta**.

El uso de cada perfil se explica en el [manual de funcionamiento](manual-usuario.md).

---

## 10. (Opcional) Ejecutar los tests

```bash
php artisan test
```

Los tests usan una base de datos SQLite en memoria (no tocan MySQL), así que
necesitan además la extensión **`pdo_sqlite`** de PHP. Deben pasar todos.

---

## 11. Problemas frecuentes

| Síntoma | Causa probable | Solución |
|---|---|---|
| `composer install` dice *"Your lock file does not contain a compatible set of packages. Please run composer update"* y *"requires php ^8.3"* | El `php` de la terminal es más antiguo que 8.3 | Usa PHP 8.3 o superior (`php -v`). **No ejecutes `composer update`**: no lo arregla y cambiaría las versiones fijadas del proyecto |
| `could not find driver` al migrar | Falta la extensión `pdo_mysql` | Actívala en `php.ini` |
| `SQLSTATE[HY000] [1045] Access denied` | Usuario o contraseña de MySQL incorrectos | Revisa `DB_USERNAME` y `DB_PASSWORD` en `.env` |
| `SQLSTATE[HY000] [1049] Unknown database` | No existe la base de datos | Créala (paso 6) con el mismo nombre que `DB_DATABASE` |
| `SQLSTATE[HY000] [2002]` | MySQL no está arrancado o el host/puerto no es ese | Arranca MySQL y revisa `DB_HOST` y `DB_PORT` |
| `No application encryption key has been specified` | Falta `APP_KEY` | `php artisan key:generate` |
| La página sale sin estilos | El navegador no puede cargar Bootstrap del CDN | Comprueba la conexión a internet |
| Errores de permisos al escribir en `storage/` (Linux/macOS) | La carpeta no es escribible | `chmod -R u+w storage bootstrap/cache` |
| Cambias el `.env` y no se nota | Configuración en caché | `php artisan config:clear` |

---

## Pendiente

- **Despliegue online:** no existe todavía ninguna URL pública de la aplicación.
- **URL del repositorio** en GitHub (paso 2), cuando se publique.
- **Usuario de MySQL propio para la aplicación** (Tarea 2).
- **Datos de prueba** de la Tarea 2 (5-10 registros por tabla).
