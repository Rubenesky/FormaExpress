<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdministradorSeeder extends Seeder
{
    /**
     * Crea (o actualiza) el administrador de prueba.
     *
     * El registro público solo da de alta empleados, así que el primer
     * administrador se crea aquí. Credenciales solo para entorno local.
     */
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@formaexpress.test']);

        // 'rol' no es asignable en masa (así no se puede elegir desde el registro),
        // por eso se usa forceFill.
        $admin->forceFill([
            'name' => 'Administrador',
            'password' => 'admin1234',
            'rol' => 'administrador',
            'email_verified_at' => now(),
        ])->save();
    }
}
