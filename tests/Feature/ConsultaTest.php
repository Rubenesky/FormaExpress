<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Insignia;
use App\Models\RegistroConsulta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ConsultaTest extends TestCase
{
    use RefreshDatabase;

    private User $encargado;

    private User $admin;

    private User $ana;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->encargado = User::factory()->create(['name' => 'Eva Encargada', 'rol' => 'encargado']);
        $this->admin = User::factory()->administrador()->create(['name' => 'Admin']);
        $this->ana = User::factory()->create(['name' => 'Ana López']);

        // Ana: curso con 2 lecciones (1 completada), certificado y una insignia.
        $this->curso = Curso::create(['titulo' => 'Apertura de caja']);
        $leccion = $this->curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);
        $this->curso->lecciones()->create(['titulo' => 'L2', 'contenido' => 'x', 'orden' => 2]);
        $this->curso->empleadosAsignados()->attach($this->ana->id, ['fecha_asignacion' => '2026-09-01']);
        $this->ana->completarLeccion($leccion);
        DB::table('certificados')->insert(['empleado_id' => $this->ana->id, 'curso_id' => $this->curso->id, 'fecha_emision' => '2026-09-20']);
        DB::table('empleado_insignia')->insert([
            'empleado_id' => $this->ana->id,
            'insignia_id' => Insignia::where('codigo', Insignia::NOTA_MAXIMA)->value('id'),
            'created_at' => '2026-09-20 10:00:00',
        ]);
    }

    private function ficha(?User $empleado = null): string
    {
        return '/consulta/empleados/'.($empleado ?? $this->ana)->id;
    }

    /** Contenido de <main> (sin el menú, que incluye el formulario de logout). */
    private function principal(string $html): string
    {
        return preg_match('/<main[^>]*>(.*)<\/main>/s', $html, $m) ? $m[1] : '';
    }

    // --- Búsqueda ---

    public function test_el_encargado_busca_empleados_por_nombre(): void
    {
        User::factory()->create(['name' => 'Luis Pérez']);

        $this->actingAs($this->encargado)->get('/consulta?q=an')
            ->assertOk()
            ->assertSee('Ana López')
            ->assertDontSee('Luis Pérez')
            ->assertSee('href="'.url($this->ficha()).'"', false);
    }

    public function test_la_busqueda_solo_lista_empleados_y_exige_dos_caracteres(): void
    {
        $this->actingAs($this->encargado)->get('/consulta')->assertOk()->assertDontSee('Ver ficha');
        $this->actingAs($this->encargado)->get('/consulta?q=a')->assertSee('Escribe al menos 2 caracteres.')->assertDontSee('Ver ficha');

        // "a" aparece en Eva Encargada y en Admin, pero no son empleados.
        $this->actingAs($this->encargado)->get('/consulta?q=ad')->assertDontSee('Ver ficha');
        $this->actingAs($this->encargado)->get('/consulta?q=%25%25')->assertDontSee('Ver ficha');
    }

    public function test_la_busqueda_no_registra_consultas(): void
    {
        $this->actingAs($this->encargado)->get('/consulta?q=ana');

        $this->assertDatabaseCount('registro_consultas', 0);
    }

    // --- Ficha ---

    public function test_el_encargado_ve_la_ficha_completa(): void
    {
        $this->actingAs($this->encargado)->get($this->ficha())
            ->assertOk()
            ->assertSee('Ana López')
            ->assertSee('Apertura de caja')
            ->assertSee('1 de 2 lecciones')
            ->assertSee('50%')
            ->assertSee('20/09/2026')
            ->assertSee('Nota máxima');
    }

    public function test_la_ficha_no_tiene_nada_editable_ni_el_pdf(): void
    {
        $contenido = $this->principal($this->actingAs($this->encargado)->get($this->ficha())->getContent());

        $this->assertNotSame('', $contenido);
        $this->assertStringNotContainsString('<form', $contenido);
        $this->assertStringNotContainsString('<button', $contenido);
        $this->assertDoesNotMatchRegularExpression('#/(create|edit|eliminar|asignaciones|evaluacion|pdf)\b#', $contenido);
        $this->assertDoesNotMatchRegularExpression('/method="(POST|PUT|DELETE)"|name="_method"/i', $contenido);
    }

    public function test_la_busqueda_no_tiene_nada_editable(): void
    {
        $contenido = $this->principal($this->actingAs($this->encargado)->get('/consulta?q=ana')->getContent());

        $this->assertStringContainsString('method="GET"', $contenido);
        $this->assertDoesNotMatchRegularExpression('/method="(POST|PUT|DELETE)"|name="_method"/i', $contenido);
        $this->assertDoesNotMatchRegularExpression('#/(create|edit|eliminar|asignaciones|pdf)\b#', $contenido);
    }

    public function test_solo_se_consultan_fichas_de_empleados(): void
    {
        $this->actingAs($this->encargado)->get($this->ficha($this->admin))->assertNotFound();
        $this->actingAs($this->encargado)->get($this->ficha(User::factory()->create(['rol' => 'encargado'])))->assertNotFound();
        $this->actingAs($this->encargado)->get('/consulta/empleados/99999')->assertNotFound();

        $this->assertDatabaseCount('registro_consultas', 0);
    }

    public function test_el_encargado_no_accede_a_rutas_de_edicion_ni_al_pdf(): void
    {
        $certificado = DB::table('certificados')->value('id');
        $c = $this->curso->id;

        foreach (['/cursos', '/cursos/create', "/cursos/{$c}/edit", "/cursos/{$c}/eliminar", "/cursos/{$c}/asignaciones", "/cursos/{$c}/evaluacion", "/certificados/{$certificado}/pdf", '/registro-consultas'] as $url) {
            $this->actingAs($this->encargado)->get($url)->assertForbidden();
        }
        $this->actingAs($this->encargado)->post('/cursos', ['titulo' => 'X'])->assertForbidden();
        $this->actingAs($this->encargado)->put("/cursos/{$c}", ['titulo' => 'X'])->assertForbidden();
        $this->actingAs($this->encargado)->delete("/cursos/{$c}")->assertForbidden();

        $this->assertDatabaseHas('cursos', ['id' => $c, 'titulo' => 'Apertura de caja']);
    }

    public function test_el_administrador_tambien_puede_consultar(): void
    {
        $this->actingAs($this->admin)->get('/consulta?q=ana')->assertOk()->assertSee('Ana López');
        $this->actingAs($this->admin)->get($this->ficha())->assertOk()->assertSee('Apertura de caja');
    }

    // --- Registro RGPD ---

    public function test_cada_consulta_a_una_ficha_crea_una_fila(): void
    {
        $this->travelTo(now()->setDateTime(2026, 9, 29, 10, 15, 0));
        $this->actingAs($this->encargado)->get($this->ficha());

        $this->travelTo(now()->setDateTime(2026, 9, 29, 10, 20, 0));
        $this->actingAs($this->encargado)->get($this->ficha()); // recargar también cuenta
        $this->actingAs($this->admin)->get($this->ficha());

        $filas = DB::table('registro_consultas')->orderBy('id')->get();
        $this->assertCount(3, $filas);
        $this->assertSame([$this->encargado->id, $this->encargado->id, $this->admin->id], $filas->pluck('consultor_id')->map(fn ($id) => (int) $id)->all());
        $this->assertSame([$this->ana->id], $filas->pluck('consultado_id')->map(fn ($id) => (int) $id)->unique()->values()->all());
        $this->assertSame('2026-09-29 10:15:00', $filas[0]->consultado_en);
        $this->assertSame('2026-09-29 10:20:00', $filas[1]->consultado_en);
    }

    public function test_solo_el_administrador_ve_la_pantalla_del_registro(): void
    {
        RegistroConsulta::create(['consultor_id' => $this->encargado->id, 'consultado_id' => $this->ana->id, 'consultado_en' => '2026-09-29 10:15:00']);

        $this->actingAs($this->admin)->get('/registro-consultas')
            ->assertOk()
            ->assertSee('29/09/2026 10:15:00')
            ->assertSee('Eva Encargada')
            ->assertSee('Encargado')
            ->assertSee('Ana López');

        $this->actingAs($this->encargado)->get('/registro-consultas')->assertForbidden();
        $this->actingAs($this->ana)->get('/registro-consultas')->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->get('/registro-consultas')->assertRedirect('/login');
    }

    public function test_el_registro_esta_paginado_y_ordenado_del_mas_reciente(): void
    {
        foreach (range(1, 30) as $n) {
            RegistroConsulta::create([
                'consultor_id' => $this->encargado->id,
                'consultado_id' => $this->ana->id,
                'consultado_en' => now()->setDateTime(2026, 9, 1, 8, 0, 0)->addMinutes($n),
            ]);
        }

        $this->actingAs($this->admin)->get('/registro-consultas')
            ->assertViewHas('registros', fn ($r) => $r->count() === 25 && $r->total() === 30)
            ->assertSeeInOrder(['01/09/2026 08:30:00', '01/09/2026 08:29:00']);
        $this->actingAs($this->admin)->get('/registro-consultas?page=2')
            ->assertViewHas('registros', fn ($r) => $r->count() === 5);
    }

    public function test_el_registro_no_se_puede_editar_ni_borrar_desde_la_interfaz(): void
    {
        $registro = RegistroConsulta::create(['consultor_id' => $this->encargado->id, 'consultado_id' => $this->ana->id, 'consultado_en' => '2026-09-29 10:15:00']);

        // La pantalla no ofrece formularios ni enlaces de acción.
        $contenido = $this->principal($this->actingAs($this->admin)->get('/registro-consultas')->getContent());
        $this->assertStringNotContainsString('<form', $contenido);
        $this->assertDoesNotMatchRegularExpression('#/(edit|eliminar)\b|method="(POST|PUT|DELETE)"|name="_method"#i', $contenido);

        // No hay rutas de escritura: ni siquiera el administrador puede.
        foreach (['post', 'put', 'patch', 'delete'] as $metodo) {
            $this->actingAs($this->admin)->{$metodo}('/registro-consultas')->assertStatus(405);
            $this->actingAs($this->admin)->{$metodo}("/registro-consultas/{$registro->id}")->assertNotFound();
        }

        // Todas las rutas del módulo son de solo lectura.
        foreach (Route::getRoutes() as $ruta) {
            if (str_starts_with($ruta->uri(), 'consulta') || str_starts_with($ruta->uri(), 'registro-consultas')) {
                $this->assertSame([], array_diff($ruta->methods(), ['GET', 'HEAD']), "La ruta {$ruta->uri()} no es de solo lectura.");
            }
        }

        $this->assertDatabaseHas('registro_consultas', ['id' => $registro->id, 'consultado_en' => '2026-09-29 10:15:00']);
        $this->assertDatabaseCount('registro_consultas', 1);
    }

    // --- Empleado normal y menús ---

    public function test_un_empleado_no_accede_a_nada_del_modulo(): void
    {
        $this->actingAs($this->ana)->get('/consulta')->assertForbidden();
        $this->actingAs($this->ana)->get('/consulta?q=ana')->assertForbidden();
        $this->actingAs($this->ana)->get($this->ficha())->assertForbidden();
        $this->actingAs($this->ana)->get($this->ficha(User::factory()->create()))->assertForbidden();
        $this->actingAs($this->ana)->get('/registro-consultas')->assertForbidden();

        $this->assertDatabaseCount('registro_consultas', 0);
    }

    public function test_los_enlaces_del_menu_segun_el_rol(): void
    {
        $consulta = 'href="'.url('/consulta').'"';
        $registro = 'href="'.url('/registro-consultas').'"';

        $this->actingAs($this->encargado)->get('/home')->assertSee($consulta, false)->assertDontSee($registro, false);
        $this->actingAs($this->admin)->get('/home')->assertSee($consulta, false)->assertSee($registro, false);
        $this->actingAs($this->ana)->get('/home')->assertDontSee($consulta, false)->assertDontSee($registro, false);
    }
}
