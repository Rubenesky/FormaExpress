<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Leccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeccionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->administrador()->create();
        $this->curso = Curso::create(['titulo' => 'Apertura de caja']);
    }

    private function crearLeccion(array $datos = []): Leccion
    {
        return $this->curso->lecciones()->create($datos + ['titulo' => 'Lección', 'contenido' => 'Texto', 'orden' => 1]);
    }

    public function test_el_listado_muestra_las_lecciones_del_curso_por_orden(): void
    {
        $this->crearLeccion(['titulo' => 'Segunda', 'orden' => 2]);
        $this->crearLeccion(['titulo' => 'Primera', 'orden' => 1]);
        Curso::create(['titulo' => 'Otro'])->lecciones()->create(['titulo' => 'De otro curso', 'contenido' => 'x', 'orden' => 1]);

        $this->actingAs($this->admin)->get("/cursos/{$this->curso->id}/lecciones")
            ->assertOk()
            ->assertSeeInOrder(['Primera', 'Segunda'])
            ->assertDontSee('De otro curso');
    }

    public function test_el_formulario_propone_el_siguiente_orden(): void
    {
        $this->actingAs($this->admin)->get("/cursos/{$this->curso->id}/lecciones/create")
            ->assertViewHas('leccion', fn ($leccion) => $leccion->orden === 1);

        $this->crearLeccion(['orden' => 5]);

        $this->actingAs($this->admin)->get("/cursos/{$this->curso->id}/lecciones/create")
            ->assertViewHas('leccion', fn ($leccion) => $leccion->orden === 6);
    }

    public function test_el_administrador_crea_una_leccion_dentro_del_curso(): void
    {
        $this->actingAs($this->admin)
            ->post("/cursos/{$this->curso->id}/lecciones", ['titulo' => 'Arqueo', 'contenido' => 'Contar el efectivo', 'orden' => 1])
            ->assertRedirect("/cursos/{$this->curso->id}/lecciones")
            ->assertSessionHas('status', 'Lección creada.');

        $this->assertDatabaseHas('lecciones', ['curso_id' => $this->curso->id, 'titulo' => 'Arqueo', 'orden' => 1]);
    }

    public function test_la_validacion_de_lecciones_esta_en_espanol(): void
    {
        $this->actingAs($this->admin)
            ->post("/cursos/{$this->curso->id}/lecciones", ['titulo' => '', 'contenido' => '', 'orden' => 0])
            ->assertSessionHasErrors([
                'titulo' => 'El campo título es obligatorio.',
                'contenido' => 'El campo contenido es obligatorio.',
                'orden' => 'El campo orden debe ser al menos 1.',
            ]);

        $this->actingAs($this->admin)
            ->post("/cursos/{$this->curso->id}/lecciones", ['titulo' => 'X', 'contenido' => 'Y', 'orden' => 'abc'])
            ->assertSessionHasErrors(['orden' => 'El campo orden debe ser un número entero.']);

        $this->assertDatabaseCount('lecciones', 0);
    }

    public function test_el_administrador_edita_una_leccion(): void
    {
        $leccion = $this->crearLeccion();

        $this->actingAs($this->admin)->get("/cursos/{$this->curso->id}/lecciones/{$leccion->id}/edit")->assertOk();

        $this->actingAs($this->admin)
            ->put("/cursos/{$this->curso->id}/lecciones/{$leccion->id}", ['titulo' => 'Editada', 'contenido' => 'Nuevo', 'orden' => 3])
            ->assertRedirect("/cursos/{$this->curso->id}/lecciones");

        $this->assertDatabaseHas('lecciones', ['id' => $leccion->id, 'titulo' => 'Editada', 'orden' => 3]);
    }

    public function test_el_administrador_elimina_una_leccion(): void
    {
        $leccion = $this->crearLeccion(['titulo' => 'A borrar']);

        $this->actingAs($this->admin)->get("/cursos/{$this->curso->id}/lecciones/{$leccion->id}/eliminar")
            ->assertOk()
            ->assertSee('A borrar');

        $this->actingAs($this->admin)
            ->delete("/cursos/{$this->curso->id}/lecciones/{$leccion->id}")
            ->assertRedirect("/cursos/{$this->curso->id}/lecciones")
            ->assertSessionHas('status', 'Lección eliminada.');

        $this->assertDatabaseMissing('lecciones', ['id' => $leccion->id]);
        $this->assertDatabaseHas('cursos', ['id' => $this->curso->id]);
    }

    public function test_no_se_elimina_una_leccion_con_progreso_de_empleados(): void
    {
        $leccion = $this->crearLeccion();
        DB::table('progreso_lecciones')->insert([
            'empleado_id' => User::factory()->create()->id,
            'leccion_id' => $leccion->id,
            'fecha_finalizacion' => now(),
        ]);

        $this->actingAs($this->admin)
            ->delete("/cursos/{$this->curso->id}/lecciones/{$leccion->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('lecciones', ['id' => $leccion->id]);
    }

    public function test_una_leccion_no_se_puede_manejar_desde_la_url_de_otro_curso(): void
    {
        $leccion = $this->crearLeccion(['titulo' => 'Original']);
        $otro = Curso::create(['titulo' => 'Otro']);
        $base = "/cursos/{$otro->id}/lecciones/{$leccion->id}";

        $this->actingAs($this->admin)->get("{$base}/edit")->assertNotFound();
        $this->actingAs($this->admin)->put($base, ['titulo' => 'Hackeada', 'contenido' => 'x', 'orden' => 1])->assertNotFound();
        $this->actingAs($this->admin)->get("{$base}/eliminar")->assertNotFound();
        $this->actingAs($this->admin)->delete($base)->assertNotFound();

        $this->assertDatabaseHas('lecciones', ['id' => $leccion->id, 'titulo' => 'Original']);
    }

    public function test_un_empleado_no_puede_acceder_a_las_lecciones(): void
    {
        $leccion = $this->crearLeccion();
        $empleado = User::factory()->create();
        $base = "/cursos/{$this->curso->id}/lecciones";

        $this->actingAs($empleado)->get($base)->assertForbidden();
        $this->actingAs($empleado)->get("{$base}/create")->assertForbidden();
        $this->actingAs($empleado)->post($base, ['titulo' => 'Intrusa', 'contenido' => 'x', 'orden' => 9])->assertForbidden();
        $this->actingAs($empleado)->get("{$base}/{$leccion->id}/edit")->assertForbidden();
        $this->actingAs($empleado)->put("{$base}/{$leccion->id}", ['titulo' => 'Cambiada', 'contenido' => 'x', 'orden' => 1])->assertForbidden();
        $this->actingAs($empleado)->delete("{$base}/{$leccion->id}")->assertForbidden();

        $this->assertDatabaseMissing('lecciones', ['titulo' => 'Intrusa']);
        $this->assertDatabaseHas('lecciones', ['id' => $leccion->id, 'titulo' => 'Lección']);
    }
}
