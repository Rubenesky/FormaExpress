-- =============================================================================
-- FormaExpress - Usuarios y privilegios de MySQL (Tarea 2)
--
-- Crea dos cuentas, ninguna con privilegios de administración ni DDL:
--
--   formaexpress_app      La que usa la aplicación (.env). SELECT, INSERT,
--                         UPDATE y DELETE sobre la base de datos formaexpress.
--                         No puede crear, modificar ni borrar tablas.
--   formaexpress_lectura  Solo SELECT sobre formaexpress: para consultas e
--                         informes sin riesgo de cambiar datos.
--
-- ANTES DE EJECUTARLO
--   1. Sustituye en este archivo los dos marcadores por contraseñas propias:
--        CAMBIA_ESTA_CONTRASENA_APP      (líneas del usuario formaexpress_app)
--        CAMBIA_ESTA_CONTRASENA_LECTURA  (líneas del usuario formaexpress_lectura)
--      Hazlo en una copia local o deshaz el cambio después: las contraseñas
--      reales no deben guardarse en el repositorio.
--   2. Ejecútalo con un usuario administrador de MySQL (por ejemplo, root):
--        mysql -u root -p < database/sql/03_usuarios_y_privilegios.sql
--
-- DESPUÉS
--   - En el .env de la aplicación (no se sube al repositorio):
--        DB_USERNAME=formaexpress_app
--        DB_PASSWORD=<la contraseña que hayas puesto>
--     y `php artisan config:clear` si la configuración estaba en caché.
--   - Las migraciones (`php artisan migrate`) crean y modifican tablas, así que
--     necesitan un usuario con privilegios DDL (por ejemplo, root). Se ejecutan
--     con ese usuario y la aplicación funciona después con formaexpress_app.
--   - Para cambiar una contraseña más adelante:
--        ALTER USER 'formaexpress_app'@'localhost' IDENTIFIED BY 'nueva';
--
-- Las cuentas se crean para 'localhost': sirven para la aplicación y MySQL en
-- el mismo equipo (también con DB_HOST=127.0.0.1). Si MySQL estuviera en otro
-- servidor, habría que crearlas para el host desde el que conecta la aplicación.
--
-- CREATE USER IF NOT EXISTS no cambia la contraseña de una cuenta que ya
-- exista; para eso está ALTER USER.
-- =============================================================================

-- Usuario de la aplicación: lee y escribe datos, sin DDL.
CREATE USER IF NOT EXISTS 'formaexpress_app'@'localhost'
  IDENTIFIED BY 'CAMBIA_ESTA_CONTRASENA_APP';
GRANT SELECT, INSERT, UPDATE, DELETE ON formaexpress.* TO 'formaexpress_app'@'localhost';

-- Usuario de solo lectura.
CREATE USER IF NOT EXISTS 'formaexpress_lectura'@'localhost'
  IDENTIFIED BY 'CAMBIA_ESTA_CONTRASENA_LECTURA';
GRANT SELECT ON formaexpress.* TO 'formaexpress_lectura'@'localhost';

-- Comprobación: deben aparecer USAGE (sin privilegios globales) y solo los
-- privilegios de arriba sobre formaexpress.*.
SHOW GRANTS FOR 'formaexpress_app'@'localhost';
SHOW GRANTS FOR 'formaexpress_lectura'@'localhost';
