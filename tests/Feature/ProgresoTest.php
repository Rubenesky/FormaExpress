<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Leccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProgresoTest extends TestCase
{
    use RefreshDatabase;

    private User $empleado;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empleado = User::factory()->create();
        $this->curso = Curso::create(['titulo' => 'Apertura de caja']);
        $this->curso->empleadosAsignados()->attach($this->empleado->id, ['fecha_asignacion' => today()]);
    }

    private function leccion(Curso $curso, string $titulo, int $orden = 1, string $contenido = 'Texto'): Leccion
    {
        return $curso->lecciones()->create(['titulo' => $titulo, 'contenido' => $contenido, 'orden' => $orden]);
    }

    private function porcentajeEnListado(): int
    {
        $respuesta = $this->actingAs($this->empleado)->get('/mis-cursos');

        return $respuesta->viewData('cursos')->firstWhere('id', $this->curso->id)->porcentajeProgreso();
    }

    // --- Mis cursos ---

    public function test_mis_cursos_solo_muestra_los_asignados(): void
    {
        Curso::create(['titulo' => 'Curso no asignado']);

        $this->actingAs($this->empleado)->get('/mis-cursos')
            ->assertOk()
            ->assertSee('Apertura de caja')
            ->assertDontSee('Curso no asignado');
    }

    public function test_el_detalle_muestra_las_lecciones_ordenadas(): void
    {
        $this->leccion($this->curso, 'Segunda', 2);
        $this->leccion($this->curso, 'Primera', 1);
        $this->leccion($this->curso, 'Tercera', 3);

        $this->actingAs($this->empleado)->get("/mis-cursos/{$this->curso->id}")
            ->assertOk()
            ->assertSeeInOrder(['Primera', 'Segunda', 'Tercera']);
    }

    // --- Completar lección ---

    public function test_completar_una_leccion_crea_el_progreso_con_fecha(): void
    {
        $leccion = $this->leccion($this->curso, 'L1');
        $this->travelTo(now()->setDateTime(2026, 9, 28, 10, 30, 0));

        $this->actingAs($this->empleado)
            ->post("/mis-lecciones/{$leccion->id}/completar")
            ->assertRedirect("/mis-lecciones/{$leccion->id}")
            ->assertSessionHas('status', 'Lección completada.');

        $this->assertDatabaseHas('progreso_lecciones', [
            'empleado_id' => $this->empleado->id,
            'leccion_id' => $leccion->id,
            'fecha_finalizacion' => '2026-09-28 10:30:00',
        ]);

        $this->actingAs($this->empleado)->get("/mis-lecciones/{$leccion->id}")
            ->assertSee('Completada el 28/09/2026 10:30')
            ->assertDontSee('Marcar como completada');
    }

    public function test_completar_dos_veces_no_duplica_ni_cambia_la_fecha(): void
    {
        $leccion = $this->leccion($this->curso, 'L1');

        $this->travelTo(now()->setDateTime(2026, 9, 28, 10, 0, 0));
        $this->actingAs($this->empleado)->post("/mis-lecciones/{$leccion->id}/completar")->assertRedirect();

        $this->travelTo(now()->setDateTime(2026, 9, 29, 12, 0, 0));
        $this->actingAs($this->empleado)->post("/mis-lecciones/{$leccion->id}/completar")
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(1, DB::table('progreso_lecciones')->where('leccion_id', $leccion->id)->count());
        $this->assertDatabaseHas('progreso_lecciones', ['leccion_id' => $leccion->id, 'fecha_finalizacion' => '2026-09-28 10:00:00']);
    }

    // --- Cálculo del % ---

    public function test_el_porcentaje_es_completadas_entre_totales(): void
    {
        $l1 = $this->leccion($this->curso, 'L1', 1);
        $l2 = $this->leccion($this->curso, 'L2', 2);
        $l3 = $this->leccion($this->curso, 'L3', 3);

        $this->assertSame(0, $this->porcentajeEnListado());

        $this->empleado->completarLeccion($l1);
        $this->assertSame(33, $this->porcentajeEnListado());

        $this->empleado->completarLeccion($l2);
        $this->assertSame(67, $this->porcentajeEnListado());

        $this->empleado->completarLeccion($l3);
        $this->assertSame(100, $this->porcentajeEnListado());

        $this->actingAs($this->empleado)->get('/mis-cursos')->assertSee('3 de 3 lecciones');
    }

    public function test_el_progreso_de_otros_empleados_y_cursos_no_cuenta(): void
    {
        $l1 = $this->leccion($this->curso, 'L1', 1);
        $this->leccion($this->curso, 'L2', 2);

        User::factory()->create()->completarLeccion($l1);
        $this->empleado->completarLeccion($this->leccion(Curso::create(['titulo' => 'Otro']), 'Ajena'));

        $this->assertSame(0, $this->porcentajeEnListado());
    }

    public function test_un_curso_sin_lecciones_esta_al_0_por_ciento(): void
    {
        $this->assertSame(0, $this->porcentajeEnListado());
    }

    // --- Acceso ---

    public function test_un_empleado_sin_asignacion_no_ve_el_curso_ni_sus_lecciones(): void
    {
        $otro = Curso::create(['titulo' => 'Almacén']);
        $ajena = $this->leccion($otro, 'Ajena');

        $this->actingAs($this->empleado)->get("/mis-cursos/{$otro->id}")->assertForbidden();
        $this->actingAs($this->empleado)->get("/mis-lecciones/{$ajena->id}")->assertForbidden();
    }

    public function test_un_empleado_no_puede_completar_una_leccion_ajena(): void
    {
        $ajena = $this->leccion(Curso::create(['titulo' => 'Almacén']), 'Ajena');

        $this->actingAs($this->empleado)->post("/mis-lecciones/{$ajena->id}/completar")->assertForbidden();

        $this->assertDatabaseCount('progreso_lecciones', 0);
    }

    public function test_al_desasignar_se_pierde_el_acceso(): void
    {
        $leccion = $this->leccion($this->curso, 'L1');
        $this->curso->empleadosAsignados()->detach($this->empleado->id);

        $this->actingAs($this->empleado)->get("/mis-cursos/{$this->curso->id}")->assertForbidden();
        $this->actingAs($this->empleado)->get("/mis-lecciones/{$leccion->id}")->assertForbidden();
    }

    public function test_administrador_encargado_e_invitado_no_usan_mis_cursos(): void
    {
        $leccion = $this->leccion($this->curso, 'L1');

        foreach ([User::factory()->administrador()->create(), User::factory()->create(['rol' => 'encargado'])] as $usuario) {
            $this->actingAs($usuario)->get('/mis-cursos')->assertForbidden();
            $this->actingAs($usuario)->get("/mis-cursos/{$this->curso->id}")->assertForbidden();
            $this->actingAs($usuario)->post("/mis-lecciones/{$leccion->id}/completar")->assertForbidden();
        }

        $this->app['auth']->forgetGuards();
        $this->get('/mis-cursos')->assertRedirect('/login');
        $this->assertDatabaseCount('progreso_lecciones', 0);
    }

    public function test_solo_el_empleado_ve_mis_cursos_en_el_menu(): void
    {
        $enlace = 'href="'.url('/mis-cursos').'"';

        $this->actingAs($this->empleado)->get('/home')->assertSee($enlace, false);
        $this->actingAs(User::factory()->administrador()->create())->get('/home')->assertDontSee($enlace, false);
    }

    // --- Salida escapada ---

    public function test_el_contenido_de_la_leccion_se_muestra_escapado(): void
    {
        $leccion = $this->leccion($this->curso, 'L1', 1, "Línea uno\n<script>alert(1)</script>");

        $this->actingAs($this->empleado)->get("/mis-lecciones/{$leccion->id}")
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
