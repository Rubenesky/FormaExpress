<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CursoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->administrador()->create();
    }

    private function empleado(): User
    {
        return User::factory()->create();
    }

    // --- Listar ---

    public function test_el_administrador_ve_el_listado_de_cursos(): void
    {
        Curso::create(['titulo' => 'Apertura de caja', 'descripcion' => 'Arqueo inicial']);

        $this->actingAs($this->admin())->get('/cursos')
            ->assertOk()
            ->assertSee('Apertura de caja')
            ->assertSee('Arqueo inicial');
    }

    public function test_el_listado_esta_paginado_de_10_en_10(): void
    {
        foreach (range(1, 12) as $n) {
            Curso::create(['titulo' => sprintf('Curso %02d', $n)]);
        }
        $admin = $this->admin();

        $this->actingAs($admin)->get('/cursos')
            ->assertViewHas('cursos', fn ($cursos) => $cursos->count() === 10 && $cursos->total() === 12)
            ->assertSee('Curso 10')
            ->assertDontSee('Curso 11');

        $this->actingAs($admin)->get('/cursos?page=2')
            ->assertSee('Curso 11')
            ->assertSee('Curso 12');
    }

    public function test_el_listado_muestra_cuantas_lecciones_tiene_cada_curso(): void
    {
        $curso = Curso::create(['titulo' => 'Con lecciones']);
        $curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);
        $curso->lecciones()->create(['titulo' => 'L2', 'contenido' => 'x', 'orden' => 2]);

        $this->actingAs($this->admin())->get('/cursos')
            ->assertViewHas('cursos', fn ($cursos) => $cursos->first()->lecciones_count === 2);
    }

    // --- Crear ---

    public function test_el_administrador_crea_un_curso(): void
    {
        $this->actingAs($this->admin())
            ->post('/cursos', ['titulo' => 'Prevención de riesgos', 'descripcion' => 'Obligatorio'])
            ->assertRedirect('/cursos')
            ->assertSessionHas('status', 'Curso creado.');

        $this->assertDatabaseHas('cursos', ['titulo' => 'Prevención de riesgos', 'descripcion' => 'Obligatorio']);
    }

    public function test_la_descripcion_es_opcional(): void
    {
        $this->actingAs($this->admin())
            ->post('/cursos', ['titulo' => 'Sin descripción'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cursos', ['titulo' => 'Sin descripción', 'descripcion' => null]);
    }

    public function test_el_titulo_es_obligatorio_con_mensaje_en_espanol(): void
    {
        $this->actingAs($this->admin())
            ->from('/cursos/create')
            ->post('/cursos', ['titulo' => ''])
            ->assertRedirect('/cursos/create')
            ->assertSessionHasErrors(['titulo' => 'El campo título es obligatorio.']);

        $this->assertDatabaseCount('cursos', 0);
    }

    public function test_el_titulo_no_puede_superar_255_caracteres(): void
    {
        $this->actingAs($this->admin())
            ->post('/cursos', ['titulo' => str_repeat('a', 256)])
            ->assertSessionHasErrors(['titulo' => 'El campo título no puede tener más de 255 caracteres.']);
    }

    // --- Editar ---

    public function test_el_administrador_edita_un_curso(): void
    {
        $curso = Curso::create(['titulo' => 'Antiguo']);
        $admin = $this->admin();

        $this->actingAs($admin)->get("/cursos/{$curso->id}/edit")
            ->assertOk()
            ->assertSee('value="Antiguo"', false);

        $this->actingAs($admin)
            ->put("/cursos/{$curso->id}", ['titulo' => 'Nuevo', 'descripcion' => 'Actualizada'])
            ->assertRedirect('/cursos')
            ->assertSessionHas('status', 'Curso actualizado.');

        $this->assertDatabaseHas('cursos', ['id' => $curso->id, 'titulo' => 'Nuevo', 'descripcion' => 'Actualizada']);
    }

    public function test_editar_valida_el_titulo(): void
    {
        $curso = Curso::create(['titulo' => 'Antiguo']);

        $this->actingAs($this->admin())
            ->put("/cursos/{$curso->id}", ['titulo' => ''])
            ->assertSessionHasErrors('titulo');

        $this->assertDatabaseHas('cursos', ['id' => $curso->id, 'titulo' => 'Antiguo']);
    }

    // --- Eliminar ---

    public function test_la_confirmacion_avisa_de_las_lecciones_que_se_borraran(): void
    {
        $curso = Curso::create(['titulo' => 'Con lecciones']);
        $curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);
        $curso->lecciones()->create(['titulo' => 'L2', 'contenido' => 'x', 'orden' => 2]);

        $this->actingAs($this->admin())->get("/cursos/{$curso->id}/eliminar")
            ->assertOk()
            ->assertSee('Se borrarán también sus 2 lecciones.');
    }

    public function test_la_confirmacion_usa_singular_con_una_leccion(): void
    {
        $curso = Curso::create(['titulo' => 'Con una']);
        $curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);

        $this->actingAs($this->admin())->get("/cursos/{$curso->id}/eliminar")
            ->assertSee('Se borrará también su única lección.');
    }

    public function test_el_administrador_elimina_un_curso_y_sus_lecciones_en_cascada(): void
    {
        $curso = Curso::create(['titulo' => 'A borrar']);
        $leccion = $curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);

        $this->actingAs($this->admin())
            ->delete("/cursos/{$curso->id}")
            ->assertRedirect('/cursos')
            ->assertSessionHas('status', 'Curso eliminado junto con sus lecciones.');

        $this->assertDatabaseMissing('cursos', ['id' => $curso->id]);
        $this->assertDatabaseMissing('lecciones', ['id' => $leccion->id]);
    }

    public function test_no_se_elimina_un_curso_con_datos_de_empleados(): void
    {
        $curso = Curso::create(['titulo' => 'Asignado']);
        DB::table('asignaciones_curso')->insert([
            'empleado_id' => $this->empleado()->id,
            'curso_id' => $curso->id,
            'fecha_asignacion' => today(),
        ]);

        $this->actingAs($this->admin())
            ->delete("/cursos/{$curso->id}")
            ->assertRedirect('/cursos')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('cursos', ['id' => $curso->id]);
    }

    // --- Control de acceso ---

    public function test_un_empleado_no_puede_acceder_a_ninguna_ruta_de_cursos(): void
    {
        $curso = Curso::create(['titulo' => 'Protegido']);
        $empleado = $this->empleado();

        $this->actingAs($empleado)->get('/cursos')->assertForbidden();
        $this->actingAs($empleado)->get('/cursos/create')->assertForbidden();
        $this->actingAs($empleado)->post('/cursos', ['titulo' => 'Intruso'])->assertForbidden();
        $this->actingAs($empleado)->get("/cursos/{$curso->id}/edit")->assertForbidden();
        $this->actingAs($empleado)->put("/cursos/{$curso->id}", ['titulo' => 'Cambiado'])->assertForbidden();
        $this->actingAs($empleado)->get("/cursos/{$curso->id}/eliminar")->assertForbidden();
        $this->actingAs($empleado)->delete("/cursos/{$curso->id}")->assertForbidden();

        $this->assertDatabaseMissing('cursos', ['titulo' => 'Intruso']);
        $this->assertDatabaseHas('cursos', ['id' => $curso->id, 'titulo' => 'Protegido']);
    }

    public function test_un_encargado_tampoco_puede_acceder(): void
    {
        $encargado = User::factory()->create(['rol' => 'encargado']);

        $this->actingAs($encargado)->get('/cursos')->assertForbidden();
    }

    public function test_un_invitado_es_enviado_al_login(): void
    {
        $this->get('/cursos')->assertRedirect('/login');
        $this->post('/cursos', ['titulo' => 'Intruso'])->assertRedirect('/login');

        $this->assertDatabaseCount('cursos', 0);
    }

    public function test_solo_el_administrador_ve_el_enlace_cursos_en_el_menu(): void
    {
        $enlace = 'href="'.url('/cursos').'"';

        $this->actingAs($this->empleado())->get('/home')->assertDontSee($enlace, false);
        $this->actingAs($this->admin())->get('/home')->assertSee($enlace, false);
    }
}
