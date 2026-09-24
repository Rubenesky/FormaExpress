<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdministradorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_login_esta_en_espanol_y_usa_bootstrap_desde_cdn(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Iniciar sesión')
            ->assertSee('Correo electrónico')
            ->assertSee('Contraseña')
            ->assertSee('¿Has olvidado tu contraseña?')
            ->assertSee('cdn.jsdelivr.net/npm/bootstrap@5.3.8', false)
            ->assertDontSee('/build/', false);
    }

    public function test_los_errores_de_validacion_estan_en_espanol(): void
    {
        $this->from('/login')->post('/login', ['email' => '', 'password' => ''])
            ->assertSessionHasErrors([
                'email' => 'El campo correo electrónico es obligatorio.',
                'password' => 'El campo contraseña es obligatorio.',
            ]);
    }

    public function test_credenciales_incorrectas_muestran_mensaje_en_espanol(): void
    {
        $this->from('/login')->post('/login', ['email' => 'nadie@formaexpress.test', 'password' => 'incorrecta'])
            ->assertSessionHasErrors(['email' => 'Estas credenciales no coinciden con nuestros registros.']);
    }

    public function test_el_seeder_crea_un_unico_administrador_aunque_se_ejecute_dos_veces(): void
    {
        $this->seed(AdministradorSeeder::class);
        $this->seed(AdministradorSeeder::class);

        $this->assertSame(1, User::where('email', 'admin@formaexpress.test')->count());
        $this->assertSame('administrador', User::where('email', 'admin@formaexpress.test')->value('rol'));
    }

    public function test_el_administrador_de_prueba_puede_iniciar_sesion(): void
    {
        $this->seed(AdministradorSeeder::class);

        $this->post('/login', ['email' => 'admin@formaexpress.test', 'password' => 'admin1234'])
            ->assertRedirect('/home');

        $this->assertAuthenticated();
    }

    public function test_el_registro_crea_empleados_aunque_se_envie_otro_rol(): void
    {
        $this->post('/register', [
            'name' => 'Ana',
            'email' => 'ana@formaexpress.test',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
            'rol' => 'administrador',
        ])->assertRedirect('/home');

        $this->assertSame('empleado', User::where('email', 'ana@formaexpress.test')->value('rol'));
    }
}
