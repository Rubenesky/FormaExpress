<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuarios de DEMOSTRACIÓN: un empleado y un encargado con contraseña conocida,
 * solo para poder probar y documentar el acceso de cada perfil.
 *
 * IMPORTANTE: esto NO es el dataset de la Tarea 2. No sustituye ni se solapa con
 * el script de carga de datos de prueba (5-10 registros por tabla), que sigue
 * pendiente. Solo crea estos dos usuarios; no crea cursos, lecciones ni nada más.
 *
 * No lo ejecuta DatabaseSeeder: hay que pedirlo aparte con
 *   php artisan db:seed --class=UsuariosDemostracionSeeder
 *
 * Credenciales solo para evaluación en local: no usar en un despliegue real.
 */
class UsuariosDemostracionSeeder extends Seeder
{
    /** email => [nombre, contraseña, rol] */
    private const USUARIOS = [
        'empleado@formaexpress.test' => ['Empleado de demostración', 'empleado1234', 'empleado'],
        'encargado@formaexpress.test' => ['Encargado de demostración', 'encargado1234', 'encargado'],
    ];

    public function run(): void
    {
        foreach (self::USUARIOS as $email => [$nombre, $password, $rol]) {
            $usuario = User::firstOrNew(['email' => $email]);

            // 'rol' no es asignable en masa: igual que en AdministradorSeeder, forceFill.
            $usuario->forceFill([
                'name' => $nombre,
                'password' => $password,
                'rol' => $rol,
                'email_verified_at' => now(),
            ])->save();
        }
    }
}
